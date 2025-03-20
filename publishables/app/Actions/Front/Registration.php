<?php

namespace App\Actions\Front;

use App\Accessors\EventAccessor;
use App\Mail\Front\CreateAccountRequestMail;
use App\Models\Event;
use App\Models\UserRegistration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use MetaFramework\Traits\Ajax;
use MetaFramework\Traits\Responses;
use ReflectionException;
use Throwable;

class Registration
{
    use Ajax;
    use Responses;

    private ?UserRegistration $instance = null;
    private Event $event;

    public function setInstance(UserRegistration $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    public function getInstance(): UserRegistration
    {
        return $this->instance;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function sendMail(): self
    {
        $this->checkInstanceState();

        if ( ! $this->hasErrors()) {
            $eventAccessor = new EventAccessor($this->event);

            try {
                $this->responseSuccess(
                    "<p class='fs-5'>"
                    .__('front/register.email_sent_to', ['mail' => $this->instance->email]).
                    "</p><p>".__('front/register.mail_validation_notice', ['email' => $eventAccessor->adminEmail()])."</p>",
                );

                $this->sendEmailWithToken();
            } catch (Throwable $e) {
                $this->responseException($e);
                $this->responseError(__('front/register.an_error_occurred'));
            }
        }

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    private function sendEmailWithToken(): ?SentMessage
    {
        $mail = new CreateAccountRequestMail(
            eventName: $this->instance->event->texts->name,
            eventUrl: EventAccessor::getEventFrontUrl($this->instance->event),
            eventMediaUrl: EventAccessor::getBannerUrlByEvent($this->instance->event),
            createAccountUrl: route('front.confirm-public-account', [
                'locale' => App::getLocale(),
                'token'  => $this->instance->id,
            ]),
        );

        $this->responseDebug($mail->render(),"Mail envoyé");

        // Send the email
        return Mail::to($this->instance->email)->send($mail);
    }

    public function resentRegistrationMail(): self
    {
        $this->instance = UserRegistration::find((string)request('instance'));

        if ( ! $this->instance) {
            try {
                $this->setEvent(Event::findOrFail((int)request('event')));
            } catch (ModelNotFoundException $e) {
                $this->responseException($e);
                $this->responseError(__('errors.event_not_found'));

                return $this;
            }

            $this->responseError(__('front/register.instance_not_found', ['route' => route('front.event.registerByEmail', ['locale' => app()->getLocale(), 'event' => $this->event->id])]));

            return $this;
        }


        $this->checkInstanceState();


        if ( ! $this->hasErrors()) {
            try {
                $this->responseSuccess(__('front/register.another_email_was_sent'));
                $this->sendEmailWithToken();
            } catch (Throwable $e) {
                $this->responseException($e);
                $this->responseError(__('front/register.an_error_occurred'));
            }
        }

        return $this;
    }

    private function checkInstanceState(): void
    {
        // If the registration hasn't been validated yet

        if ($this->instance->validated_at && $this->instance->terminated_at) {
            // Default warning if already registered
            $this->responseWarning(__('front/register.you_have_already_registered'));
        }

        // If validated but not terminated
        if ($this->instance->validated_at && ! $this->instance->terminated_at) {
            $this->responseWarning(__('front/register.you_have_already_validated', [
                'route' => route('front.register-public-account-form', [
                    'locale' => app()->getLocale(),
                    'token'  => $this->instance->id,
                ]),
            ]));
        }
    }

    public function validateByToken(): self
    {
        $this->checkInstanceState();

        if ( ! $this->hasErrors()) {
            $this->instance->validated_at = now();
            $this->instance->save();
        }

        return $this;
    }
}
