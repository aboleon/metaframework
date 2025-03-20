<?php

namespace App\Accessors;

use App\DataTables\View\UserView;
use App\Models\Account;
use App\Models\AccountAddress;
use App\Models\AccountPhone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;

class Accounts
{
    public function __construct(public Account $account) {}

    public static function searchByKeyword(string $keyword, array $options = []): array
    {
        $event_group_id = $options['event_group_id'] ?? null;
        $sortBy         = $options['sortBy'] ?? null;

        $q = Account::query()
            ->select('users.first_name', 'users.last_name', 'users.email', 'users.id', 'b.locality', 'c.name->'.app()->getLocale().' as country', 'd.account_type', 'd.function');
        if ($event_group_id) {
            $q->addSelect(\DB::raw('(SELECT COUNT(*) FROM `event_groups` WHERE id='.(int)$event_group_id.' AND  main_contact_id=users.id) as is_main_contact'));
        }
        $q
            ->leftJoin('account_address as b', fn($join)
                => $join
                ->on('users.id', '=', 'b.user_id')->orderBy('billing', 'desc')->take(1)
                ->join('countries as c', 'c.code', '=', 'b.country_code'),
            )
            ->leftJoin('account_profile as d', 'd.user_id', '=', 'users.id')
            ->where(fn($query)
                => $query
                ->where('first_name', 'like', '%'.$keyword.'%')
                ->orWhere('last_name', 'like', '%'.$keyword.'%')
                ->orWhere('email', 'like', '%'.Str::slug($keyword).'%'),
            )
            ->groupBy('users.id');

        if ($sortBy) {
            $q->orderBy($sortBy);
        }


        return $q
            ->get()
            ->toArray();
    }


    public static function filter(?string $keyword = null, $options = []): Builder
    {
        $key         = $options['key'] ?? 'id';
        $value       = $options['value'] ?? 'text';
        $showTrashed = $options['showTrashed'] ?? false;

        $b = UserView::query()
            ->select('id as '.$key)
            ->addSelect(\DB::raw('CONCAT( first_name, " ", last_name, " (", COALESCE(locality, ""), " - ", COALESCE(country, ""), ")" ) as '.$value))
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q
                        ->where('first_name', 'like', '%'.$keyword.'%')
                        ->orWhere('last_name', 'like', '%'.$keyword.'%');
                });
            });
        if ( ! $showTrashed) {
            $b->whereNull('deleted_at');
        }

        return $b;
    }

    public static function getAccountByEmail(string $email, bool $withDeleted = false): ?Account
    {
        $query = Account::query();

        if ($withDeleted) {
            $query->withTrashed();
        }

        return $query
            ->where('email', $email)
            ->orWhereHas('mails', function ($query) use ($email, $withDeleted) {
                $query->where('email', $email);
            })->first();
    }


    public function isMedical(): bool
    {
        return ! is_null($this->account->profile?->profession_id) && $this->account->profile?->account_type == 'medical' && array_key_exists($this->account->profile->profession_id, Dictionnaries::medicalProfessions());
    }

    public function isCompany(): bool
    {
        return $this->account->profile?->account_type == 'company';
    }

    public function hasAddressInFrance(): bool
    {
        if ($this->account->address->isEmpty()) {
            return false;
        }

        return (bool)$this->account->address->where('country_code', 'FR')->count();
    }

    public function billingAddress(): ?AccountAddress
    {
        if ($this->account->address->isEmpty()) {
            return null;
        }

        $billingAddress = $this->account->address
            ->filter(fn($item) => $item->billing == 1)
            ->first();

        if ( ! $billingAddress) {
            $billingAddress = $this->account->address?->first();
        }

        return $billingAddress;
    }

    public static function getBillingAddressByAccount(Account $account): ?AccountAddress
    {
        $i = new self($account);

        return $i->billingAddress();
    }

    public function defaultPhone(?string $data = null, $nullable = false): array|string|null
    {
        $defaultPhone = $this->account->phones->where('default', 1)->first();

        if ($defaultPhone === null) {
            $defaultPhone = $this->account->phones->first();
        }

        $phonedata = [
            'phone'        => $defaultPhone?->phone?->formatInternational() ?: ($nullable ? null : 'NC'),
            'country_code' => $defaultPhone?->country_code ?? 'FR',
        ];

        if ($data && array_key_exists($data, $phonedata)) {
            return $phonedata[$data];
        }

        return $phonedata;
    }

    public static function getDefaultPhoneModelByAccount(Account $account): AccountPhone|null
    {
        return $account->phones->sortByDesc('default')->first();
    }


    /**
     * @param  Account  $account
     *
     * @return PhoneNumber|null
     *
     *
     * Note: once you get the $phone from this method, you can do things like this:
     * //dd($phone->formatNational());
     * //dd($phone->getCountry());
     */
    public static function getDefaultPhoneNumberByAccount(Account $account): PhoneNumber|null
    {
        $defaultPhone = $account->phones->where('default', 1)->first();
        if ($defaultPhone) {
            // note: the country code is guessed automatically as long as the phone is in E.164.
            // However, since the database might contain other formats recorded before,
            // it is better to specify the country code from the database, just to be sure.
            return new PhoneNumber($defaultPhone->phone, $defaultPhone->country_code);
        }

        return null;
    }

    public function companyName(string $notfound = 'NC'): string
    {
        return $this->billingAddress()?->company ?? $notfound;
    }

    public static function getContactsFromPool(array $ids, array $options = []): Collection
    {
        $mainContactId = $options['main_contact_id'] ?? null;
        $sortBy        = $options['sortBy'] ?? null;

        $selectColumns = ['users.first_name', 'users.last_name', 'users.email', 'users.id', 'd.account_type', 'd.function'];
        if (null !== $mainContactId) {
            $selectColumns[] = \DB::raw("CASE WHEN users.id = $mainContactId THEN true ELSE false END as is_main_contact");
        }

        $q = Account::query()
            ->whereIn('users.id', $ids)
            ->select($selectColumns)
            ->join('account_profile as d', 'd.user_id', '=', 'users.id')
            ->with('address');

        if ($sortBy) {
            $q->orderBy($sortBy);
        }

        return $q->get();
    }


    public static function getPhotoByAccount(Account $account, bool $useDefault = true): string|null
    {
        $photo = $account->getFirstMediaUrl('photo');
        if ($photo) {
            return $photo;
        }
        if ($useDefault) {
            return url('media/logo-black.png');
        }

        return null;
    }

    /**
     * Retourne la langue pour la traduction par rapport à ce qui est défini en BO
     * @return string
     */
    public function getLocale(): string
    {
        $locale = Str::lower(Str::substr($this->account->profile?->language?->name, 0, 2)) ?? config('app.fallback_locale');

        return $locale == app()->getFallbackLocale() ? $locale : 'en';
    }

    public function names(): string
    {
        return $this->account->names();
    }

}
