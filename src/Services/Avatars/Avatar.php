<?php

declare(strict_types=1);

namespace MetaFramework\Services\Avatars;

use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use MetaFramework\Mediaclass\Path;

class Avatar
{
    private string $path;

    private FilesystemAdapter $disk;

    private bool $error = false;

    private string $initials;

    public function __construct(private User $user)
    {
        $this->path = 'users/avatars/';
        $this->disk = Storage::disk('media');
        $this->initials = substr(config('app.name'), 0, 2);
        $this->initials();
    }

    public static function avatar(User $user)
    {
        return (new self($user))->fetchImage();
    }

    private function fetchImage(): ?string
    {
        if ($this->disk->exists($this->path . $this->file())) {
            return $this->disk->url($this->path . $this->file());
        }

        $this->fetchFromApi();

        if (!$this->error) {
            return $this->disk->url($this->path . $this->file());
        }

        return null;

    }

    private function file(): string
    {
        return $this->initials . '.svg';
    }

    private function initials(): void
    {
        if ($this->user->first_name && $this->user->last_name) {
            $this->initials = substr($this->user->first_name, 0, 1) . substr($this->user->last_name, 0, 1);

            return;
        }

        if ($this->user->name) {
            $name = explode(' ', $this->user->name);
            $this->initials = (count($name) > 1)
                ? substr($name[0], 0, 1) . substr($name[1], 0, 1)
                : substr($this->user->name, 0, 2);

            return;
        }

        if ($this->user->first_name) {
            $this->initials = substr($this->user->first_name, 0, 2);

            return;
        }

        if ($this->user->last_name) {
            $this->initials = substr($this->user->last_name, 0, 2);
        }
    }

    private function fetchFromApi(): void
    {

        $random_color = array_rand($this->colors());

        $url = 'https://ui-avatars.com/api/?format=svg&background=' . $this->colors()[$random_color] . '&color=fff&name=' . $this->initials . '&size=128';

        Path::checkMakeDir($this->disk->path($this->path));
        file_put_contents($this->disk->path($this->path) . $this->file(), file_get_contents($url));
    }

    private function colors(): array
    {
        return [
            'D32F2F', // Red
            '1976D2', // Blue
            '388E3C', // Green
            'FBC02D', // Yellow
            '8E24AA', // Purple
            'C2185B', // Pink
            '7B1FA2', // Indigo
            '0288D1', // Light Blue
            'CDDC39', // Lime
            'FFC107', // Amber
            '0097A7', // Cyan
            '689F38', // Light Green
            '8D6E63', // Brown
            '616161', // Grey
            '455A64', // Blue Grey
            'F57C00', // Orange
            'E64A19', // Deep Orange
            '512DA8', // Deep Purple
            '0288D1', // Light Blue
            'C0CA33', // Lime
            'F4511E', // Deep Orange
            '1B5E20', // Dark Green
            'F44336', // Bright Red
            '03A9F4', // Light Blue
            '9C27B0'  // Bright Purple
        ];
    }
}
