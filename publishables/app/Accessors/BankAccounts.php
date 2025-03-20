<?php

namespace App\Accessors;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

class BankAccounts
{

    public static string $signature = 'bank_accounts';

    /*
     * Returns cached value of database bank account records
     */
    public static function accounts(): EloquentCollection
    {
        return Cache::rememberForever(self::$signature, fn() => BankAccount::all()->sortBy('name'));
    }

    /*
     * Returns array of id => name of bank accounts
     */
    public static function selectables(): array
    {
        return static::accounts()->pluck('name','id')->toArray();
    }

    /*
     * Remets le cache des comptes bancaires à zéro
     */
    public static function resetCache(): void
    {
        Cache::forget(self::$signature);
    }

}
