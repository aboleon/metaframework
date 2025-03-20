<?php

namespace App\Actions\Order;

use App\Abstract\OrderCart;
use App\Accessors\OrderAccessor;
use App\Models\Order\Cart\AccommodationCart;
use App\Models\Order\Cart\ServiceCart;
use App\Models\Order\StockTemp;
use App\Traits\TempStockable;
use ReflectionClass;
use Throwable;

class AccommodationCartActions extends OrderCart
{
    use TempStockable;

    private ?AccommodationCart $cart = null;


    public function setCartFromId(int $cart_id): self
    {
        $this->cart = AccommodationCart::find($cart_id);
        return $this;
    }


    public function updateOrderCartFromRowManipulation(array $data): self
    {
        $this->missingCartAlert();

        if (!$this->cart) {
            return $this;
        }

        $this->cart->quantity = $data['quantity'] ?? 0;
        $this->cart->total_net = $data['total_net'] ?? 0;
        $this->cart->total_vat = $data['total_vat'] ?? 0;
        $this->cart->save();

        $this->responseSuccess("La commande a été mise à jour");

        return $this;
    }


    public function removeServicePriceRow(): array
    {
        $this->setCartFromId((int)request('service_cart_id'));

        $this->fetchInput();
        $this->fetchCallback();

        $this->cart
            ? $this->removeRowFromCart()
            : $this->removeRowFromTemporaryStock();

        //TODO: reatribute PEC

        return $this->fetchResponse();
    }


    private function removeRowFromTemporaryStock(): void
    {
        $order_uuid = (string)request('order_uuid');
        $shoppable_id = (int)request('shoppable_id');
        $shoppable_model = (string)request('shoppable_model');

        $stockable = StockTemp::where('uuid', $order_uuid)->where('shoppable_type', $shoppable_model)->where('shoppable_id', $shoppable_id)->first();

        $this->responseElement('stockable', $stockable);
        if (!$stockable) {

            $this->responseError("Impossible de trouve un élément avec ces identifiants dans le stock temporaire.");

        } else {

            try {

                $this->responseElement('putBackInStock', $stockable->quantity);

                $reflection = new ReflectionClass($shoppable_model);
                $shoppable = $reflection->newInstance()->find($shoppable_id);
                $shoppable->stock += $stockable->quantity;
                $shoppable->save();

                $stockable->delete();

                $this->responseSuccess("Le stock pour ".$shoppable->title." a été mis à jour.");

            } catch (Throwable $e) {
                $this->responseException($e);
            }
        }

    }

    private function removeRowFromCart(): void
    {
        $this->missingCartAlert();

        if ($this->hasErrors()) {
            $this->responseError("Aucune ligne n'a été trouvée.");
            return;
        }

        try {

            request()->merge(['quantity' => $this->cart->quantity]);

            $this->validateStockableInput();

            $this->setShoppable();
            $this->shoppable->stock += $this->cart->quantity;
            $this->shoppable->save();

            $this->responseSuccess("Le stock pour " . $this->shoppable->getStockableLabel() . " été remis");

            $order = $this->cart->order;

            $orderAccessor = (new OrderAccessor($order));
            $this->cart->delete();

            # Cart Totals
            $subtotals = $orderAccessor->serviceCartTotals();
            $this->responseElement('subtotals', $subtotals);

            # Order Totals
            (new OrderActions())->setOrder($order)->updateTotals($orderAccessor->computeOrderTotalsFromCarts());

            $this->responseElement('putBackInStock', $this->cart->quantity);

            $this->responseSuccess("La ligne a été effacée.");

        } catch (Throwable $e) {
            $this->responseException($e);
        }


    }


    private function missingCartAlert(): void
    {
        if (!$this->cart) {
            $this->responseWarning("Il semble que vous essayez de mettre à jour un ordre existant mais il n'a pas pu être retrouvé");
        }
    }


}
