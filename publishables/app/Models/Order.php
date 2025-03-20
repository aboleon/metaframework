<?php

namespace App\Models;

use App\Accessors\OrderAccessor;
use App\Enum\OrderCartType;
use App\Enum\OrderClientType;
use App\Enum\OrderOrigin;
use App\Enum\OrderStatus;
use App\Enum\OrderType;
use App\Enum\PaymentMethod;
use App\Interfaces\CustomPaymentInterface;
use App\Models\EventManager\Grant\Quota;
use App\Models\Order\Accompanying;
use App\Models\Order\Cart\AccommodationAttribution;
use App\Models\Order\Cart\AccommodationCart;
use App\Models\Order\EventDeposit;
use App\Models\Order\Invoiceable;
use App\Models\Order\Note;
use App\Models\Order\Refund;
use App\Models\Order\RoomNote;
use App\Services\PaymentProvider\PayBox\TransactionRequest;
use App\Services\Pec\PecDistributionResult;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use MetaFramework\Casts\PriceInteger;
use MetaFramework\Traits\Responses;

/**
 * @property int                   $id
 * @property int                   $total_net
 * @property int                   $total_vat
 * @property int                   $total_pec
 * @property Carbon|null           $created_at
 * @property int|null              $external_invoice
 * @property mixed                 $po
 * @property mixed                 $note
 * @property mixed                 $terms
 * @property bool                  $pecAuthorized
 * @property PecDistributionResult $pecDistribution
 * @property EloquentCollection    $pecDistributions
 * @property EloquentCollection    $services
 * @property EloquentCollection    $accommodation
 * @property EloquentCollection    $taxroom
 * @property EloquentCollection    $grantDeposit
 * @property EloquentCollection    $sellableDeposit
 * @property Invoiceable           $invoiceable
 * @property int                   $client_id
 * @property Event                 $event
 * @property EloquentCollection    $taxRoom
 * @property string                $client_type
 * @property Account               $account
 * @property Group                 $group
 * @property string                $marker
 * @property int                   $created_by
 * @property int                   $event_id
 * @property Carbon|string|null    $cancellation_request
 * @property Carbon|string|null    $cancelled_at
 * @property int|null              $binded_to_order
 * @property int|null              $amendableAccommodation
 * @property EloquentCollection    $payments
 * @property null|int              $amended_order_id
 * @property null|int              $amended_by_order_id
 * @property null|Order|BelongsTo  $amendedByOrder
 * @property null|string $amend_type | OrderAmendedType
 * @property null|Order|BelongsTo  $amendedOrder
 * @property null|Order            $parentOrder
 * @property EloquentCollection    $accommodationAttributions
 * @property string|OrderType      $type
 * @property HasOne|PaymentCall  $paymentCall |tb front_payment_calls
 * @property HasOne|CustomPaymentCall  $customPaymentCall |tb payment_calls
 * @property HasOne|EventDeposit   $grantDepositRecord
 * @property array                 $configs
 * @property string                $cancellation_status
 */
class Order extends Model implements CustomPaymentInterface
{
    use HasFactory;
    use Responses;

    public OrderAccessor $accessor;
    private PecDistributionResult $pecDistribution;
    private bool $pecAuthorized = false;

    protected ?int $beneficiary_event_contact_id = null;

    // not part of the DB model
    protected ?AccommodationCart $amendedAccommodationCart = null;

    protected $fillable
        = [
            'event_id',
            'uuid',
            'order_ref',
            'client_id',
            'client_type',
            'total_net',
            'total_vat',
            'total_pec',
            'status',
            'created_by',
            'origin',
            'type',
            'external_invoice',
            'po',
            'note',
            'terms',
            'paybox_num_trans',
            'paybox_num_appel',
            'amended_order_id',
            'amended_by_order_id',
            'amend_type',
            'as_group',
            'parent_id',
            'configs',
        ];

    protected $casts
        = [
            'total_net'            => PriceInteger::class,
            'total_vat'            => PriceInteger::class,
            'total_pec'            => PriceInteger::class,
            'cancellation_request' => 'datetime',
            'cancelled_at'         => 'datetime',
            'configs'              => 'array',
        ];

    public function __construct()
    {
        parent::__construct();
        $this->pecDistribution = new PecDistributionResult();
    }

    public function setAccessor(): void
    {
        $this->accessor = new OrderAccessor($this);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }


    public function accommodation(): HasMany
    {
        return $this->hasMany(Order\Cart\AccommodationCart::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Order\Cart\ServiceCart::class);
    }

    public function serviceAttribution(): HasMany
    {
        return $this->hasMany(Order\Cart\ServiceAttribution::class);
    }

    public function taxRoom(): HasMany
    {
        return $this->hasMany(Order\Cart\TaxRoomCart::class);
    }

    public function grantDeposit(): HasMany
    {
        return $this->hasMany(Order\Cart\GrantDepositCart::class);
    }

    public function sellableDeposit(): HasMany
    {
        return $this->hasMany(Order\Cart\SellableDepositCart::class);
    }

