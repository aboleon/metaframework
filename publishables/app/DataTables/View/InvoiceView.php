<?php

namespace App\DataTables\View;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceView extends Model
{
    protected $table = 'order_invoices_view';
    public $timestamps = false;
}
