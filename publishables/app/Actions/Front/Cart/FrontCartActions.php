<?php

namespace App\Actions\Front\Cart;

use App\Accessors\EventContactAccessor;
use App\Accessors\EventManager\Sellable\Deposits;
use App\Accessors\EventManager\SellableAccessor;
use App\Accessors\Front\FrontCache;
use App\Accessors\Front\FrontCartAccessor;
use App\Accessors\Order\Cart\ServiceCarts;
use App\Accessors\Order\Orders;
use App\Actions\Order\StockActions;
use App\Enum\OrderClientType;
use App\Enum\OrderType;
use App\Models\Event;
use App\Models\EventContact;
use App\Models\EventManager\Accommodation;
use App\Models\EventManager\Sellable;
use App\Models\EventService;
use App\Models\FrontCart;
use App\Models\FrontCartLine;
use App\Models\Order\Cart\ServiceCart;
use App\Models\Order\EventDeposit;
use App\Models\Order\StockTemp as TempStock;
use App\Services\Pec\PecParser;
use App\Traits\CheckParameters;
use App\Traits\Front\Cart\FrontCartTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use MetaFramework\Accessors\VatAccessor;

class FrontCartActions
{
    use CheckParameters;
    use FrontCartTrait;

    private Event $event;

    private FrontCartAccessor $frontCartAccessor;

    private ?EventContact $eventContact;
    private FrontCart $cart;


    public function __construct()
    {
        $this->enableAjaxMode();
        $this->eventContact      = FrontCache::getEventContact();
        $this->frontCartAccessor = new FrontCartAccessor();
        $this->cart              = $this->frontCartAccessor->getCart();
    }

    public function hasPecStayLines()//: bool
    {
        return (bool)$this->frontCartAccessor->getStayLines()->filter(fn($line)=> (int)$line->total_pec > 0)->count();
    }


    public function addService($serviceId, $quantity, bool $force = false): array
    {
        try {
            return $this->doUpdateServiceQuantity($serviceId, $quantity, force: $force);
        } catch (Exception $e) {
            $this->responseError($e->getMessage());
        }

        return $this->fetchResponse();
    }


    public function updateServiceQuantity($serviceId, $quantity, bool $force = false): array
    {
        try {
            return $this->doUpdateServiceQuantity($serviceId, $quantity, isAdd: false, force: $force);
        } catch (Exception $e) {
            $this->responseError($e->getMessage());
        }

        return $this->fetchResponse();
    }

    public static function deleteCartLine(FrontCartLine $cartLine): void
    {
        $cart = $cartLine->cart;
        if ($cartLine->shoppable_type == Sellable::class) {
            $cartLine->delete();
            $cart->load("lines");

            if ($cart->lines->isEmpty()) {
                $cart->delete();
            }
        } elseif ($cartLine->shoppable_type == "stay") {
            StockActions::clearFrontTempStock($cartLine);
            $cartLine->delete();
            $cart->load("lines");
            if ($cart->lines->isEmpty()) {
                $cart->delete();
            }
        } else {
            switch ($cartLine->shoppable_type) {
                case OrderType::GRANTDEPOSIT->value:
                case "grant_processing_fee":
                    $cartLine->delete();
                    break;
                default:
                    Log::warning("FrontCartLine deletion not implemented yet for type $cartLine->shoppable_type");
            }
        }
    }

