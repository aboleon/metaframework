<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use MetaFramework\Casts\PriceInteger;
use MetaFramework\Traits\Ajax;
use Throwable;

/**
 * @property mixed              $shoppable
 * @property string             $state
 * @property string             $uuid
 * @property null|string        $closed_at
 * @property int                $id
 * @property PaymentTransaction $transaction HasOne
 */
class CustomPaymentCall extends Model
{
    use Ajax;

    public $timestamps = false;
    protected $table = "payment_call";
    protected $fillable
        = [
            'provider',
            'shoppable_type',
            'shoppable_id',
            'total',
            'closed_at',
            'state',
        ];
    protected $casts
        = [
            'total'     => PriceInteger::class,
            'closed_at' => 'datetime',
        ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    public function shoppable(): MorphTo
    {
        return $this->morphTo();
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(PaymentTransaction::class, 'payment_call_id');
    }

    public function updateUuid(): self
    {
        $this->uuid = Str::uuid()->toString();

        return $this;
    }

    public function updateState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function encryptedId(): string
    {
        return Crypt::encryptString($this->id);
    }

    public function sendPaymentMail(): array
    {
        if ($this->closed_at) {
            $this->responseError("Ce paiement a déjà été effectué le ".$this->closed_at->format('d/m/ Y à H:i'));

            return $this->fetchResponse();
        }

        try {
            return $this->shoppable->sendPaymentMail($this);
        } catch (Throwable $e) {
            $this->responseException($e, "La demande de paiement n'a pas pu être envoyée.");
        }

        return $this->fetchResponse();
    }

    public function sendPaymentResponseNotification(): array
    {
        try {
            return $this->shoppable->sendPaymentResponseNotification($this);
        } catch (Throwable $e) {
            $this->responseException($e, "La notification de retour de paiement n'a pas pu être envoyée.");
        }

        return $this->fetchResponse();
    }
}
