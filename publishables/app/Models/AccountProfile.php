<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFramework\Traits\Responses;

/**
 * @property string $account_type
 * @property int  $profession_id
 */
class AccountProfile extends Model
{
    use HasFactory;
    use Responses;

    public $timestamps = false;

    protected $table = 'account_profile';

    protected $casts = [
        'birth' => 'date',
        'blacklisted' => 'date'
    ];

    protected $fillable = [
        'user_id',
        'account_type',
        'base_id',
        'billing_address',
        'birth',
        'blacklisted',
        'blacklist_comment',
        'civ',
        'company_name',
        'cotisation_year',
        'created_by',
        'domain_id',
        'establishment_id',
        'function',
        'language_id',
        'notes',
        'passport_first_name',
        'passport_last_name',
        'profession_id',
        'rpps',
        'savant_society_id',
        'title_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')
            ->withoutGlobalScope('active')
            ->withTrashed();
    }

    public function address(): HasMany
    {
        return $this->hasMany(AccountAddress::class, 'user_id');
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(DictionnaryEntry::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(DictionnaryEntry::class);
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(DictionnaryEntry::class);
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(DictionnaryEntry::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(DictionnaryEntry::class);
    }

    public function savantSociety(): BelongsTo
    {
        return $this->belongsTo(DictionnaryEntry::class);
    }
}
