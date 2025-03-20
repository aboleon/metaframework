<?php

namespace App\Actions\Order;

use App\Accessors\OrderAccessor;
use App\DataTables\View\EventSellableServiceStockView;
use App\Models\EventManager\Sellable;
use App\Models\FrontCartLine;
use App\Models\Order;
use App\Models\Order\StockTemp;
use App\Traits\TempStockable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use MetaFramework\Traits\Ajax;
use Throwable;

class StockActions
{
    use Ajax;
    use TempStockable;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();
        $this->fetchCallback();
    }

    public function decreaseShoppableStock(): array
    {
        //d(request()->all());
        $this->validateStockableInput();

        try {
            $this->setShoppable();

            // Is it possible ?

            if ($this->quantity > $this->getAvailableStock()) {
                $this->responseError("Aucun stock n'est disponible");

                return $this->fetchResponse();
            }

            $this->processStockTempObject();

            if ( ! $this->shoppable->stock_unlimited) {
                $this->responseElement('updated_stock', $this->getAvailableStock());
                $this->responseSuccess("Le stock pour ".$this->shoppable->title." a été mis à jour");
            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function increaseShoppableStock(): array
    {
        $this->validateStockableInput();

        try {
            $this->setShoppable();

            $this->processStockTempObject();

            if ( ! $this->shoppable->stock_unlimited) {
                $this->responseElement('updated_stock', $this->getAvailableStock());
                $this->responseSuccess("Le stock pour ".$this->shoppable->title." a été remis.");
            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function clearServicesTempStock()
    {
        $records = StockTemp::query()
            ->where('created_at', '<', Carbon::now()->subMinutes(15))
            ->where('shoppable_type', Sellable::class)
            ->get()
            ->groupBy('shoppable_id')
            ->map(fn($sellable) => $sellable->sum('quantity'))
            ->toArray();


        if ($records) {
            DB::beginTransaction();
            try {
                foreach ($records as $shoppable_id => $stock) {
                    Sellable::query()->where('id', $shoppable_id)->increment('stock', $stock);
                }
                StockTemp::where('shoppable_type', Sellable::class)->delete();

                DB::commit();
            } catch (Throwable $e) {
                $this->responseException($e);
                DB::rollBack();
            }

            return $this;
        }
    }

    public function restoreShoppableStock(Order $order): self
    {
        try {
            $orderAccessor = (new OrderAccessor($order));

            if ($orderAccessor->serviceCart()) {
                foreach ($orderAccessor->serviceCart() as $stockable) {
                    $sellable = Sellable::find($stockable->service_id);

                    if ( ! $sellable->stock_unlimited) {
                        $sellable->stock += $stockable->quantity;
                        $sellable->save();
                    }
                }
            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this;
    }

    private function getAvailableStock(): int
    {
        return self::fetchAvailableStock($this->shoppable_id);
    }

    public static function fetchAvailableStock(int $shoppable_id): int
    {
        return EventSellableServiceStockView::where('id', $shoppable_id)->value('available');
    }

    public static function clearFrontTempStock(FrontCartLine $cartLine): void
    {
        $cartLine->tempStock()->delete();
    }

}
