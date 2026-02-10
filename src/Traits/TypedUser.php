<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Support\UserTypes;
use Throwable;

trait TypedUser
{
    protected static function bootTypedUser(): void
    {
        if (!UserTypes::enabled()) {
            return;
        }

        $model = new static;
        if (!self::hasUserTypeColumn($model)) {
            return;
        }

        $scopeType = trim((string) (static::typedUserScopeType() ?? ''));
        if ($scopeType !== '') {
            static::addGlobalScope('mfw-user-type', static function (Builder $query) use ($scopeType): void {
                $query->where(
                    $query->getModel()->qualifyColumn(UserTypes::column()),
                    UserTypes::resolve($scopeType)
                );
            });
        }

        static::creating(static function (Model $model): void {
            if (!self::hasUserTypeColumn($model)) {
                return;
            }

            $column = UserTypes::column();
            $current = trim((string) ($model->{$column} ?? ''));
            if ($current !== '') {
                return;
            }

            $model->{$column} = UserTypes::resolve(static::typedUserCreateType());
        });
    }

    protected static function typedUserScopeType(): ?string
    {
        return null;
    }

    protected static function typedUserCreateType(): ?string
    {
        return static::typedUserScopeType();
    }

    public function scopeOfUserType(Builder $query, ?string $type = null): Builder
    {
        if (!UserTypes::enabled() || !self::hasUserTypeColumn($query->getModel())) {
            return $query;
        }

        return $query->where(
            $query->getModel()->qualifyColumn(UserTypes::column()),
            UserTypes::resolve($type)
        );
    }

    public function assignUserType(?string $type = null, ?string $guard = null): static
    {
        if (!UserTypes::enabled() || !self::hasUserTypeColumn($this)) {
            return $this;
        }

        $this->{UserTypes::column()} = UserTypes::resolve($type, $guard);

        return $this;
    }

    private static function hasUserTypeColumn(Model $model): bool
    {
        try {
            return Schema::hasTable($model->getTable()) && Schema::hasColumn($model->getTable(), UserTypes::column());
        } catch (Throwable) {
            return false;
        }
    }
}
