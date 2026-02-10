<?php

declare(strict_types=1);

namespace MetaFramework\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MetaFramework\Models\Role;
use MetaFramework\Models\RoleGroup;
use MetaFramework\Support\Traits\Responses;
use MetaFramework\Support\UserRoles;
use Throwable;

class RoleController extends Controller
{
    use Responses;

    public function index(): Renderable
    {
        abort_unless($this->canManageRoles(), 403, __('mfw-users.errors.access_denied'));

        return view('mfw::roles.index')->with([
            'roles' => Role::query()
                ->with('group')
                ->orderByDesc('is_system')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create(): Renderable
    {
        abort_unless($this->canManageRoles(), 403, __('mfw-users.errors.access_denied'));
        $groups = $this->groupSelectValues();

        return view('mfw::roles.form')->with([
            'role' => new Role,
            'groups' => $groups,
            'defaultGroupId' => (int) (array_key_first($groups) ?? 0),
            'route' => route('mfw.roles.store'),
            'method' => null,
            'title' => __('mfw-users.roles.create_title'),
        ]);
    }

    public function edit(Role $role): Renderable
    {
        abort_unless($this->canManageRoles(), 403, __('mfw-users.errors.access_denied'));
        $groups = $this->groupSelectValues();

        return view('mfw::roles.form')->with([
            'role' => $role,
            'groups' => $groups,
            'defaultGroupId' => (int) ($role->group_id ?? array_key_first($groups) ?? 0),
            'route' => route('mfw.roles.update', $role),
            'method' => 'PUT',
            'title' => __('mfw-users.roles.edit_title'),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (!$this->canManageRoles()) {
            $this->responseError(__('mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        $validated = $this->validateRolePayload();

        try {
            $key = strtolower(trim($validated['key']));
            Role::query()->create([
                'key' => $key,
                'label' => $this->normalizeLabelPayload($validated['label']),
                'group_id' => (int) $validated['group_id'],
                'is_system' => $this->resolveIsSystemValue($key, request()->boolean('is_system')),
            ]);

            $this->responseSuccess(__('mfw-users.roles.created'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_route = 'mfw.roles.index';

        return $this->sendResponse();
    }

    public function update(Role $role): RedirectResponse
    {
        if (!$this->canManageRoles()) {
            $this->responseError(__('mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        $validated = $this->validateRolePayload($role);
        $key = strtolower(trim($validated['key']));

        if ($role->is_system && $key !== $role->key) {
            $this->responseError(__('mfw-users.roles.cannot_update_system_key'));

            return $this->sendResponse();
        }

        try {
            $role->update([
                'key' => $key,
                'label' => $this->normalizeLabelPayload($validated['label']),
                'group_id' => (int) $validated['group_id'],
                'is_system' => $this->resolveIsSystemValue($key, request()->boolean('is_system')),
            ]);

            $this->responseSuccess(__('mfw-users.roles.updated'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_route = 'mfw.roles.index';

        return $this->sendResponse();
    }

    private function validateRolePayload(?Role $role = null): array
    {
        $validator = Validator::make(request()->all(), [
            'key' => [
                'required',
                'string',
                'max:64',
                'alpha_dash',
                Rule::unique('roles', 'key')->ignore($role?->id),
            ],
            'label' => ['required', 'array'],
            'label.*' => ['nullable', 'string'],
            'group_id' => ['required', 'integer', Rule::exists('role_groups', 'id')],
            'is_system' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($validator): void {
            if ($this->normalizeLabelPayload(request()->input('label')) === null) {
                $validator->errors()->add('label', __('validation.required', ['attribute' => __('mfw-users.roles.label')]));
            }
        });

        return $validator->validate();
    }

    /**
     * @return array<string,string>|string|null
     */
    private function normalizeLabelPayload(mixed $label): array|string|null
    {
        if (is_string($label)) {
            $parsed = trim($label);

            return $parsed !== '' ? $parsed : null;
        }

        if (!is_array($label)) {
            return null;
        }

        $cleaned = collect($label)
            ->filter(static fn ($value, $key): bool => is_string($key))
            ->map(static fn ($value): string => is_string($value) ? trim($value) : '')
            ->filter(static fn ($value): bool => $value !== '')
            ->toArray();

        if ($cleaned === []) {
            return null;
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

    public function destroy(Role $role): RedirectResponse
    {
        if (!$this->canManageRoles()) {
            $this->responseError(__('mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        if ($role->is_system) {
            $this->responseError(__('mfw-users.roles.cannot_delete_system'));

            return $this->sendResponse();
        }

        if ($role->userRoles()->exists()) {
            $this->responseError(__('mfw-users.roles.assigned_cannot_delete'));

            return $this->sendResponse();
        }

        try {
            $role->delete();
            $this->responseSuccess(__('mfw-users.roles.deleted'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_route = 'mfw.roles.index';

        return $this->sendResponse();
    }

    private function canManageRoles(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && method_exists($user, 'hasRole')
            && $user->hasRole('dev|super-admin');
    }

    /**
     * @return array<int,string>
     */
    private function groupSelectValues(): array
    {
        return RoleGroup::query()
            ->orderBy('key')
            ->get()
            ->pluck('label', 'id')
            ->all();
    }

    private function resolveIsSystemValue(string $key, bool $requested): bool
    {
        if (in_array($key, [UserRoles::CORE_DEV_KEY, UserRoles::CORE_SUPER_ADMIN_KEY], true)) {
            return true;
        }

        return $requested;
    }
}
