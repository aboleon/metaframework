<?php

declare(strict_types=1);

namespace MetaFramework\Support;

final class UserTypes
{
    /**
     * @return list<string>
     */
    public static function values(): array
    {
        $values = config('mfw-user-types.values', ['system', 'account']);
        if (!is_array($values)) {
            return [self::default()];
        }

        $sanitized = collect($values)
            ->filter(static fn ($item): bool => is_string($item) && trim($item) !== '')
            ->map(static fn (string $item): string => trim($item))
            ->unique()
            ->values()
            ->all();

        if ($sanitized === []) {
            return [self::default()];
        }

        return $sanitized;
    }

    public static function enabled(): bool
    {
        return (bool) config('mfw-user-types.enabled', true);
    }

    public static function column(): string
    {
        $column = trim((string) config('mfw-user-types.column', 'type'));

        return $column !== '' ? $column : 'type';
    }

    public static function default(): string
    {
        $default = trim((string) config('mfw-user-types.default', 'system'));
        if ($default === '') {
            return 'system';
        }

        return $default;
    }

    /**
     * @return array<string, string|list<string>>
     */
    public static function guards(): array
    {
        $guards = config('mfw-user-types.guards', []);
        if (!is_array($guards)) {
            return [];
        }

        return collect($guards)
            ->mapWithKeys(static function ($types, $guard): array {
                if (!is_string($guard) || trim($guard) === '') {
                    return [];
                }

                if (is_string($types) && trim($types) !== '') {
                    return [trim($guard) => trim($types)];
                }

                if (!is_array($types)) {
                    return [];
                }

                $sanitized = collect($types)
                    ->filter(static fn ($type): bool => is_string($type) && trim($type) !== '')
                    ->map(static fn (string $type): string => trim($type))
                    ->unique()
                    ->values()
                    ->all();

                if ($sanitized === []) {
                    return [];
                }

                return [trim($guard) => count($sanitized) === 1 ? $sanitized[0] : $sanitized];
            })
            ->toArray();
    }

    /**
     * @return list<string>
     */
    public static function typesForGuard(?string $guard = null): array
    {
        if (!is_string($guard) || trim($guard) === '') {
            return [];
        }

        $mapped = self::guards()[trim($guard)] ?? null;

        if (is_string($mapped) && trim($mapped) !== '') {
            return [trim($mapped)];
        }

        if (!is_array($mapped)) {
            return [];
        }

        return collect($mapped)
            ->filter(static fn ($type): bool => is_string($type) && trim($type) !== '')
            ->map(static fn (string $type): string => trim($type))
            ->unique()
            ->values()
            ->all();
    }

    public static function typeForGuard(?string $guard = null): ?string
    {
        $types = self::typesForGuard($guard);

        if (count($types) !== 1) {
            return null;
        }

        return $types[0];
    }

    public static function resolve(?string $type = null, ?string $guard = null): string
    {
        $candidate = trim((string) ($type ?? ''));
        if ($candidate === '') {
            $candidate = trim((string) (self::typeForGuard($guard) ?? ''));
        }

        if ($candidate === '') {
            $candidate = self::default();
        }

        if (!self::isAllowed($candidate)) {
            return self::default();
        }

        return $candidate;
    }

    public static function isAllowed(?string $type): bool
    {
        if (!is_string($type) || trim($type) === '') {
            return false;
        }

        return in_array(trim($type), self::values(), true);
    }

    /**
     * @param  array<string,mixed>  $credentials
     * @return array<string,mixed>
     */
    public static function addToCredentials(array $credentials, ?string $guard = null, ?string $type = null): array
    {
        if (!self::enabled()) {
            return $credentials;
        }

        $resolvedType = trim((string) $type);

        if ($resolvedType !== '') {
            $credentials[self::column()] = self::resolve($resolvedType, $guard);

            return $credentials;
        }

        $guardTypes = self::typesForGuard($guard);
        if (count($guardTypes) === 1) {
            $credentials[self::column()] = self::resolve($guardTypes[0], $guard);
        }

        return $credentials;
    }
}
