<?php

namespace App\Http\Controllers\Account;


use App\Http\Controllers\Controller;
use App\Traits\Users;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{
    Auth,
    Session};
use MetaFramework\Services\Validation\ValidationTrait;

class LoginController extends Controller
{
    use ValidationTrait;
    use Users;

    public function auth(): Renderable|RedirectResponse
    {
        if (Auth::check() && Auth::user()->hasRole($this->dashboardUsers()->keys())) {
            return redirect()->route($this->serveNamedRoute());
        }
        return view('front.account.login');
    }

    public function login(): RedirectResponse
    {
        $this->validation_rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
        $this->validation_messages = [
            'email.required' => __('validation.required', ['attribute' => __('forms.email')]),
            'email.email' => __('validation.email', ['attribute' => __('forms.email')]),
            'password' => __('validation.required', ['attribute' => __('forms.password')]),
        ];

        $this->validation();

        $credentials = request()->only('email', 'password');
        if (Auth::attempt($credentials, request()->has('remember'))) {

            return redirect()->route($this->serveNamedRoute());
        }

        return redirect()->route('account.login');
    }

    public function logout(): RedirectResponse
    {

        \Log::info('Logout from LoginController');
        Session::flush();
        Auth::logout();

        return redirect()->route('account.auth');
    }

/*
    public function registration()
    {
        return view('auth.registration');
    }

    public function customRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("dashboard")->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }
*/
    private function serveNamedRoute(): string
    {
        return 'panel.dashboard';
        //return auth()->user()->hasRole(auth()->user()->someRoleFromTheUserTrait()) ? 'account.route_1.dashboard' : 'account.route_2.dashboard';
    }


}
