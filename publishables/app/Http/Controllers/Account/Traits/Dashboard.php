<?php

namespace App\Http\Controllers\Account\Traits;

use App\Models\Account;

trait Dashboard
{

    protected Account $dashboarder;

    public function setDashboarder(): void
    {
        $this->dashboarder = Account::find(auth()->id());
        view()->share('dashboarder', $this->dashboarder);
    }

}
