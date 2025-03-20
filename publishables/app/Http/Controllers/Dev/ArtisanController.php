<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use MetaFramework\Traits\Ajax;

class ArtisanController extends Controller
{
    use Ajax;

    public function optimizeClear(): array
    {
        Artisan::call('optimize:clear');

        $this->responseSuccess("Le cache application a été réinitialisé");

        return $this->fetchResponse();
    }
}
