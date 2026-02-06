<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait OnlineStatus
{
    public function isActive(string $tag = ''): string
    {
        $allowed_tags = ['div', 'td', 'th'];
        $tag = in_array($tag, $allowed_tags) ? $tag : 'span';

        return '<' . $tag . " class='mfw-status " . $this->statusTag() . "'>" .
            ($this->published instanceof Carbon
            ? ($this->published?->format('d/m/Y H:i') ?: trans('mfw.no'))
            : ($this->published ? trans('mfw.yes') : trans('mfw.no'))) . '</' . $tag . '>';
    }

    public function printStatusAsBadge(): string
    {
        return '<div class="mfw-published_status" data-ajax-url="' . route('mfw.ajax') . '" data-status="' . $this->statusTag() . '" data-id="' . $this->id . '" data-class="' . get_class($this) . '" data-label-pushonline="' . __('mfw.published.publish') . '" data-label-pushoffline="' . __('mfw.published.unpublish') . '" data-label-isonline="' . __('mfw.published.online') . '" data-label-isoffline="' . __('mfw.published.offline') . '"><button type="button" class="btn btn-sm btn-' . $this->statusClass() . '">' . $this->statusLabel() . '</button></div>';
    }

    public function statusClass(): string
    {
        return empty($this->published) ? 'danger' : 'success';
    }

    public function statusLabel(): string
    {
        return trans('mfw.published.' . $this->statusTag());
    }

    public function statusTag(): string
    {
        return empty($this->published) ? 'offline' : 'online';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', 1);
    }

    public function isPublished(): bool
    {
        return (bool) $this->published;
    }
}
