<?php

namespace App\Http\Controllers\Order;

use App\DataTables\RefundsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventManager\RefundRequest;
use App\Models\Event;
use App\Models\Order;
use App\Models\Order\Refund;
use App\Models\Order\RefundItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use MetaFramework\Accessors\Prices;
use MetaFramework\Traits\Responses;
use Throwable;

class RefundController extends Controller
{
    use Responses;

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event): JsonResponse|View
    {
        $dataTable = new RefundsDataTable($event);
        return $dataTable->render('refunds.datatable.index', ['event' => $event]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event, Order $order)
    {
        return view('orders.refundable')->with([
            'event' => $event,
            'order' => $order,
            'refunds' => collect(),
            'data' => new Refund(),
            'method' => 'post',
            'route' => route('panel.manager.event.orders.refunds.store', ['event' => $event, 'order' => $order])
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RefundRequest $request, Event $event, Order $order)
    {
        DB::beginTransaction();

        $refundable = $this->refundableTreshold($request, $order);

        if ($refundable['can_refund'] === false) {

            // HACK
            // Le message de dépassement est bien transmis sur update mais nullé sur store, inéxplicable...
            return view('orders.refundable')->with(
                array_merge([
                    'event' => $event,
                    'order' => $order,
                    'refunds' => collect(),
                    'data' => new Refund(),
                    'method' => 'post',
                    'route' => route('panel.manager.event.orders.refunds.store', ['event' => $event, 'order' => $order])
                ],
                    ['error_message' => $refundable['message']]
                )
            );
        }
        try {

            // TODO: Refund number
            $refund = $order->refunds()->save(new Refund());
            $this->processRefunds($request, $refund);

            $this->responseSuccess(__('mfw.record_created'));
            $this->redirectTo(route('panel.manager.event.orders.edit', ['event' => $event, 'order' => $order]) . '?tab=refunds-tabpane-tab');

            DB::commit();

        } catch (Throwable $e) {
            $this->responseException($e);
            DB::rollback();
        }

        return $this->sendResponse();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event, Refund $refund)
    {
        return view('orders.refundable')->with([
            'event' => $event,
            'order' => $refund->order,
            'refunds' => $refund->items,
            'data' => $refund,
            'method' => 'put',
            'route' => route('panel.manager.event.refunds.update', ['event' => $event, 'refund' => $refund]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RefundRequest $request, Event $event, Refund $refund): RedirectResponse
    {

        DB::beginTransaction();

        try {
            $refundable = $this->refundableTreshold($request, $refund->order, $refund);
            if (!$refundable['can_refund']) {
                $this->responseError($refundable['message']);
                return redirect()->back()->with('error_message', $refundable['message']);
            }

            $refund->items()->delete();
            $this->processRefunds($request, $refund);

            DB::commit();


            $this->responseSuccess(__('mfw.record_updated'));
            $this->redirectTo(route('panel.manager.event.orders.edit', ['event' => $event, 'order' => $refund->order_id]) . '?tab=refunds-tabpane-tab');

        } catch (Throwable $e) {
            $this->responseException($e);
            DB::rollback();
        }

        return $this->sendResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function processRefunds(RefundRequest $request, Model|Refund $refund)
    {
        $refunds = [];
        $data = $request->validated('order_refund');

        for ($i = 0; $i < count($data['date']); ++$i) {
            $refunds[] = new RefundItem([
                'object' => $data['object'][$i],
                'amount' => $data['amount'][$i],
                'vat_id' => $data['vat_id'][$i],
                'date' => $data['date'][$i],
            ]);
        }

        $refund->items()->saveMany($refunds);
    }

    private function refundableTreshold(RefundRequest $request, Order $order, ?Refund $refund = null): array
    {
        $orderAccessor = new \App\Accessors\OrderAccessor($order);

        $total_refunds = $order->refunds->load('items')->pluck('items.*.amount')->flatten()->sum();

        if ($refund) {
            $total_refunds -= $refund->items->sum('amount');
        }

        $total_refunds += array_sum($request->validated('order_refund.amount'));


        return [
            'total_refunds' => $total_refunds,
            'order_payable' => $orderAccessor->totalPayable(),
            'can_refund' => $total_refunds <= $orderAccessor->totalPayable(),
            'message' => "Le montant total des remboursements " . Prices::readableFormat($total_refunds) . " dépasse le montant autorisé remboursable " . Prices::readableFormat($orderAccessor->totalPayable())
        ];
    }

}