    /**
     * @throws Exception
     */
    private function doUpdateServiceQuantity(int $serviceId, int $quantity, bool $isAdd = true, bool $force = false): array
    {
        if ($isAdd && ! $quantity) {
            $this->responseError(__('front/cart.wrong_quantity_input', ['quantity' => 0]));

            return $this->fetchResponse();
        }

        $service = Sellable::query()->where('id', $serviceId)->with("prices")->first();

        if ($service) {
            $serviceLine = $this->frontCartAccessor->getServiceLineByServiceId($serviceId);

            if ( ! $serviceLine) {
                if ( ! $quantity) {
                    $this->responseError("ServiceCart not found with id=$serviceId");

                    return $this->fetchResponse();
                }
                $serviceLine                 = new FrontCartLine();
                $serviceLine->front_cart_id  = $this->cart->id;
                $serviceLine->shoppable_type = Sellable::class;
                $serviceLine->shoppable_id   = $serviceId;
                $serviceLine->quantity       = 0;
                $serviceLine->vat_id         = $service->vat_id ?? VatAccessor::defaultId();
            }

            if ( ! $quantity) {
                $this->checkCanRemoveService($service);
                if ($this->hasErrors()) {
                    return $this->fetchResponse();
                }
                $serviceLine->delete();
            } else {
                if ( ! $force && $this->checkServiceDateOverlapping($service)) {
                    return $this->fetchResponse();
                }

                $meta                      = [];
                $unitPriceTtc              = SellableAccessor::getRelevantPrice($service);
                $serviceLine->unit_ttc     = $unitPriceTtc;
                $endQuantity               = $isAdd ? $serviceLine->quantity + $quantity : $quantity;
                $quantityToRemoveFromStock = $endQuantity - $serviceLine->quantity;

                $this->checkServiceAvailability($service, $quantityToRemoveFromStock);
                if ($this->hasErrors()) {
                    return $this->fetchResponse();
                }

                $totalPriceTtc = $unitPriceTtc * $endQuantity;
                $totalPriceNet = VatAccessor::netPriceFromVatPrice($unitPriceTtc, $service->vat_id);

                if ($service->deposit) {
                    $meta['deposit_net']    = Deposits::getSellableDepositNetAmount($service->deposit);
                    $meta['deposit_ttc']    = Deposits::getSellableDepositAmount($service->deposit);
                    $meta['deposit_vat_id'] = $service->deposit->vat_id;

                    $effectivePriceTtc = $totalPriceTtc - $meta['deposit_ttc'];
                    $totalPriceNet     = VatAccessor::netPriceFromVatPrice($effectivePriceTtc, $service->vat_id);
                }

                $totalPec = 0;
                if ($this->eventContact->pec_enabled) {
                    if ($service->pec_eligible) {
                        $pec = new PecParser($this->eventContact->event, collect()->push($this->eventContact));
                        $pec->calculate();

                        if ($pec->hasGrants($this->eventContact->id)) {
                            $totalPec = $totalPriceTtc;
                            if ($service->deposit) {
                                $totalPec -= $meta['deposit_ttc'];
                            }
                        }
                    }
                }

                $serviceLine->quantity  = $endQuantity;
                $serviceLine->total_net = $totalPriceNet;
                $serviceLine->total_ttc = $totalPriceTtc;
                $serviceLine->total_pec = $totalPec;
                $serviceLine->meta_info = $meta;

                $serviceLine->save();
            }

            $this->responseElement("serviceId", $service->id);
            $this->responseElement("stockQuantity", StockActions::fetchAvailableStock($service->id));
        } else {
            $this->responseError('ServiceCart not found with id='.$serviceId);
        }

        return $this->fetchResponse();
    }


