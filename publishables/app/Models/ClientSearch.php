<?php

namespace App\Models;

use App\Traits\Locale;
use Illuminate\Database\Eloquent\{Builder, Factories\HasFactory, Model};
use Illuminate\Pagination\LengthAwarePaginator;

class ClientSearch extends Model
{
    use HasFactory;
    use Locale;

    private Builder $query;

    /** Nombre de pages pour la paginaton
     * @var int
     */
    protected $perPage = 15;

    /** Faire fonctionner avec des données de test
     * @var bool
     */
    private bool $test_mode = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->query = Account::query();
    }

    public function search(): LengthAwarePaginator
    {
        return $this
            ->selectables()
            ->filters()
            ->result();
    }


    private function selectables(): static
    {
        $this->query->select('users.id', 'first_name', 'last_name', 'created_at','user_profile.phone','email_verified_at')
            ->leftJoin('user_profile', function ($join) {
                $join->on('users.id', '=', 'user_profile.user_id');
            })
        ->with(['profile']);
        return $this;
    }

    private function filters(): static
    {
        $this->query->where(function ($q) {
            if (request()->filled('names')) {
                $q->where('first_name', 'like', '%' . request('names') . '%')->orWhere('last_name', 'like', '%' . request('names') . '%');
            }
            if (request()->filled('email')) {
                $q->where('email', 'like', '%' . request('email') . '%');
            }
            if (request()->filled('phone')) {
                $q->where('user_profile.phone', 'like', '%' . request('phone') . '%');
            }
        });

        return $this;
    }

    public function result(): LengthAwarePaginator
    {
        return $this->query->paginate($this->perPage)->appends(request()->input());
    }


}
