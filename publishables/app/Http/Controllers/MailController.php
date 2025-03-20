<?php

namespace App\Http\Controllers;

use App\Interfaces\Mailer;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use MetaFramework\Traits\Ajax;
use MetaFramework\Traits\Responses;
use Throwable;

class MailController extends Controller
{
    use Ajax;
    use Responses;

    public function distribute(string $type, object|string $identifier): self
    {

        $mailer = '\App\Mailer\\' . ucfirst($type);

        if (class_exists($mailer) && in_array(Mailer::class, class_implements($mailer))) {

            try {

                $mailer = new $mailer();

                if (is_object($identifier)) {
                    $mailer->setModel($identifier);
                }

                if (is_string($identifier)) {
                    $mailer->setIdentifier($identifier);
                }

                if (method_exists($mailer, 'setData')) {
                    $mailer->setData();
                }

                $mailer->send();

                $this->responseSuccess("L'email a été envoyé.");
            } catch (Throwable $e) {
                $this->responseException($e);
            }

        } else {
            $this->responseError('Mailer inconnu');
        }

        return $this;

    }

    public function render(string $type, string $identifier): RedirectResponse
    {
        $this->distribute($type, $identifier);

        return $this->sendResponse();
    }
}
