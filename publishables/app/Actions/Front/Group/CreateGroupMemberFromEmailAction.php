<?php

namespace App\Actions\Front\Group;

use App\Accessors\Accounts;
use App\Accessors\Countries;
use App\Accessors\EventContactAccessor;
use App\Accessors\Front\FrontCache;
use App\Accessors\Users;
use App\Actions\EventManager\EventContact\GetUserInfo;
use App\Actions\Front\Registration;
use App\Http\Controllers\Front\Auth\AccountRegistrationController;
use App\Models\Event;
use App\Models\SystemUser;
use Illuminate\Support\Facades\Validator;
use MetaFramework\Traits\Ajax;

class CreateGroupMemberFromEmailAction
{
    use Ajax;

    public function create(): array
    {
        $this->enableAjaxMode();
        $this->fetchInput();

        $event = FrontCache::getEvent();

        # Validation
        $validator = Validator::make([
            'email'          => request('email'),
            'event_group_id' => request('event_group_id'),
        ], [
            'email'          => 'required|email',
            'event_group_id' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            foreach ($errors as $error) {
                $this->responseError($error);
            }

            return $this->fetchResponse();
        }

        $email    = $validator->validated()['email'];

        # Block System Users
        if (SystemUser::where('email', $email)->exists()) {
            $this->responseWarning(__('front/register.this_email_is_not_allowed'));

            return $this->fetchResponse();
        }

        # Check and return existing account // mais pquoi ?
        $account = Accounts::getAccountByEmail($email);

        if ($account) {
            $eventContact = EventContactAccessor::getEventContactByEventAndUser($event->id, $account->id);

            $accountAccessor = new Accounts($account);

            $address = $accountAccessor->billingAddress();
            $account->location = __('errors.undefinded_locality');
            if ($address) {
                $account->location = rtrim($address->locality . Countries::getCountryName($address->country_code), ',');

            }

            $account->participation_type = $eventContact?->participationType?->name;
            $this->responseElement('userInfo', $account);
            return $this->fetchResponse();
        }

        request()->merge(['rtype' => 'group-member']);

        # Make Registration
        $registration = new AccountRegistrationController();

        $instance = $registration->createRegistrationInstance($validator, $event);

        $this->pushMessages(
            (new Registration())->ajaxMode()->setEvent($event)->setInstance($instance)->sendMail(),
        );
        # Auto validate
        $this->pushMessages(
            (new Registration())->ajaxMode()->setInstance($instance)->validateByToken()
        );

        # Redirect to UI
        $this->responseElement("url", route('front.register-public-account-form', [
            'locale' => app()->getLocale(),
            'token' => $instance->id,
            'event_group_id'       => $validator->validated()['event_group_id'],
        ]));

        return $this->fetchResponse();
    }


}
