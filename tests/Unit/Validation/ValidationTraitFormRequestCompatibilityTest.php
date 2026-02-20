<?php

declare(strict_types=1);

namespace Tests\Unit\Validation;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;
use MetaFramework\Services\Validation\ValidationAbstract;
use MetaFramework\Services\Validation\ValidationInstance;
use Tests\TestCase;

class ValidationTraitFormRequestCompatibilityTest extends TestCase
{
    public function test_legacy_rules_validation_still_works(): void
    {
        $this->bindRequest(['email' => 'john@example.com']);

        $instance = new ValidationInstance;
        $instance->addValidationRules([
            'email' => ['required', 'email'],
        ]);
        $instance->validation();

        $this->assertSame('john@example.com', $instance->validatedData('email'));
    }

    public function test_validation_abstract_hooks_are_applied(): void
    {
        $this->bindRequest([
            'name' => '  John Doe  ',
            'token' => 'allowed',
        ]);

        $instance = new ValidationInstance;
        $validation = new class extends ValidationAbstract
        {
            public bool $prepared = false;
            public bool $withValidatorCalled = false;
            public bool $passed = false;

            public function prepareForValidation(): void
            {
                $this->prepared = true;
                request()->merge([
                    'name' => trim((string) request('name')),
                ]);
            }

            public function rules(): array
            {
                return [
                    'name' => ['required', 'string'],
                    'token' => ['required', 'string'],
                ];
            }

            public function withValidator(ValidatorContract $validator): void
            {
                $this->withValidatorCalled = true;
            }

            public function after(ValidatorContract $validator): array
            {
                return [
                    function (ValidatorContract $validator): void {
                        if (request('token') !== 'allowed') {
                            $validator->errors()->add('token', 'Invalid token.');
                        }
                    },
                ];
            }

            public function passedValidation(): void
            {
                $this->passed = true;
            }
        };

        $instance->validation($validation);

        $this->assertTrue($validation->prepared);
        $this->assertTrue($validation->withValidatorCalled);
        $this->assertTrue($validation->passed);
        $this->assertSame('John Doe', $instance->validatedData('name'));
    }

    public function test_validation_abstract_authorization_is_enforced(): void
    {
        $this->bindRequest(['email' => 'john@example.com']);

        $instance = new ValidationInstance;
        $validation = new class extends ValidationAbstract
        {
            public function authorize(): bool
            {
                return false;
            }

            public function rules(): array
            {
                return [
                    'email' => ['required', 'email'],
                ];
            }
        };

        $this->expectException(AuthorizationException::class);

        $instance->validation($validation);
    }

    public function test_form_request_lifecycle_is_applied(): void
    {
        TestFormRequest::$withValidatorCalled = false;
        TestFormRequest::$passedValidationCalled = false;

        $this->bindRequest(['email' => ' USER@EXAMPLE.COM ']);

        $instance = new ValidationInstance;
        $instance->validation(new TestFormRequest);

        $this->assertSame('user@example.com', $instance->validatedData('email'));
        $this->assertTrue(TestFormRequest::$withValidatorCalled);
        $this->assertTrue(TestFormRequest::$passedValidationCalled);
    }

    public function test_form_request_after_hook_can_fail_validation(): void
    {
        $this->bindRequest(['email' => 'user@forbidden.test']);

        $instance = new ValidationInstance;

        $this->expectException(ValidationException::class);

        $instance->validation(new TestFormRequest);
    }

    private function bindRequest(array $payload): void
    {
        $request = HttpRequest::create('/validation-test', 'POST', $payload);
        $request->setUserResolver(static fn () => null);
        $request->setRouteResolver(static fn () => null);

        $this->app->instance('request', $request);
    }
}

class TestFormRequest extends FormRequest
{
    public static bool $withValidatorCalled = false;
    public static bool $passedValidationCalled = false;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        self::$withValidatorCalled = true;
    }

    public function after(): array
    {
        return [
            function (ValidatorContract $validator): void {
                if (str_ends_with((string) $this->input('email'), '@forbidden.test')) {
                    $validator->errors()->add('email', 'Forbidden domain.');
                }
            },
        ];
    }

    protected function passedValidation(): void
    {
        self::$passedValidationCalled = true;
    }
}

