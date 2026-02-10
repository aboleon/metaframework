<?php

declare(strict_types=1);

namespace MetaFramework\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use MetaFramework\Models\Role;
use MetaFramework\Support\Traits\Responses;
use Throwable;

class RoleController extends Controller
{
    use Responses;

    public function index(): Renderable
    {
        abort_unless($this->canManageRoles(), 403, __('mfw-users.errors.access_denied'));

        return view('mfw::roles.index')->with([
            'roles' => Role::query()
                ->orderByDesc('is_system')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (!$this->canManageRoles()) {
            $this->responseError(__('mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        $validated = request()->validate([
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('roles', 'slug')],
            'label' => ['required', 'string', 'max:120'],
            'profile' => ['nullable', 'string', 'max:64'],
            'subgroup' => ['nullable', 'string', 'max:64'],
            'group_key' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            Role::query()->create([
                'slug' => strtolower(trim($validated['slug'])),
                'label' => trim($validated['label']),
                'profile' => trim((string) ($validated['profile'] ?? 'public')) ?: 'public',
                'subgroup' => trim((string) ($validated['subgroup'] ?? 'public')) ?: 'public',
                'group_key' => trim((string) ($validated['group_key'] ?? ($validated['subgroup'] ?? 'public'))) ?: 'public',
                'is_system' => false,
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

        if ($role->is_system) {
            $this->responseError(__('mfw-users.roles.cannot_update_system'));

            return $this->sendResponse();
        }

        $validated = request()->validate([
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('roles', 'slug')->ignore($role->id)],
            'label' => ['required', 'string', 'max:120'],
            'profile' => ['nullable', 'string', 'max:64'],
            'subgroup' => ['nullable', 'string', 'max:64'],
            'group_key' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $role->update([
                'slug' => strtolower(trim($validated['slug'])),
                'label' => trim($validated['label']),
                'profile' => trim((string) ($validated['profile'] ?? 'public')) ?: 'public',
                'subgroup' => trim((string) ($validated['subgroup'] ?? 'public')) ?: 'public',
                'group_key' => trim((string) ($validated['group_key'] ?? ($validated['subgroup'] ?? 'public'))) ?: 'public',
            ]);

            $this->responseSuccess(__('mfw-users.roles.updated'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_route = 'mfw.roles.index';

        return $this->sendResponse();
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
}
