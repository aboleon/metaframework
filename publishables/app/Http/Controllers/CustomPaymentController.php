<?php

namespace App\Http\Controllers;

use App\Enum\PaymentCallState;
use App\Interfaces\CustomPaymentInterface;
use App\Models\CustomPaymentCall;
use App\Services\PaymentProvider\PayBox\TransactionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Log;
use MetaFramework\Traits\Responses;
use Throwable;

class CustomPaymentController extends Controller
{
    use Responses;

    private ?CustomPaymentCall $customPayment = null;
    private string $fetchType = 'uuid';

    /**
     *  Lorsque l'appel d'une des methodes de retour est fait hors routes auto
     *  ne pas rediriger vers le custom model
     */

    public function disableRedirect(): self
    {
        $this->disable_redirects = true;

        return $this;
    }

    protected function setDefaultRedirect(): self
    {
        $this->redirect_to = route('custompayment.form', $this->customPayment->encryptedId());

        return $this;
    }

    protected function setRedirect(): ?RedirectResponse
    {
        if ( ! $this->disable_redirects) {
            $this->setDefaultRedirect();

            return redirect()->to($this->redirect_to);
        }

        return null;
    }

    public function setModel(CustomPaymentCall $customPaymentCall): self
    {
        $this->customPayment = $customPaymentCall;

        return $this;
    }

    public function show(string $uuid)
    {
        $this->setFetchTypeEncryption()->fetchModel($uuid);
        $this->customPayment->responseElement('state', 'call');

        return $this->renderForm($this->customPayment->shoppable);
    }

    public function renderForm(CustomPaymentInterface $model)
    {
        return $model->renderCustomPaymentForm();
    }

    public function success(): ?RedirectResponse
    {
        $this->fetchModel();
        $this->processSuccessfullResponse();
        $this->sendPaymentResponseNotification();

        return $this->setRedirect();
    }

    public function decline(): RedirectResponse
    {
        $this->fetchModel();
        $this->customPayment
            ->updateState(PaymentCallState::DECLINED->value)
            ->save();
        $this->sendPaymentResponseNotification();

        return $this->setRedirect();
    }

    public function cancel(): RedirectResponse
    {
        $this->fetchModel();
        $this->customPayment
            ->updateState(PaymentCallState::CANCEL->value)
            ->save();
        $this->sendPaymentResponseNotification();

        return $this->setRedirect();
    }

    public function autoresponse(): void
    {
        $this->fetchModel();

        // Ne pas retraiter si déja traité via les retours de base
        if ($this->customPayment->state != PaymentCallState::default() && !is_null($this->customPayment->closed_at)) {
            Log::info("PayBox Autoresponse: model already processed ". $this->customPayment->id);
            return;
        }

        Log::info("PayBox Autoresponse: processing model ". $this->customPayment->id);

        if (TransactionRequest::successful()) {
            $this->processSuccessfullResponse();
        } else {
            $this->customPayment
                ->updateState(PaymentCallState::DECLINED->value)
                ->save();
        }

        $this->sendPaymentResponseNotification();
    }

    public function sendPaymentMail(CustomPaymentCall $model): array
    {
        return $model->sendPaymentMail();
    }

    private function fetchModel(string $uuid = ''): void
    {
        if ( ! $this->customPayment) {
            match ($this->fetchType) {
                'encryption' => $this->fetchModelByEncryption($uuid),
                default => $this->fetchModelByUuid($uuid),
            };

            if ( ! $this->customPayment) {
                $this->responseError(__('errors.impossible_identify_payment_call'));
                $this->abort();
            }
        }
    }

    private function fetchModelByUuid(string $uuid = ''): void
    {
        $uuid = $uuid ?: request('uuid');
        if ($uuid) {
            $this->customPayment = CustomPaymentCall::where('uuid', $uuid)->first();
        }
    }

    private function fetchModelByEncryption(string $uuid = '')
    {
        if ($uuid) {
            try {
                $this->customPayment = CustomPaymentCall::find(Crypt::decryptString($uuid));
            } catch (Throwable $e) {
                $this->responseError($e->getMessage());
                $this->abort();
            }
        }
    }


    private function setFetchTypeEncryption(): self
    {
        $this->fetchType = 'encryption';

        return $this;
    }

    private function processSuccessfullResponse()
    {
        $this->customPayment
            ->updateState(PaymentCallState::SUCCESS->value);
        $this->customPayment->closed_at = now();
        $this->customPayment->save();


        $this->customPayment->shoppable->processSuccessCustomPayment();

        return $this;
    }

    private function sendPaymentResponseNotification()
    {
        return $this->customPayment->sendPaymentResponseNotification();
    }

    private function abort(?string $message = ''): void
    {
        $message = $message ?: __('errors.impossible_identify_payment_call');
        if ( ! $this->disable_redirects) {
            abort(404, $message);
        }
    }


}
