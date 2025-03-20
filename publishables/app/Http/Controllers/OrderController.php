<?php

namespace App\Http\Controllers;

use App\Accessors\{EventAccessor, EventContactAccessor, GroupAccessor, OrderAccessor, OrderRequestAccessor};
use App\Actions\{Order\OrderAccommodationActions, Order\OrderServiceActions, Order\PecActions};
use App\DataTables\OrderDataTable;
use App\Enum\OrderClientType;
use App\Http\Controllers\Order\AmendedAccommodationCartController;
use App\Http\Requests\OrderRequest;
use App\Models\{Event, EventManager\EventGroup, Order};
use App\Traits\OrderPecTrait;
use App\Traits\OrderTrait;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use MetaFramework\Services\Validation\ValidationTrait;

class OrderController extends Controller
{
    use OrderPecTrait;
    use OrderTrait;
    use ValidationTrait;

    protected bool $is_amended_order = false;

    public function __construct()
    {
        $this->as_orator = request('as_orator', false);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event): JsonResponse|View
    {
        $dataTable = new OrderDataTable($event);

        return $dataTable->render('orders.datatable.index', ['event' => $event]);
    }

    public function orators(Event $event): JsonResponse|View
    {
        $dataTable = new OrderDataTable($event);
        $dataTable->setPool([OrderClientType::ORATOR->value]);

        return $dataTable->render('orders.datatable.index', ['event' => $event]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event): Renderable
    {
        $order = new Order();

        return view('orders.orderable')->with([
            'event'             => $event->load('services'),
            'order'             => $order,
            'is_order'          => false,
            'eventAccessor'     => (new EventAccessor($event)),
            'orderAccessor'     => new OrderAccessor($order),
            'isSubOrder'        => false,
            'sellables'         => '',
            'groupParticipants' => [],
            'samePayer'         => true,
            'invoiced'          => false,
            'as_orator'         => request()->has('as_orator'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event, Order $order): Renderable
    {
        if ($order->event_id != $event->id) {
            abort(404, "Cette commande n'est pas associée à cet évènement.");
        }

        if ($order->amended_order_id) {
            return (new AmendedAccommodationCartController())->edit($order);
        }

        $order->load(['payments', 'vat', 'suborders']);

        $groupParticipants = [];
        if ($order->client_type == 'group') {
            $group             = new GroupAccessor($order->group);
            $groupParticipants = $group->getParticipantsForEvent($event->id);
        }

        $orderAccessor = new OrderAccessor($order);

        return view('orders.orderable')->with([
            'event'             => $event->load('services'),
            'eventAccessor'     => (new EventAccessor($event)),
            'is_order'          => true,
            'order'             => $order,
            'orderAccessor'     => $orderAccessor,
            'isSubOrder'        => $orderAccessor->isSuborder(),
            'samePayer'         => $orderAccessor->isSamePayer(),
            'groupParticipants' => $groupParticipants,
            'invoiced'          => $orderAccessor->isInvoiced(),
            'event_contact'     => EventContactAccessor::getData($event->id, $order->client_id),
            'as_orator'         => $order->client_type == OrderClientType::ORATOR->value,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Event $event, OrderRequest $request): RedirectResponse
    {
        $this->evaluatePossiblePec($event);
        $this->pecComputeAmounts();

        DB::beginTransaction();

        //  try {

        $this->createOrder($event, $request);
        $this->setOrderPecState();
        $this->processPayerData($request);
        $this->processAccompanying();
        $this->processRoomnotes();
        //$this->updateInvoiceableAddress();

        //  $this->order->setBeneficiary(OrderRequestAccessor::getBeneficiaryEventContact($event, $request));

        OrderServiceActions::attachServiceToOrder($this->order);
        OrderAccommodationActions::attachAccommodationToOrder($this->order);

        $this->deleteTempStock();
        $this->pecResponse();

        DB::commit();

        $this->responseSuccess("La commande a été créée");
        $this->redirect_to = route('panel.manager.event.orders.edit', [$event, $this->order]);

        /* } catch (Throwable $e) {
             $this->responseException($e);

             DB::rollBack();
         } finally {
 */

        return $this->sendResponse();
        //  }

    }

    public function show(Event $event, Order $order): RedirectResponse
    {
        return redirect()->route('panel.manager.event.orders.edit', ['event' => $event->id, 'order' => $order->id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Event $event, Order $order, OrderRequest $request)
    {
        $this->is_amended_order = request()->filled('is_amended_order');
        if ( ! $this->is_amended_order) {
            $validation                = new OrderRequest();
            $this->validation_rules    = $validation->rules();
            $this->validation_messages = $validation->messages();

            $this->validation();
        }

        $this->order = $order;

        # PEC
        # ----
        # Suppression PEC
        if (!OrderRequestAccessor::isGroup() && $this->order->pecDistributions->count()) {
            $this->order->pecDistributions()->delete();
            $this->order->pecQuota()->delete();
        }
        # Evaluation
        $this->evaluatePossiblePec($event);
        # Rattachement
        if ($this->evaluatePec) {
            $this->pecComputeAmounts();
            $this->pec->setOrder($order);
        }

        DB::beginTransaction();

        // try {
        $totalPec   = $this->getComputedPecTotal();
        $payableNet = OrderRequestAccessor::getTotalNetFromRequest();
        $payableVat = OrderRequestAccessor::getTotalVatFromRequest();

        $this->order->total_net = $this->is_amended_order
            ? request('amended.total_net')
            : $payableNet;
        $this->order->total_vat = $this->is_amended_order
            ? request('amended.total_vat')
            : $payableVat;
        $this->order->total_pec = $this->is_amended_order
            ? request('amended.total_pec')
            : $totalPec;

        $this->order->created_at       = Carbon::createFromFormat('d/m/Y', request('order.date'));
        $this->order->external_invoice = request()->filled('order.external_invoice') ? 1 : null;
        $this->order->po               = request('order.po');
        $this->order->note             = request('order.note');
        $this->order->terms            = request('order.terms');
        $this->order->configs          = (array)request('configs');
        $this->order->save();


        if ($this->evaluatePec) {
            $this->order->pecAuthorized   = $this->pecAuthorized();
            $this->order->pecDistribution = $this->pec->getPecDistributionResult();
        }


        if ( ! $this->is_amended_order) {
            // Ne pas MAJ comptes d'affectation et paiement
            $this->processPayerData($request);

            // MAJ adresse de facturation
            $this->updateInvoiceableAddress();
        }

        $this->processAccompanying();
        $this->processRoomnotes();

        if ( ! $this->is_amended_order) {
            OrderServiceActions::attachServiceToOrder($order);
        }
        OrderAccommodationActions::attachAccommodationToOrder($order);

        $this->deleteTempStock();
        $this->pecResponse();

        DB::commit();

        $this->responseSuccess("La commande a été mise à jour");
        $account = $order->client();

        if ($account) {
            if ($order->client_type == 'group') {
                $eventGroup = EventGroup::where('event_id', $event->id)
                    ->where('group_id', $account->id)
                    ->first();
                if ($eventGroup) {
                    $this->redirect_to = route('panel.manager.event.event_group.edit', ['event' => $event, 'event_group' => $eventGroup->id]);
                }
            } else {
                $eventContact = EventContactAccessor::getData($event->id, $order->client_id);
                if (isset($eventContact['event_contact_id'])) {
                    $this->redirect_to = route('panel.manager.event.event_contact.edit', ['event' => $event, 'event_contact' => $eventContact['event_contact_id']]);
                }
            }
        }

        /*
                } catch (Throwable $e) {
                    DB::rollBack();
                    $this->responseException($e);
                }
        */

        return $this->sendResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $this->responseError("Une commande ne peut pas être supprimée.");
        $this->redirectTo(route('panel.manager.event.orders.index', $event));
        $this->whitout('input');

        return $this->sendResponse();
    }

}
