<?php

namespace App\Actions\Order;

use App\Abstract\OrderCart;
use App\Accessors\EventAccessor;
use App\Accessors\OrderAccessor;
use App\Models\Order;
use App\Models\Order\Cart\ServiceCart;
use App\Models\Order\StockTemp;
use App\Traits\TempStockable;
use MetaFramework\Traits\Ajax;
use ReflectionClass;
use Throwable;

class ServiceCartActions extends OrderCart
{
    use Ajax;
    use TempStockable;

    protected ?ServiceCart $cart = null;


    public function setCartFromId(int $cart_id): self
    {
        $this->cart = ServiceCart::find($cart_id);

        return $this;
    }

    public function updateOrderCartFromRowManipulation(array $data): self
    {
        $this->missingCartAlert();

        if ( ! $this->cart) {
            return $this;
        }

        $this->cart->quantity  = $data['quantity'] ?? 0;
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


        return $this->fetchResponse();
    }

    public function cancelServicePriceRow(): array
    {
        $this->setCartFromId((int)request('service_cart_id'));

        if ($this->cart) {
            $this->cancelRowFromCart();
        } else {
            $this->responseError("Impossible de trouver un cet élément dans la commande.");
        }

        return $this->fetchResponse();
    }


    /**
     * @throws \ReflectionException
     */
    private function removeRowFromTemporaryStock(): void
    {
        $order_uuid      = (string)request('order_uuid');
        $uuid            = (string)request('uuid');
        $shoppable_id    = (int)request('shoppable_id');
        $shoppable_model = (string)request('shoppable_model');

        $stockable = StockTemp::where([
            'uuid'           => $order_uuid,
            'identifier'     => $uuid,
            'shoppable_type' => $shoppable_model,
            'shoppable_id'   => $shoppable_id,
        ])->delete();

        $this->responseElement('stockable', $stockable);

        if ( ! $stockable) {
            $this->responseError("Impossible de trouver un élément avec ces identifiants dans le stock temporaire.");
        } else {
            $this->responseElement('putBackInStock', StockActions::fetchAvailableStock($shoppable_id));

            $reflection = new ReflectionClass($shoppable_model);
            $shoppable  = $reflection->newInstance()->find($shoppable_id);

            $this->responseSuccess("Le stock pour ".$shoppable->title." a été mis à jour.");
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

            $this->responseSuccess("Le stock pour ".$this->shoppable->getStockableLabel()." été remis");

            $order = $this->cart->order;

            # Remettre la PEC à niveau
            if ($this->cart->total_pec) {
                $this->pushMessages(
                    (new PecActions())
                        ->enableAjaxMode()
                        ->setOrder($order)
                        ->resetServicePec($this->cart),
                );
            }

            $orderAccessor = (new OrderAccessor($order));
            $this->cart->delete();

            # Order Totals
            (new OrderActions())->setOrder($order)->updateTotals($orderAccessor->computeOrderTotalsFromCarts());

            $eventAccessor = (new EventAccessor($order->event));

            $this->responseElement('putBackInStock', $eventAccessor->availableSellableStockFor($this->cart->service_id));
            $this->responseSuccess("La ligne a été effacée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }
    }

    private function cancelRowFromCart(): void
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

            $time = now();
            $this->responseElement('cancelled_at', $time->format("d/m/Y à H\hi"));
            $this->cart->cancelled_at = $time;
            $this->cart->save();

            $this->responseSuccess("Le stock pour ".$this->shoppable->getStockableLabel()." été remis");
            $this->responseElement('putBackInStock', $this->cart->quantity);
        } catch (Throwable $e) {
            $this->responseException($e);
        }
    }


    private function missingCartAlert(): void
    {
        if ( ! $this->cart) {
            $this->responseWarning("Il semble que vous essayez de mettre à jour un ordre existant mais il n'a pas pu être retrouvé en ce qui concerne ".$this->shoppable->title);
        }
    }

    public function getOrder(): Order
    {
        return $this->cart->order;
    }

    public function getCart(): ServiceCart
    {
        return $this->cart;
    }


}