    /**
     * @throws Exception
     */
    private function checkServiceDateOverlapping(Sellable $service): bool
    {
        $serviceLines = $this->frontCartAccessor->getServiceLines();

        $serviceCarts = ServiceCarts::getServiceCartsByEventContact(
            $this->cart->eventContact,
        );

        $overlapOrigin       = null;
        $overlappingItem     = null;
        $serviceDateOverlaps = $serviceLines
            ->concat($serviceCarts)
            ->contains(
                function ($item) use ($service, &$overlapOrigin, &$overlappingItem) {
                    if ($item instanceof FrontCartLine) {
                        $itemService   = $item->shoppable;
                        $overlapOrigin = "cart";
                    } elseif ($item instanceof ServiceCart) {
                        $itemService   = $item->service;
                        $overlapOrigin = "order";
                    } else {
                        throw new Exception("Not implemented for type ".get_class($item));
                    }

                    if (
                        is_null($itemService->service_date)
                        || is_null($service->service_date)
                        || $itemService->id == $service->id
                    ) {
                        return false;
                    }

                    $defaultStartTime = '00:00:00';
                    $defaultEndTime   = '23:59:59';

                    $existingServiceDate  = $itemService->getRawOriginal("service_date") ?: '';
                    $existingServiceStart = $itemService->getRawOriginal("service_starts") ?: $defaultStartTime;
                    $existingServiceEnd   = $itemService->getRawOriginal("service_ends") ?: $defaultEndTime;

                    $newServiceDate  = $service->getRawOriginal("service_date") ?: '';
                    $newServiceStart = $service->getRawOriginal("service_starts") ?: $defaultStartTime;
                    $newServiceEnd   = $service->getRawOriginal("service_ends") ?: $defaultEndTime;

                    $oldStart = Carbon::parse($existingServiceDate.' '.$existingServiceStart);
                    $oldEnd   = Carbon::parse($existingServiceDate.' '.$existingServiceEnd);
                    $newStart = Carbon::parse($newServiceDate.' '.$newServiceStart);
                    $newEnd   = Carbon::parse($newServiceDate.' '.$newServiceEnd);

                    $res = $newStart->lessThan($oldEnd) && $newEnd->greaterThan($oldStart);
                    if ($res) {
                        $overlappingItem = $item;
                    }

                    return $res;
                },
            );

        if ($serviceDateOverlaps) {
            $expression     = $overlapOrigin === "cart" ? "votre panier" : "l'une de vos commandes";
            $prestationName = "non définie";
            if ($overlappingItem) {
                if ($overlappingItem instanceof FrontCartLine) {
                    $prestationName = $overlappingItem->shoppable->title;
                } elseif ($overlappingItem instanceof ServiceCart) {
                    $prestationName = $overlappingItem->service->title;
                } else {
                    $this->responseError("Not implemented for type ".get_class($overlappingItem));
                }
            }

            $this->responseElement(
                "confirm",
                __('front/cart.confirm_overlapping', ['expression' => $expression, 'servicename' => $prestationName]),
            );

            return true;
        }

        return false;
    }


