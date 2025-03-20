<?php

namespace App\Models;

use App\Enum\UserType;
use App\Interfaces\CreatorInterface;
use App\Modules\CustomFields\Traits\HasCustomFields;
use App\Traits\{Locale, Users};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;
use MetaFramework\Mediaclass\Traits\Mediaclass;
use MetaFramework\Traits\DateManipulator;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property string                      $type
 * @property AccountProfile|null         $profile
 * @property Collection                  $groups
 * @property Collection|AccountAddress[] $address
 * @property string                      $access_key
 */
class Account extends Model implements CreatorInterface, HasMedia
{
    use HasFactory;
    use HasCustomFields;
    use DateManipulator;
    use Locale;
    use InteractsWithMedia;
    use Users;
    use SoftDeletes;
  //  use Mediaclass;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable
        = [
            'name',
            'type',
            'first_name',
            'last_name',
            'email',
            'password',
        ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden
        = [
            'password',
            'remember_token',
            'two_factor_recovery_codes',
            'two_factor_secret',
        ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('userOfTypeAccount', function ($query) {
            $query->where('type', UserType::ACCOUNT->value);
        });

        static::creating(function ($model) {
            $model->type = UserType::ACCOUNT->value;
        });
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }


    public function getCompanyAttribute(): string
    {
        $billingAddress = $this->address->where('billing', 1)->first();

        if (empty($billingAddress?->company)) {
            $billingAddress = $this->address->first();
        }

        return $billingAddress?->company ?: 'NC';
    }

    public function profile(): HasOne
    {
        return $this->hasOne(AccountProfile::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function address(): HasMany
    {
        return $this->hasMany(AccountAddress::class, 'user_id');
    }

    public function phones(): HasMany
    {
        return $this->hasMany(AccountPhone::class, 'user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AccountDocument::class, 'user_id');
    }

    public function mails(): HasMany
    {
        return $this->hasMany(AccountMail::class, 'user_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(AccountCard::class, 'user_id');
    }

    /**
     * Fetch the Group Contact association for the account
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_contacts', 'user_id', 'group_id');
    }

    /**
     * Fetch the Group Contact association for the account
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'events_contacts', 'user_id', 'event_id');
    }


    public function hasCreator(): bool
    {
        return ! is_null($this->profile?->created_by);
    }

    public function getCreator(): BelongsTo
    {
        return $this->profile?->creator();
    }

    public function photoMediaSettings(): array
    {
        return [];
    }
}
