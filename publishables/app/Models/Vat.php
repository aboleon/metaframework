<?php

namespace App\Models;

use App\Traits\Price;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Vat extends Model
{
    use HasFactory;
    use Price;

    public $timestamps = false;
    protected $table = 'vat';
    protected $fillable = [
        'rate',
        'default'
    ];

    public function manageDefaultState(): void
    {
        if ($this->default == 1) {
            Vat::where('id', '!=', $this->id)->update(['default' => 0]);
            Cache::forget('app_default_vat');
        }
    }

    public static function defaultVat(): ?self
    {
        return Cache::rememberForever('app_default_vat', fn() => Vat::where('default', 1)->first());
    }

    public static function readableArray(): array
    {
        return Vat::pluck('rate','id')->sortBy('default')->map(fn($item)=>number_format($item,2))->toArray();
    }

    public static function vats(): array
    {
        return Cache::rememberForever('app_vats', fn() => Vat::pluck('rate','id')->toArray());
    }

    public static function vatRate(?int $id): float
    {
        return self::vats()[$id] ?? self::defaultVat()->rate;
    }
}