    private function checkServiceAvailability(Sellable $service, int $askedQuantity): void
    {
        $serviceLines = $this->frontCartAccessor->getServiceLines();

        if ( ! $service->stock_unlimited) {
            $stock = StockActions::fetchAvailableStock($service->id);
            if ($askedQuantity > 0 && $askedQuantity > $stock) {
                if ( ! $stock) {
                    $this->responseError(__('front/cart.out_of_stock'));
                } else {
                    $this->responseError(__('front/cart.insufficient_stock', ['quantity' => $askedQuantity]));
                }

                return;
            }
        }

        if ($service->pec_eligible) {
            $maxNbPec = $service->pec_max_pax;
            if ($this->eventContact->is_pec_eligible) {
                $serviceCarts      = ServiceCarts::getServiceCartsByEventContact($this->eventContact);
                $serviceCollection = $serviceLines->concat($serviceCarts);
                $nbMatching        = 0;
                $serviceCollection->each(function ($serviceItem) use ($service, &$nbMatching) {
                    if ($service->id == $serviceItem->shoppable_id) {
                        $nbMatching += $serviceItem->quantity;
                    }
                });

                if ($nbMatching >= $maxNbPec) {
                    $this->responseError(__('front/cart.pec_maxed_out'));

                    return;
                }
            }
        }

        $combinedService = $service->groupCombined;
        if ($combinedService) {
            $hasGroupCombined = $serviceLines->contains(function ($item) use ($service) {
                return $service->service_group_combined === $item->shoppable->service_group;
            });
            if ( ! $hasGroupCombined) {
                $orders = EventContactAccessor::getOrdersWithServices($this->eventContact);
                foreach ($orders as $order) {
                    if (Orders::orderContainsServiceOfFamily($order, $combinedService->id)) {
                        $hasGroupCombined = true;
                    }
                }

                if ( ! $hasGroupCombined) {
                    $this->responseError(__('front/cart.conditional_buy', ['service' => $combinedService->name]));

                    return;
                }
            }
        }

        $eventServiceConfig = EventService::where("event_id", $this->eventContact->event_id)
            ->where("service_id", $service->service_group)
            ->first();

        if ($eventServiceConfig) {
            if ( ! $eventServiceConfig->unlimited) {
                $service_group_id    = $eventServiceConfig->service_id;
                $max                 = $eventServiceConfig->max;
                $date_does_not_count = $eventServiceConfig->service_date_doesnt_count;

                $serviceCarts      = ServiceCarts::getServiceCartsByEventContact($this->cart->eventContact);
                $serviceCollection = $serviceLines->concat($serviceCarts);

                if ($date_does_not_count) {
                    $nbMatchingServices = 0;

                    $serviceCollection->each(
                    /**
                     * @throws Exception
                     */ function ($serviceItem) use ($service_group_id, &$nbMatchingServices) {
                        if ($serviceItem instanceof FrontCartLine) {
                            $service = $serviceItem->shoppable;
                        } elseif ($serviceItem instanceof ServiceCart) {
                            $service = $serviceItem->service;
                        } else {
                            throw new Exception("Not implemented for type ".get_class($serviceItem));
                        }

                        if ($service->service_group === $service_group_id) {
                            $nbMatchingServices += $serviceItem->quantity;
                        }
                    },
                    );

                    if ($service->service_group === $service_group_id) {
                        $nbMatchingServices += $askedQuantity;
                    }
                    if ($nbMatchingServices > $max) {
                        $type = $service->group->name;
                        $this->responseError(__('front/cart.buy_treshold', ['max' => $max, 'type' => $type]));
                    }
                } else {
                    $desiredDate        = $service->service_date;
                    $nbMatchingServices = 0;

                    $serviceCollection->each(
                        function ($serviceItem) use ($service_group_id, &$nbMatchingServices, $service, $askedQuantity, $desiredDate) {
                            if ($serviceItem instanceof FrontCartLine) {
                                $service = $serviceItem->shoppable;
                            } elseif ($serviceItem instanceof ServiceCart) {
                                $service = $serviceItem->service;
                            } else {
                                $this->responseError("Not implemented for type ".get_class($serviceItem));
                            }

                            if ($service->service_group === $service_group_id && $service->service_date === $desiredDate) {
                                $nbMatchingServices += $serviceItem->quantity;
                            }
                        },
                    );
                    $nbMatchingServices += $askedQuantity;

                    if ($nbMatchingServices > $max) {
                        $type = $service->group->name;
                        $this->responseError(__('front/cart.buy_treshold', ['max' => $max, 'type' => $type, 'date' => $service->service_date]));
                    }
                }
            }
        }
    }

