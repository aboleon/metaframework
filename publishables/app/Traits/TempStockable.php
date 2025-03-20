<?php

namespace App\Traits;

use App\Enum\OrderClientType;
use App\Interfaces\Stockable;
use App\Models\Order\StockTemp;
use Illuminate\Validation\Rules\Enum;
use MetaFramework\Services\Validation\ValidationTrait;
use ReflectionClass;

trait TempStockable
{
    use ValidationTrait;

    private ?Stockable $shoppable = null;

    private ?string $shoppable_model = null;
    private ?int $shoppable_id = null;
    private int $quantity = 1;
    private int $prevalue = 0;
    private int $account_id;
    private string $account_type;
    private StockTemp $tempStock;
    private string $order_uuid;

    private function processStockTempObject(): void
    {
        $data = $this->setStockTempData();
        $this->tempStock = StockTemp::firstOrCreate($data);
        $this->tempStock->quantity = $this->quantity;
        $this->tempStock->account_type = $this->account_type;
        $this->tempStock->account_id = $this->account_id;
        $this->tempStock->identifier = $data['identifier'] ?? null;

        $this->tempStock->save();
    }

    private function setStockTempData(): array
    {
        $data = [
            'uuid' => $this->order_uuid,
            'shoppable_type' => $this->shoppable_model,
            'shoppable_id' => $this->shoppable_id
        ];

        if (request()->filled('identifier')) {
            $data['identifier'] = (string)request('identifier');
        }

        if (request()->filled('room_id')) {
            $data['room_id'] = request('room_id');
        }
        if (request()->filled('participation_type')) {
            $data['participation_type_id'] = (int)request('participation_type');
        }
        if (request()->filled('date')) {
            $data['date'] = request('date');
        }
        if (request()->filled('on_quota')) {
            $data['on_quota'] = (int)request('on_quota');
        }
        return $data;
    }

    private function validateStockableInput(): void
    {

        $this->validation_rules = [
            'shoppable_id' => 'required|integer',
            'shoppable_model' => 'required|string',
            'order_uuid' => 'required|string',
            'quantity' => 'required|int',
            'account_type' => [new Enum(OrderClientType::class)],
            'account_id' => 'required|integer'
        ];

        $this->validation_messages = [
            'shoppable_id.required' => "L'identifiant pour le traitement du stock est requis.",
            'shoppable_model.required' => "L'objet pour le traitement du stock est requis.",
            'order_uuid.required' => "L'identifant de la commande est requis.",
            'stockable_id.integer' => "L'identifiant pour le traitement du stock est incorrect.",
            'shoppable_model.string' => "L'objet pour le traitement du stock est incorrect.",
            'order_uuid.string' => "L'identifant de la commande est incorrect.",
            'quantity.required' => "La quantité du stock est requise.",
            'quantity.int' => "La quantité du stock est incorrecte.",
            'account_id.required' => __('validation.required', ['attribute' => "Le compte d'affectation"]),
        ];


        $this->validation();

        $this->shoppable_model = (string)$this->validatedDataStringable('shoppable_model');
        $this->shoppable_id = (int)$this->validatedDataStringable('shoppable_id');
        $this->order_uuid = (string)$this->validatedDataStringable('order_uuid');
        $this->quantity = (int)$this->validatedDataStringable('quantity');
        $this->account_type = (string)$this->validatedDataStringable('account_type');
        $this->account_id = (int)$this->validatedDataStringable('account_id');

    }

    private function setShoppable(): void
    {
        $reflection = new ReflectionClass($this->shoppable_model);
        $this->shoppable = $reflection->newInstance()->find($this->shoppable_id);
        $this->prevalue = (int)request('prevalue');
    }
}
