<?php

namespace App\Actions\Order;

use App\Accessors\EventManager\Availability;
use App\Enum\OrderClientType;
use App\Models\Order\StockTemp;
use App\Traits\TempStockable;
use MetaFramework\Traits\Ajax;
use Throwable;

class ContingentActions
{
    use Ajax;
    use TempStockable;


    private Availability $availability;
    private array $roomGroup;
    private string $accountType;


    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();
        $this->fetchCallback();

        $this->setAccountType((string)request('account_type'));


        $this->validateStockableInput();

        $this->prevalue = (int)request('prevalue');
    }

    public function setAccountType(string $accountType): self
    {
        $this->accountType = in_array($accountType, OrderClientType::values()) ? $accountType : OrderClientType::default();

        return $this;
    }

    public function checkAvailability(): void
    {
        $this->availability = (new Availability())
            ->setEventAccommodation(request('event_accommodation_id'))
            ->setDate(request('date'))
            ->setRoomGroupId((int)request('shoppable_id'))
            ->setParticipationType((int)request('participation_type'))
            ->setEventGroupId(request('account_type') == 'group' ? (int)request('event_group_id') : 0);

        $this->roomGroup = $this->availability->getRoomGroup();
    }

    public function decreaseStock(): array
    {
        $this->checkAvailability();

        $this->responseElement('before_stock', $this->availability->getRoomGroupAvailability());

        $controlQty   = $this->quantity;
        $availability = $this->availability->getRoomGroupAvailability();

        if ($this->prevalue != 1 && $this->quantity != 1) {
            $controlQty = $this->quantity - $this->prevalue;
        }


        $this->responseElement('controlQty', $controlQty);
        $this->responseElement('block_booking', $availability < $controlQty);


        if ($availability < $controlQty) {
            $this->responseError("Il ne reste plus de la disponibilité ".$this->roomGroup['name'].' pour le '.$this->availability->get('dates')['formatted']['date']);
        }

        if ( ! $this->hasErrors()) {
            $this->prevalue = (int)request('prevalue');

            $this->processStockTempObject();
        }

        if ($this->hasErrors()) {
            return $this->fetchResponse();
        }

        try {
            // Ne pas mettre à jour le temp stock si on édite une commande, on l'a met directement à jour

            $this->responseElement('evaluated_stock_qty', $controlQty);


            $this->checkAvailability();

            $this->responseElement('updated_stock', $this->availability->getRoomGroupAvailability());
            $this->successMessage();
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function increaseStock(): array
    {
        $this->prevalue = (int)request('prevalue');

        $this->processStockTempObject();

        try {
            $this->checkAvailability();

            $this->responseElement('updated_stock', $this->availability->getRoomGroupAvailability());
            $this->successMessage();
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function clearTempStock(): array
    {
        try {
            StockTemp::where($this->setStockTempData())->delete();

            $this->checkAvailability();

            $this->responseElement('updated_stock', $this->availability->getRoomGroupAvailability());
            $this->responseElement('before_stock', request('before_stock'));
            $this->responseElement('shoppable_id', (int)request('shoppable_id'));
            $this->responseSuccess("La disponibilité a été remise.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    private function successMessage(): void
    {
        $this->responseSuccess("La disponibilité pour ".$this->roomGroup['name'].' pour le '.$this->availability->get('dates')['formatted']['date']." a été mise à jour");
    }


}