    public function addStay(array $stayData): bool|array
    {
        $errors = [];

        // TODO : get rid of event_contact_id EVERYWHERE
        try {
            $this->checkParamsExist([
                'event_contact_id',
                'date_start',
                'date_end',
                'room_group',
                'room',
                'nb_person',
                'total_ttc',
                'total_pec',
                'price_per_night',
                'pec_per_night',
                'accompanying_details',
                'processing_fee',
                'processing_fee_vat_id',
                'comment',
            ], $stayData);

            if (
                $stayData['amendable'] != 'cart'
                && (
                    empty($stayData['date_start'])
                    || empty($stayData['date_end'])
                )
            ) {
                $errors[] = __('front/cart.specify_dates');
                goto add_stay_end;
            }

            $accommodation = $stayData['room_group']->accommodation;

            $totalTtc   = $stayData['total_ttc'];
            $totalPec   = $stayData['total_pec'];
            $userAmount = $totalTtc - $totalPec;

            $totalNet = VatAccessor::netPriceFromVatPrice(
                $userAmount,
                $accommodation->vat_id,
            );

            $hotel = $accommodation->hotel;

            $participation_type_id = $this->eventContact->participation_type_id;

            $processingFeeTtc = $stayData['processing_fee'];
            $processingFeeVat = 0;
            if ($accommodation->processingFeeVat) {
                $processingFeeVatRate = $accommodation->processingFeeVat->rate / 100;
                $processingFeeVat     = $processingFeeTtc - ($processingFeeTtc / (1 + ($processingFeeVatRate / 100)));
            }

            // TODO: get rid of event_contact_id
            $details = [
                'event_contact_id' => $stayData['event_contact_id'],
                'accommodation_id'             => $accommodation->id,
                'hotel_name'                   => $hotel->name,
                'date_start'                   => $stayData['date_start'],
                'date_end'                     => $stayData['date_end'],
                'room_group_id'                => $stayData['room_group']->id,
                'room_group_name'              => $stayData['room_group']->name,
                'room_id'                      => $stayData['room']->id,
                'room_name'                    => $stayData['room']->room->name,
                'nb_person'                    => $stayData['nb_person'],
                'vat_id'                       => $accommodation->vat_id,
                'price_per_night'              => $stayData['price_per_night'],
                'pec_per_night'                => $stayData['pec_per_night'],
                'participation_type_id'        => $participation_type_id,
                'accompanying_details'         => $stayData['accompanying_details'],
                'comment'                      => $stayData['comment'],
                'processing_fee_ttc'           => $processingFeeTtc,
                'processing_fee_vat'           => $processingFeeVat,
                'processing_fee_vat_id'        => $stayData['processing_fee_vat_id'],
                'amendable'                    => $stayData['amendable'],
                'amendable_order_id'           => $stayData['amendable_order_id'],
                'amendable_id'                 => $stayData['amendable_id'],
                'amendable_amount'             => $stayData['amendable_amount'],
            ];

            $cartLine                 = new FrontCartLine();
            $cartLine->front_cart_id  = $this->cart->id;
            $cartLine->shoppable_type = "stay";
            $cartLine->shoppable_id   = 0;
            $cartLine->quantity       = 1;
            $cartLine->unit_ttc       = $userAmount;
            $cartLine->vat_id         = $accommodation->vat_id;
            $cartLine->meta_info      = $details;
            $cartLine->total_net      = $totalNet;
            $cartLine->total_ttc      = $totalTtc;
            $cartLine->total_pec      = $totalPec;
            $cartLine->save();

            $pricePerNight = $stayData['price_per_night'];
            foreach ($pricePerNight as $date => $price) {
                $tempStock                        = new TempStock();
                $tempStock->shoppable_type        = Accommodation\RoomGroup::class;
                $tempStock->shoppable_id          = $details['room_group_id'];
                $tempStock->date                  = $date;
                $tempStock->quantity              = 1;
                $tempStock->room_id               = $details['room_id'];
                $tempStock->participation_type_id = $this->eventContact->participation_type_id;
                $tempStock->account_type          = OrderClientType::CONTACT->value;
                $tempStock->account_id            = FrontCache::getAccount()->id;
                $tempStock->frontcartline_id      = $cartLine->id;
                $tempStock->save();
            }
        } catch (Exception $e) {
            $errors[] = "An exception occurred: ".$e->getMessage();
        }

        add_stay_end:
        if ($errors) {
            return $errors;
        }

        return true;
    }

    /**
     * Add Deposit Fee to front cart
     *
     * @param  EventDeposit  $deposit
     *
     * @return void
     */

    public function addGrantWaiverFees(EventDeposit $deposit): void
    {
        FrontCartLine::where(['front_cart_id' => $this->cart->id, 'shoppable_type' => OrderType::GRANTDEPOSIT->value])->delete();

        $model                 = new FrontCartLine();
        $model->front_cart_id  = $this->cart->id;
        $model->shoppable_type = OrderType::GRANTDEPOSIT->value;
        $model->shoppable_id   = $deposit->id;
        $model->unit_ttc       = $deposit->total_net + $deposit->total_vat;
        $model->quantity       = 1;
        $model->total_net      = $deposit->total_net;
        $model->total_ttc      = $deposit->total_net + $deposit->total_vat;
        $model->vat_id         = $deposit->vat_id;
        $model->meta_info      = [
            "grant_id"    => $deposit->shoppable_id,
            "grant_title" => $deposit->shoppable_label,
        ];
        $model->save();
    }

    public function clearCart(bool $replenishStock = true): void
    {
        FrontCart::setReplenishStock($replenishStock);
        $this->cart->delete();
        FrontCart::resetReplenishStock();
    }

}
