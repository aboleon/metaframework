<?php

namespace App\Traits;


use App\Accessors\OrderRequestAccessor;
use App\Actions\Account\UpdateAccountAddressAction;
use App\Enum\OrderClientType;
use App\Models\Event;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

trait OrderTrait
{

    private array $payerData = [];

    private Order $order;
    protected bool $as_orator = false;

    protected function createOrder(Event $event, FormRequest $request): void
    {
        $totalPec = $this->getComputedPecTotal();
        $payableNet = OrderRequestAccessor::getTotalNetFromRequest();
        $payableVat = OrderRequestAccessor::getTotalVatFromRequest();

        $this->order = Order::create([
            'event_id' => $event->id,
            'uuid' => request('order_uuid'),
            'created_by' => auth()->id(),
            'client_id' => $request->validated('order.client_type') == OrderClientType::CONTACT->value ? $request->validated('order.contact_id') : $request->validated('order.group_id'),
            'client_type' => $request->validated('order.client_type') == OrderClientType::CONTACT->value && $this->as_orator ? OrderClientType::ORATOR->value : $request->validated('order.client_type'),
            'total_net' =>  $payableNet,
            'total_vat' => $payableVat,
            'total_pec' => $totalPec,
            'created_at' => Carbon::createFromFormat('d/m/Y', $request->validated('order.date')),
            'external_invoice' => $request->validated('order.external_invoice'),
            'po' => $request->validated('order.po'),
            'note' => $request->validated('order.note'),
            'terms' => $request->validated('order.terms')
        ]);
    }


    protected function processAccompanying(): void
    {
        $this->order->accompanying()->delete();

        if (request()->has('add_accompanying') && request()->filled('order_accompanying')) {
            $data = request('order_accompanying');
            $insert = [];
            for ($i = 0; $i < count($data['total']); ++$i) {
                if (!$data['total'][$i] or !$data['room_id']) {
                    continue;
                }
                $insert[] = new Order\Accompanying([
                    'total' => $data['total'][$i],
                    'names' => $data['names'][$i],
                    'room_id' => $data['room_id'][$i]
                ]);
            }
            if ($insert) {
                $this->order->accompanying()->saveMany($insert);
            }
        }
    }

    protected function processRoomnotes(): void
    {
        $this->order->roomnotes()->delete();

        if (request()->has('add_roomnotes') && request()->filled('order_roomnotes')) {
            $data = request('order_roomnotes');
            $insert = [];
            for ($i = 0; $i < count($data['note']); ++$i) {
                if (empty(trim($data['note'][$i])) or !$data['room_id']) {
                    continue;
                }
                $insert[] = new Order\RoomNote([
                    'note' => $data['note'][$i],
                    'room_id' => $data['room_id'][$i],
                    'user_id' => auth()->id()
                ]);
            }
            if ($insert) {
                $this->order->roomnotes()->saveMany($insert);
            }
        }
    }

    protected function processPayerData(FormRequest $request): void
    {
        if (!$this->as_orator) {
            $this->payerData = $request->validated(('payer'));
        }
        $this->payerData['account_id'] = $request->validated('selected_client_id');
        if (request()->has('samepayer')) {
            $this->payerData['account_type'] = $this->as_orator ? OrderClientType::CONGRESS->value : $request->validated('order.client_type');
        }

        if ($this->as_orator) {
            $this->payerData = Arr::only($this->payerData, ['account_id', 'account_type']);
        }

        $this->order->invoiceable
            ? $this->order->invoiceable->update($this->payerData)
            : $this->order->invoiceable()->save(new Order\Invoiceable($this->payerData));

    }

    protected function updateInvoiceableAddress(): void
    {
        $invoiceableAddress = (new UpdateAccountAddressAction($this->payerData))->update();

        $this->order->invoiceable->address_id = $invoiceableAddress->getAddressId();
        $this->order->invoiceable->save();
    }

    protected function deleteTempStock(): void
    {
        Order\StockTemp::where('uuid', request('order_uuid'))->delete();
    }

}
