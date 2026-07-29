<?php

declare(strict_types=1);

namespace MetaFramework\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MetaFramework\Models\RoleGroup;
use MetaFramework\Support\Traits\Responses;
use Throwable;

class RoleGroupController extends Controller
{
    use Responses;

    public function index(): Renderable
    {
        abort_unless($this->canManageRoleGroups(), 403, __('mfw::mfw-users.errors.access_denied'));

        return view('mfw::role-groups.index')->with([
            'groups' => RoleGroup::query()
                ->orderBy('key')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create(): Renderable
    {
        abort_unless($this->canManageRoleGroups(), 403, __('mfw::mfw-users.errors.access_denied'));

        return view('mfw::role-groups.form')->with([
            'group' => new RoleGroup,
            'route' => route('mfw.role-groups.store'),
            'method' => null,
            'title' => __('mfw::mfw-users.role_groups.create_title'),
        ]);
    }

    public function edit(RoleGroup $roleGroup): Renderable
    {
        abort_unless($this->canManageRoleGroups(), 403, __('mfw::mfw-users.errors.access_denied'));

        return view('mfw::role-groups.form')->with([
            'group' => $roleGroup,
            'route' => route('mfw.role-groups.update', $roleGroup),
            'method' => 'PUT',
            'title' => __('mfw::mfw-users.role_groups.edit_title'),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (! $this->canManageRoleGroups()) {
            $this->responseError(__('mfw::mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        $validated = $this->validateGroupPayload();
        $key = strtolower(trim($validated['key']));

        try {
            RoleGroup::query()->create([
                'key' => $key,
                'label' => $this->normalizeTranslatablePayload($validated['label'], true),
                'description' => $this->normalizeTranslatablePayload($validated['description'] ?? null, false),
            ]);

            $this->responseSuccess(__('mfw::mfw-users.role_groups.created'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_route = 'mfw.role-groups.index';

        return $this->sendResponse();
    }

    public function update(RoleGroup $roleGroup): RedirectResponse
    {
        if (! $this->canManageRoleGroups()) {
            $this->responseError(__('mfw::mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        $validated = $this->validateGroupPayload($roleGroup);
        $key = strtolower(trim($validated['key']));

        try {
            $roleGroup->update([
                'key' => $key,
                'label' => $this->normalizeTranslatablePayload($validated['label'], true),
                'description' => $this->normalizeTranslatablePayload($validated['description'] ?? null, false),
            ]);

            $this->responseSuccess(__('mfw::mfw-users.role_groups.updated'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_route = 'mfw.role-groups.index';

        return $this->sendResponse();
    }

    public function destroy(RoleGroup $roleGroup): RedirectResponse
    {
        if (! $this->canManageRoleGroups()) {
            $this->responseError(__('mfw::mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        if ($roleGroup->roles()->exists()) {
            $this->responseError(__('mfw::mfw-users.role_groups.assigned_cannot_delete'));

            return $this->sendResponse();
        }

        try {
            $roleGroup->delete();
            $this->responseSuccess(__('mfw::mfw-users.role_groups.deleted'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_route = 'mfw.role-groups.index';

        return $this->sendResponse();
    }

    private function validateGroupPayload(?RoleGroup $roleGroup = null): array
    {
        $validator = Validator::make(request()->all(), [
            'key' => [
                'required',
                'string',
                'max:64',
                'alpha_dash',
                Rule::unique('role_groups', 'key')->ignore($roleGroup?->id),
            ],
            'label' => ['required', 'array'],
            'label.*' => ['nullable', 'string'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator): void {
            if ($this->normalizeTranslatablePayload(request()->input('label'), true) === null) {
                $validator->errors()->add('label', __('validation.required', ['attribute' => __('mfw::mfw-users.role_groups.label')]));
            }
        });

        return $validator->validate();
    }

    /**
     * @return array<string,string>|string|null
     */
    private function normalizeTranslatablePayload(mixed $payload, bool $required): array|string|null
    {
        if (is_string($payload)) {
            $parsed = trim($payload);

            return $parsed !== '' ? $parsed : null;
        }

        if (! is_array($payload)) {
            return $required ? null : null;
        }

        $cleaned = collect($payload)
            ->filter(static fn ($value, $key): bool => is_string($key))
            ->map(static fn ($value): string => is_string($value) ? trim($value) : '')
            ->filter(static fn ($value): bool => $value !== '')
            ->toArray();

        if ($cleaned === []) {
            return $required ? null : null;
        }

        if ((bool) config('mfw.translatable.multilang', false)) {
            return $cleaned;
        }

        $fallbackLocale = (string) config('app.fallback_locale', app()->getLocale());
        $currentLocale = (string) app()->getLocale();

        return $cleaned[$currentLocale]
            ?? $cleaned[$fallbackLocale]
            ?? reset($cleaned)
            ?: null;
    }

    private function canManageRoleGroups(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && method_exists($user, 'hasRole')
            && $user->hasRole('dev|super-admin');
    }
}
