<?php

declare(strict_types=1);

namespace MetaFramework\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use MetaFramework\Models\UserRole;
use MetaFramework\Services\Passwords\PasswordBroker;
use MetaFramework\Support\Traits\Responses;
use MetaFramework\Support\UserRoles;
use Throwable;

class UserController extends Controller
{
    use Responses;

    public function index(string $role): Renderable
    {
        abort_unless($this->canManageUsers(), 403, __('mfw-users.errors.access_denied'));

        if ($role === UserRoles::CORE_DEV_KEY && !$this->canAccessDevUsers()) {
            abort(403, __('mfw-users.errors.access_denied'));
        }

        $roles = $this->availableRoles();
        $archived = request()->routeIs('mfw.users.archived');
        $query = $this->userQuery();

        if ($archived) {
            if ($this->supportsSoftDeletes()) {
                $query->onlyTrashed();
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $this->applyRoleFilter($query, $role);
        $this->applyOrder($query);

        $roleLabel = $roles[$role]['label'] ?? $role;

        return view('mfw::users.index')->with([
            'users' => $query->paginate(25)->withQueryString(),
            'role' => $role,
            'roleLabel' => $roleLabel,
            'archived' => $archived,
            'columns' => $this->editableColumns(),
            'supportsSoftDeletes' => $this->supportsSoftDeletes(),
        ]);
    }

    public function create(?string $role = null): Renderable
    {
        abort_unless($this->canManageUsers(), 403, __('mfw-users.errors.access_denied'));

        $user = $this->newUserModel();
        $roles = $this->availableRoles();
        $roleSlug = $role && array_key_exists($role, $roles) ? $role : null;
        if ($roleSlug === UserRoles::CORE_DEV_KEY && !$this->canAccessDevUsers()) {
            abort(403, __('mfw-users.errors.access_denied'));
        }
        $forcedRole = $roleSlug ? $roles[$roleSlug] : null;

        return view('mfw::users.form')->with([
            'account' => $user,
            'roles' => $roles,
            'selectedRoleIds' => [],
            'forcedRole' => $forcedRole,
            'route' => route('mfw.users.store'),
            'label' => __('mfw-users.users.add_title', [
                'role' => $roleSlug ? ($forcedRole['label'] ?? $roleSlug) : __('mfw-users.users.any_role'),
            ]),
            'roleSlug' => $roleSlug ?: $this->defaultRoleSlug(),
            'columns' => $this->editableColumns(),
            'supportsSoftDeletes' => $this->supportsSoftDeletes(),
        ]);
    }

    public function edit(int $userId): Renderable
    {
        abort_unless($this->canManageUsers(), 403, __('mfw-users.errors.access_denied'));

        $user = $this->findUserOrFail($userId, true);
        $this->assertCanManageTargetUser($user);
        $roles = $this->availableRoles();
        $roleSlug = $this->resolveRoleSlug($user);

        return view('mfw::users.form')->with([
            'account' => $user,
            'roles' => $roles,
            'selectedRoleIds' => $this->selectedRoleIds($user),
            'forcedRole' => null,
            'method' => 'put',
            'route' => route('mfw.users.update', $user->getKey()),
            'label' => __('mfw-users.users.edit_title', [
                'role' => $roles[$roleSlug]['label'] ?? $roleSlug,
            ]),
            'roleSlug' => $roleSlug,
            'columns' => $this->editableColumns(),
            'supportsSoftDeletes' => $this->supportsSoftDeletes(),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (!$this->canManageUsers()) {
            $this->responseError(__('mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        request()->validate($this->validationRules());

        try {
            $passwordBroker = new PasswordBroker(request());
            $payload = $this->validatedUserData();
            $payload['password'] = $passwordBroker->getEncryptedPassword();

            $userClass = $this->userModelClass();
            /** @var Model $user */
            $user = $userClass::query()->create($payload);

            $this->syncUserRoles($user, $this->requestedRoleIds());

            $this->responseSuccess(__('mfw-users.users.created'));
            $this->responseNotice($passwordBroker->printPublicPassword());
            $this->redirect_to = route('mfw.users.index', $this->defaultRoleSlug());
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->sendResponse();
    }

    public function update(int $userId): RedirectResponse
    {
        if (!$this->canManageUsers()) {
            $this->responseError(__('mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        $user = $this->findUserOrFail($userId, true);
        $this->assertCanManageTargetUser($user);
        request()->validate($this->validationRules($user));

        try {
            $payload = $this->validatedUserData();
            if (request()->boolean('password_change')) {
                $passwordBroker = new PasswordBroker(request());
                $payload['password'] = $passwordBroker->getEncryptedPassword();
                $this->responseNotice($passwordBroker->printPublicPassword());
            }

            if ($payload !== []) {
                $user->update($payload);
            }

            $this->syncUserRoles($user, $this->requestedRoleIds());

            $this->responseSuccess(__('mfw-users.users.updated'));
            $this->redirect_to = route('mfw.users.index', $this->resolveRoleSlug($user));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->sendResponse();
    }

    public function destroy(int $userId): RedirectResponse
    {
        if (!$this->canManageUsers()) {
            $this->responseError(__('mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        $user = $this->findUserOrFail($userId, true);
        $this->assertCanManageTargetUser($user);

        try {
            if ($this->supportsSoftDeletes()) {
                $user->delete();
                $this->responseSuccess(__('mfw-users.users.archived_success'));
            } else {
                $user->delete();
                $this->responseSuccess(__('mfw-users.users.deleted_success'));
            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_to = route('mfw.users.index', $this->resolveRoleSlug($user));

        return $this->sendResponse();
    }

    public function restore(int $userId): RedirectResponse
    {
        if (!$this->canManageUsers()) {
            $this->responseError(__('mfw-users.errors.access_denied'));

            return $this->sendResponse();
        }

        if (!$this->supportsSoftDeletes()) {
            $this->responseError(__('mfw-users.users.restore_not_supported'));

            return $this->sendResponse();
        }

        try {
            $user = $this->findUserOrFail($userId, true);
            $this->assertCanManageTargetUser($user);
            if (method_exists($user, 'restore')) {
                $user->restore();
                $this->responseSuccess(__('mfw-users.users.restored_success'));
            } else {
                $this->responseError(__('mfw-users.users.restore_not_supported'));
            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        $this->redirect_to = route('mfw.users.index', $this->defaultRoleSlug());

        return $this->sendResponse();
    }

    private function canManageUsers(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && method_exists($user, 'hasRole')
            && $user->hasRole(UserRoles::CORE_DEV_KEY . '|' . UserRoles::CORE_SUPER_ADMIN_KEY);
    }

    private function canAccessDevUsers(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && method_exists($user, 'hasRole')
            && $user->hasRole(UserRoles::CORE_DEV_KEY);
    }

    private function applyRoleFilter(Builder $query, string $role): void
    {
        if (!$this->anyRoleAssignmentExists()) {
            return;
        }

        $roles = $this->availableRoles();
        $roleId = $roles[$role]['id'] ?? null;
        if (!$roleId) {
            $query->whereRaw('1 = 0');

            return;
        }

        $model = $query->getModel();
        if (method_exists($model, 'scopeWithRole')) {
            $query->withRole($role);

            return;
        }

        $query->whereExists(function ($subQuery) use ($model, $roleId): void {
            $subQuery
                ->select(DB::raw(1))
                ->from('users_roles')
                ->whereColumn('users_roles.user_id', $model->getTable() . '.' . $model->getKeyName())
                ->where('users_roles.role_id', $roleId);
        });
    }

    private function applyOrder(Builder $query): void
    {
        $table = $this->usersTable();
        if (Schema::hasColumn($table, 'last_name')) {
            $query->orderBy('last_name');
        }

        if (Schema::hasColumn($table, 'first_name')) {
            $query->orderBy('first_name');

            return;
        }

        if (Schema::hasColumn($table, 'name')) {
            $query->orderBy('name');

            return;
        }

        $query->orderBy($query->getModel()->getKeyName());
    }

    private function validationRules(?Model $user = null): array
    {
        $columns = $this->editableColumns();
        $table = $this->usersTable();
        $rules = [];

        if (Schema::hasTable('roles')) {
            $rules['roles'] = ['nullable', 'array'];
            $rules['roles.*'] = ['integer', 'exists:roles,id'];
        }

        if ($columns['first_name']) {
            $rules['user.first_name'] = ['required', 'string', 'max:120'];
        }
        if ($columns['last_name']) {
            $rules['user.last_name'] = ['required', 'string', 'max:120'];
        }
        if ($columns['name'] && (!$columns['first_name'] || !$columns['last_name'])) {
            $rules['user.name'] = ['required', 'string', 'max:160'];
        }
        if ($columns['email']) {
            $emailRule = Rule::unique($table, 'email');
            if ($user !== null) {
                $emailRule = $emailRule->ignore($user->getKey(), $user->getKeyName());
            }
            $rules['user.email'] = ['required', 'email:rfc', 'max:255', $emailRule];
        }

        $mustValidatePassword = $user === null || request()->boolean('password_change');
        if ($mustValidatePassword && !request()->boolean('random_password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    private function validatedUserData(): array
    {
        $data = request()->input('user', []);

        if (!is_array($data)) {
            return [];
        }

        $columns = $this->editableColumns();
        $payload = [];
        foreach (['name', 'first_name', 'last_name', 'email'] as $field) {
            if ($columns[$field] && array_key_exists($field, $data)) {
                $payload[$field] = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
            }
        }

        return $payload;
    }

    /**
     * @return array<int>
     */
    private function requestedRoleIds(): array
    {
        $roles = request()->input('roles', []);
        if (!is_array($roles)) {
            return [];
        }

        $availableRoleIds = collect($this->availableRoles())
            ->reject(fn (array $role, string $slug): bool => $slug === UserRoles::CORE_DEV_KEY && !$this->canAccessDevUsers())
            ->pluck('id')
            ->map(static fn ($item): int => (int) $item)
            ->all();

        return collect($roles)
            ->filter(static fn ($item): bool => is_numeric($item))
            ->map(static fn ($item): int => (int) $item)
            ->filter(static fn (int $item): bool => in_array($item, $availableRoleIds, true))
            ->unique()
            ->values()
            ->toArray();
    }

    private function syncUserRoles(Model $user, array $roleIds): void
    {
        if (!Schema::hasTable('users_roles')) {
            return;
        }

        if (method_exists($user, 'roles')) {
            $user->roles()->delete();
            if ($roleIds !== []) {
                $user->roles()->saveMany(array_map(static fn (int $roleId): UserRole => new UserRole(['role_id' => $roleId]), $roleIds));
            }

            return;
        }

        DB::table('users_roles')->where('user_id', $user->getKey())->delete();
        if ($roleIds === []) {
            return;
        }

        DB::table('users_roles')->insert(array_map(static fn (int $roleId): array => [
            'user_id' => $user->getKey(),
            'role_id' => $roleId,
        ], $roleIds));
    }

    /**
     * @return array<int>
     */
    private function selectedRoleIds(Model $user): array
    {
        if (!Schema::hasTable('users_roles')) {
            return [];
        }

        if (method_exists($user, 'roles')) {
            return $user->roles()->pluck('role_id')->map(static fn ($item): int => (int) $item)->unique()->values()->toArray();
        }

        return DB::table('users_roles')
            ->where('user_id', $user->getKey())
            ->pluck('role_id')
            ->map(static fn ($item): int => (int) $item)
            ->unique()
            ->values()
            ->toArray();
    }

    private function resolveRoleSlug(Model $user): string
    {
        $selectedRoleIds = $this->selectedRoleIds($user);
        $roles = $this->availableRoles();
        foreach ($roles as $slug => $meta) {
            if (in_array((int) $meta['id'], $selectedRoleIds, true)) {
                return $slug;
            }
        }

        return $this->defaultRoleSlug();
    }

    private function defaultRoleSlug(): string
    {
        $roles = $this->availableRoles();
        if (array_key_exists(UserRoles::CORE_SUPER_ADMIN_KEY, $roles)) {
            return UserRoles::CORE_SUPER_ADMIN_KEY;
        }

        $firstRole = array_key_first($roles);

        return $firstRole ? (string) $firstRole : UserRoles::CORE_SUPER_ADMIN_KEY;
    }

    /**
     * @return array<string, array{id:int,label:string,profile:string,subgroup:string,group_key:string,is_system:bool}>
     */
    private function availableRoles(): array
    {
        return collect(UserRoles::all())->except('default')->toArray();
    }

    /**
     * @return array{name:bool,first_name:bool,last_name:bool,email:bool}
     */
    private function editableColumns(): array
    {
        $table = $this->usersTable();

        return [
            'name' => Schema::hasColumn($table, 'name'),
            'first_name' => Schema::hasColumn($table, 'first_name'),
            'last_name' => Schema::hasColumn($table, 'last_name'),
            'email' => Schema::hasColumn($table, 'email'),
        ];
    }

    private function usersTable(): string
    {
        return $this->newUserModel()->getTable();
    }

    private function supportsSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->userModelClass()), true);
    }

    private function anyRoleAssignmentExists(): bool
    {
        try {
            if (!Schema::hasTable('users_roles')) {
                return false;
            }

            return DB::table('users_roles')->exists();
        } catch (Throwable) {
            return false;
        }
    }

    private function findUserOrFail(int $userId, bool $withTrashed = false): Model
    {
        $query = $this->userQuery();
        if ($withTrashed && $this->supportsSoftDeletes()) {
            $query->withTrashed();
        }

        return $query->findOrFail($userId);
    }

    private function assertCanManageTargetUser(Model $user): void
    {
        if (
            !$this->canAccessDevUsers()
            && method_exists($user, 'hasRole')
            && $user->hasRole(UserRoles::CORE_DEV_KEY)
        ) {
            abort(403, __('mfw-users.errors.access_denied'));
        }
    }

    private function userQuery(): Builder
    {
        $userClass = $this->userModelClass();

        return $userClass::query();
    }

    private function newUserModel(): Model
    {
        $userClass = $this->userModelClass();
        $model = new $userClass;
        if (!$model instanceof Model) {
            abort(500, __('mfw-users.users.invalid_user_model'));
        }

        return $model;
    }

    private function userModelClass(): string
    {
        $userClass = config('auth.providers.users.model');
        if (!is_string($userClass) || $userClass === '' || !class_exists($userClass)) {
            abort(500, __('mfw-users.users.invalid_user_model'));
        }

        return $userClass;
    }
}
