<?php

namespace App\Http\Controllers;


use App\DataTables\UserDataTable;
use App\Enum\UserType;
use App\Http\Requests\BackendUserRequest;
use App\Notifications\SendPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use MetaFramework\Actions\Suppressor;
use App\Models\{
    Setting,
    User,
    UserProfile,
    UserRole};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\Users;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use MetaFramework\Services\Passwords\PasswordBroker;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class UserController extends Controller
{
    use SoftDeletes;
    use Users;
    use ValidationTrait;


    public function index(UserDataTable $dataTable, string $role): JsonResponse|View
    {
        if ($role == 'dev' && !auth()->user()->hasRole('dev')) {
            $role = 'forbidden';
        }

        return $dataTable->render('users.index', [
            'role' => $role,
            'archived' => request()->routeIs('panel.users.archived'),
            'admin_shared_address' => Setting::where('name', 'admin_shared_address')->first()?->value
        ]);

    }

    public function create(?string $role = null): Renderable
    {
        $account = new User;
        $parsed_role = current(array_filter($this->user_roles(), fn($item, $key) => $key == $role, ARRAY_FILTER_USE_BOTH));
        return view('users.add')->with([
            'account' => $account,
            'roles' => $this->userTypes(),
            'role' => $parsed_role,
            'route' => route('panel.users.store'),
            'label' => 'Nouveau compte ' . ($parsed_role['label'] ?? '')
        ]);
    }

    public function edit(int $id): Renderable
    {
        $user = User::withTrashed()->findOrFail($id);

        return view('users.add')->with([
            'account' => $user,
            'roles' => $user->userTypes(),
            'method' => 'put',
            'route' => route('panel.users.update', $user),
            'label' => 'Éditer un compte'
        ]);
    }

    public function store(BackendUserRequest $request): RedirectResponse
    {
        try {

            $this->ensureDataIsValid($request, 'user');
            if (request()->has('has_account_profile')) {
                $this->ensureDataIsValid($request, 'profile');
            }

            if ($this->hasErrors()) {
                return $this->sendResponse();
            }

            $password_broker = (new PasswordBroker(request()))->passwordBroker();
            $this->validated_data['user']['password'] = $password_broker->getEncryptedPassword();
            $this->validated_data['user']['type'] = UserType::SYSTEM->value;
            $this->responseNotice($password_broker->printPublicPassword());


            $user = User::create($this->validated_data['user']);

            if (request()->has('send_password_by_mail')) {
                $this->pushMessages(
                    (new SendPasswordNotification($password_broker, $user))()
                );
            }

            if (request()->filled('roles')) {
                $roles = [];
                foreach (request('roles') as $role) {
                    $roles[] = (new UserRole(['role_id' => $role]));
                }
                $user->roles()->saveMany($roles);
            }
            $user->processMedia();
            if (request()->has('profile')) {
                $user->profile()->save(new UserProfile($this->validated_data['profile']));
            }

            $this->responseSuccess(__('ui.record_created'));
            $this->redirect_to = route('panel.users.index', 'super-admin');
            $this->saveAndRedirect(route('panel.users.index', 'super-admin'));

        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    public function update(User $user): RedirectResponse
    {
        $validation = new BackendUserRequest($user);
        $this->validation_rules = $validation->rules();
        $this->validation_messages = $validation->messages();

        $this->validation();

        try {
            # Manage password change
            $password_broker = (new PasswordBroker(request()));
            if ($password_broker->requestedChange()) {
                $this->validated_data['user']['password'] = $password_broker->getEncryptedPassword();
                $this->responseNotice($password_broker->printPublicPassword());
                if (request()->has('send_password_by_mail')) {
                    $this->pushMessages(
                        (new SendPasswordNotification($password_broker, $user))()
                    );
                }
            }

            $user->update($this->validated_data['user']);
            $user->processRoles();

//            if (request()->has('profile')) {
//                $user->profile()->update($this->validated_data['profile']);
//            }
//            $user->processMedia();
            $this->processProfilePhoto($user->profile);

            if ($password_broker->requestedChange() && $user->id == auth()->id()) {
                Auth::guard('web')->logout();
                session()->flush();
                Auth::guard('web')->login($user);
            }
            $this->redirect_to = route('panel.users.index', 'super-admin');
            $this->saveAndRedirect(route('panel.users.index', 'super-admin'));
            $this->responseSuccess(__('ui.record_updated'));

        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    /**
     * @throws \Exception
     */
    public function destroy(User $user): RedirectResponse
    {
        return (new Suppressor($user))
            ->remove()
            ->whitout('object')
            ->responseSuccess(__('Le compte est archivé.'))
            ->redirectTo(route('panel.users.index', 'super-admin'))
            ->sendResponse();
    }

    public function restore(int $account_id): RedirectResponse
    {
        try {
            $account = User::withTrashed()->findOrFail($account_id);
            $account->restore();
            $this->responseSuccess("Le compte a été réactivé");

        } catch (Throwable) {

            $this->responseSuccess("Ce compte n'existe pas");
        }

        return $this->sendResponse();
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    private function processProfilePhoto (UserProfile $account): void
    {
        if (request()->has('profile_photo')) {
            $account
                ->clearMediaCollection('profile_photo')
                ->addFromMediaLibraryRequest(request("profile_photo"))
                ->toMediaCollection('profile_photo');
        }
    }
}