    public function invoiceable(): HasOne
    {
        return $this->hasOne(Invoiceable::class, 'order_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'order_id');
    }

    public function invoice(): ?Invoice
    {
        return $this->invoices->whereNull('proforma')->first();
    }

    public function proforma(): EloquentCollection
    {
        return $this->invoices->whereNotNull('proforma');
    }


    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function vat(): BelongsTo
    {
        return $this->belongsTo(Vat::class, 'vat_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }


    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'client_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'client_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accompanying(): HasMany
    {
        return $this->hasMany(Accompanying::class);
    }

    public function roomnotes(): HasMany
    {
        return $this->hasMany(RoomNote::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function frontTransactions(): HasMany
    {
        return $this->hasMany(FrontTransaction::class, 'order_uuid', 'uuid');
    }

    public function scopeFilters($query, array $filter): Builder
    {
        if ($filter) {
            $query->where($filter);
        }

        return $query;
    }

    public function scopeWithRelations($query, array $relations): Builder
    {
        if ($relations) {
            $query->with($relations);
        }

        return $query;
    }

    public function pecDistributions(): HasMany
    {
        return $this->hasMany(PecDistribution::class, 'order_id');
    }

    public function pecQuota(): HasMany
    {
        return $this->hasMany(Quota::class, 'order_id');
    }

    public function setBeneficiary(?int $id = null): self
    {
        if ($id) {
            $this->event_contact_id = $id;
        }

        return $this;
    }

    // TODO A virer
    public function getBeneficiary(): ?int
    {
        return $this->event_contact_id;
    }

    public function client(): Account|Group|null
    {
        return $this->client_type == OrderClientType::GROUP->value ? $this->group : $this->account;
    }

    public function hasAnyPayments(): bool
    {
        return $this->payments->isNotEmpty();
    }

    public function hasPendingCancellation(): bool
    {
        return ! is_null($this->cancellation_request) && is_null($this->cancelled_at);
    }

    public function isCancelled(): bool
    {
        return ! is_null($this->cancelled_at);
    }

    //public function amendableAccommodation(): BelongsTo
    public function amendedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'amended_order_id');
    }

    public function amendedByOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'amended_by_order_id');
    }

    public function amendedAccommodation(): ?AccommodationCart
    {
        return $this->amendedOrder->accommodation->first();
    }

    public function setAmendedAccommodationCart(AccommodationCart $cart): self
    {
        $this->amendedAccommodationCart = $cart;

        return $this;
    }

    public function getAmendedAccommodationCartId(): ?int
    {
        return $this->amendedAccommodationCart?->id;
    }

    public function paymentCall(): HasOne
    {
        return $this->hasOne(PaymentCall::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(FrontTransaction::class);
    }

    public function suborders(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }

    public function accommodationAttributions(): HasMany
    {
        return $this->hasMany(
            AccommodationAttribution::class,
            'order_id',
        )->where('shoppable_type', OrderCartType::ACCOMMODATION->value);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(EventDeposit::class, 'order_id');
    }

    public function grantDepositRecord(): HasOne
    {
        return $this->hasOne(EventDeposit::class, 'order_id');
    }

    # Custom Payment Call methods
    public function customPaymentCall(): MorphOne
    {
        return $this->morphOne(CustomPaymentCall::class, 'shoppable');
    }

    public function renderCustomPaymentForm(): string
    {
        return '';
    }

    public function paymentStateMessage(): string
    {
        return __('ui.payment_state.'.$this->customPaymentCall->state);
    }

    public function processSuccessCustomPayment()
    {
        # Order
        $this->paybox_num_trans = TransactionRequest::transactionId();
        $this->paybox_num_appel = TransactionRequest::callId();
        $this->status           = OrderStatus::PAID->value;
        $this->save();
        $this->responseSuccess("Order updated");

        # Transaction

        $this->customPaymentCall->transaction()->save(
            (new PaymentTransaction([
                'transaction_call_id' => TransactionRequest::callId(),
                'transaction_id'      => TransactionRequest::transactionId(),
                'return_code'         => TransactionRequest::returnCode(),
                'details'             => request()->all(),
            ])),
        );

        $this->responseSuccess("Transaction stored");

        # Payment
        $this->payments()->save(
            (new Payment([
                'order_id'           => $this->id,
                'date'               => now(),
                'payment_method'     => PaymentMethod::CB_PAYBOX->value,
                'transaction_id'     => $this->customPaymentCall->transaction->id,
                'amount'             => $this->customPaymentCall->total,
                'transaction_origin' => OrderOrigin::BACK->value,
            ])),
        );
        $this->responseSuccess("Payment stored");

        #Invoice
        $this->setAccessor();
        if ($this->accessor->isFullyPaid()) {
            Invoice::firstOrcreate(['order_id' => $this->id], ['created_by' => $this->client_id]);
        }
        $this->responseSuccess("Invoice generated");
    }

    public function sendPaymentMail(CustomPaymentCall $paymentCall) {}

    public function sendPaymentResponseNotification(CustomPaymentCall $paymentCall): array
    {
        return [];
    }

    public function getEventContact(): EventContact
    {
        return EventContact::where(['event_id' => $this->event_id, 'user_id' => $this->client_id])->first();
    }
}
