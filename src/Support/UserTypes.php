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
        return (bool) config('mfw-user-types.enabled', false);
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
     * @return array<string,string>
     */
    public static function guards(): array
    {
        $guards = config('mfw-user-types.guards', []);
        if (!is_array($guards)) {
            return [];
        }

        return collect($guards)
            ->filter(static fn ($type, $guard): bool => is_string($guard) && trim($guard) !== '' && is_string($type) && trim($type) !== '')
            ->mapWithKeys(static fn (string $type, string $guard): array => [trim($guard) => trim($type)])
            ->toArray();
    }

    public static function typeForGuard(?string $guard = null): ?string
    {
        if (!is_string($guard) || trim($guard) === '') {
            return null;
        }

        $mapped = self::guards()[trim($guard)] ?? null;

        return is_string($mapped) && trim($mapped) !== '' ? trim($mapped) : null;
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

        $credentials[self::column()] = self::resolve($type, $guard);

        return $credentials;
    }
}
