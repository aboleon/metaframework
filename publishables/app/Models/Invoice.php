<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;
    protected $table = "order_invoices";
    public $timestamps = false;
    protected $fillable = [
        'order_id',
        'cancelled_at',
        'created_by',
        'invoice_number',
        'proforma'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'proforma' => 'boolean'
    ];


    protected static function booted()
    {
        static::created(function ($invoice) {
            // todo
            // https://trello.com/c/y6dunPis/374-prios-stp
            /**
             * prévoir que : quand statut de la commande passe en "facturé", un mail auto est envoyé vers pax avec lien vers facture (que la commande ait été faite en bo et "facturée" manuellement ou faite en front et "facturée" automatiquement)
             *
             * ---> Uniquement pour pax? --> wait for answer...
             */
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
