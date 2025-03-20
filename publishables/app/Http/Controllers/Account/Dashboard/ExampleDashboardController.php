<?php

namespace App\Http\Controllers\Account\Dashboard;


use App\Http\Controllers\Account\Traits\Dashboard;
use App\Http\Controllers\Controller;
use App\Traits\Users;
use MetaFramework\Services\Validation\ValidationTrait;

class ExampleDashboardController extends Controller
{
    use Dashboard;
    use Users;
    use ValidationTrait;

    public function __construct()
    {
        $this->middleware('roles:'.implode('|', $this->dashboardUsers()->pluck('label', 'id')->toArray()));
    }

    public function dashboard()
    {
        $this->setDashboarder();

        return view('front.account.dashboard');
    }

}
