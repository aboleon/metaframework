<?php

declare(strict_types=1);

namespace MetaFramework\Navigation;

use Closure;

final class NavigationItem
{
    /**
     * @param  array<int, self>  $children
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $icon,
        private readonly Closure $urlResolver,
        public readonly string $target = '_self',
        private readonly ?Closure $visibilityResolver = null,
        private readonly array $children = [],
        private readonly string $type = 'link',
    ) {}

    public static function link(
        string $key,
        string $label,
        string $icon,
        Closure $urlResolver,
        ?Closure $visibilityResolver = null,
        string $target = '_self',
    ): self {
        return new self($key, $label, $icon, $urlResolver, $target, $visibilityResolver);
    }

    /**
     * @param  array<int, self>  $children
     */
    public static function section(
        string $key,
        string $label,
        string $icon,
        array $children,
        ?Closure $visibilityResolver = null,
    ): self {
        return new self($key, $label, $icon, static fn (): string => '#', '_self', $visibilityResolver, $children, 'section');
    }

    public static function notice(
        string $key,
        string $label,
        string $icon,
        ?Closure $visibilityResolver = null,
    ): self {
        return new self($key, $label, $icon, static fn (): string => '#', '_self', $visibilityResolver, [], 'notice');
    }

    public function appendChildren(self ...$children): self
    {
        if (! $this->isSection()) {
            throw new \LogicException("Navigation item [{$this->key}] is not a section.");
        }

        return new self(
            $this->key,
            $this->label,
            $this->icon,
            $this->urlResolver,
            $this->target,
            $this->visibilityResolver,
            [...$this->children, ...$children],
            $this->type,
        );
    }

    public function url(): string
    {
        return (string) ($this->urlResolver)();
    }

    public function isVisible(): bool
    {
        return $this->visibilityResolver === null || (bool) ($this->visibilityResolver)();
    }

    public function isSection(): bool
    {
        return $this->children !== [];
    }

    public function isNotice(): bool
    {
        return $this->type === 'notice';
    }

    /**
     * @return array<int, self>
     */
    public function visibleChildren(): array
    {
        return array_values(array_filter(
            $this->children,
            static fn (self $child): bool => $child->isVisible(),
        ));
    }

    public function hasVisibleChildren(): bool
    {
        return $this->visibleChildren() !== [];
    }
}
