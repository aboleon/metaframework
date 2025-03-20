<?php


namespace App\Http\Controllers;

use App\Actions\Front\Registration;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Traits\Ajax;

class AjaxPublicController extends Controller
{
    use Ajax;
    use ValidationTrait;

    public function __construct()
    {
        $this->enableAjaxMode();
    }

    public function resentFrontRegistration(): array
    {
        return $this->pushMessages(
            (new Registration())->resentRegistrationMail()
        )->fetchResponse();
    }
}
