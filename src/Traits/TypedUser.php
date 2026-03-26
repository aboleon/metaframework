<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use MetaFramework\Support\UserTypes;
use ReflectionClass;
use Throwable;

trait TypedUser
{
    protected static function bootTypedUser(): void
    {
        if (!UserTypes::enabled()) {
            return;
        }

        $model = static::newModelForTypedUserBoot();
        if (!self::hasUserTypeColumn($model)) {
            return;
        }

        $scopeTypes = collect(Arr::wrap(static::typedUserScopeType()))
            ->filter(static fn ($type): bool => is_string($type) && trim($type) !== '')
            ->map(static fn (string $type): string => UserTypes::resolve($type))
            ->unique()
            ->values()
            ->all();

        if ($scopeTypes !== []) {
            static::addGlobalScope('mfw-user-type', static function (Builder $query) use ($scopeTypes): void {
                $column = $query->getModel()->qualifyColumn(UserTypes::column());

                if (count($scopeTypes) === 1) {
                    $query->where($column, $scopeTypes[0]);

                    return;
                }

                $query->whereIn($column, $scopeTypes);
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

    protected static function typedUserScopeType(): array|string|null
    {
        return null;
    }

    protected static function typedUserCreateType(): array|string|null
    {
        return static::typedUserScopeType();
    }

    public function scopeOfUserType(Builder $query, array|string|null $type = null): Builder
    {
        if (!UserTypes::enabled() || !self::hasUserTypeColumn($query->getModel())) {
            return $query;
        }

        $types = collect(Arr::wrap($type))
            ->filter(static fn ($item): bool => is_string($item) && trim($item) !== '')
            ->map(static fn (string $item): string => UserTypes::resolve($item))
            ->unique()
            ->values()
            ->all();

        if ($types === []) {
            return $query;
        }

        $column = $query->getModel()->qualifyColumn(UserTypes::column());

        if (count($types) === 1) {
            return $query->where($column, $types[0]);
        }

        return $query->whereIn($column, $types);
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

    private static function newModelForTypedUserBoot(): Model
    {
        /** @var Model $model */
        $model = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

        return $model;
    }
}
