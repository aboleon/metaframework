<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Interfaces\UserCustomDataInterface;
use App\Notifications\ResetPasswordNotification;
use App\Traits\Locale;
use App\Traits\Users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use MetaFramework\Mediaclass\Traits\Mediaclass;
use MetaFramework\Traits\Responses;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use Locale;
    use Mediaclass;
    use Notifiable;
    use Responses;
    use Users;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'first_name',
        'last_name',
        'email',
        'password',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function processRoles(): static
    {
        if ($this->roles->isNotEmpty()) {
            $this->roles->each(function ($item) {
                UserRole::where(['user_id' => $this->id, 'role_id' => $item->role_id])->delete();
            });
        }
        if (request()->filled('roles')) {
            $roles = [];
            foreach (request('roles') as $role) {
                $roles[] = new UserRole(['role_id' => $role]);
            }
            $this->roles()->saveMany($roles);
        }

        return $this;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification);
    }

    public function getRoleAttribute(): ?int
    {
        return $this->userRole();
    }


    public function userSubData(?string $role = null): UserCustomDataInterface|bool
    {
        if (!$role) {
            return false;
        }

        $subclass = "\App\Models\User\\" . ucfirst(Str::camel($role));

        return class_exists($subclass) ? new $subclass : false;
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function account(): HasOne
    {
        return $this->hasOne(Account::class, 'id', 'id');
    }




    //--------------------------------------------
    // MediaclassInterface
    //--------------------------------------------
    public function getMediaOptions(): array
    {
        return [
            'maxMediaCount' => 1,
        ];
    }
}
