<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use Illuminate\Auth\GenericUser;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;
use MetaFramework\Controllers\AjaxController;
use Tests\TestCase;

class RunArtisanCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/test/dev-artisan', [AjaxController::class, 'runArtisanCommand']);
        Process::fake(['*' => Process::result(output: 'Laravel Framework')]);
    }

    public function test_developers_and_super_admins_can_run_commands(): void
    {
        foreach (['dev', 'super-admin'] as $role) {
            $this->actingAs($this->user($role));

            $this->postJson('/test/dev-artisan', ['command' => '--version', 'callback' => 'removeVeil'])
                ->assertOk()
                ->assertJsonMissing(['error' => true])
                ->assertJsonPath('callback', 'removeVeil')
                ->assertJsonPath('mfw_ajax_messages.0.info', 'Laravel Framework');
        }

        Process::assertRanTimes(static fn (PendingProcess $process): bool => $process->command === [
            'php', base_path('artisan'), '--version', '--no-interaction',
        ], 2);
    }

    public function test_denied_requests_return_ui_messages_without_starting_a_process(): void
    {
        $this->postJson('/test/dev-artisan', ['command' => '--version', 'callback' => 'removeVeil'])
            ->assertOk()
            ->assertJsonPath('error', true)
            ->assertJsonPath('callback', 'removeVeil')
            ->assertJsonPath('mfw_ajax_messages.0.danger', __('mfw::mfw.dev.unauthorized'));

        $this->actingAs($this->user('admin'));

        $this->postJson('/test/dev-artisan', ['command' => '--version'])
            ->assertOk()
            ->assertJsonPath('error', true)
            ->assertJsonPath('mfw_ajax_messages.0.danger', __('mfw::mfw.dev.unauthorized'));

        Process::assertNothingRan();
    }

    public function test_invalid_commands_return_ui_validation_messages(): void
    {
        $this->actingAs($this->user('super-admin'));

        $this->postJson('/test/dev-artisan', ['command' => '', 'callback' => 'removeVeil'])
            ->assertOk()
            ->assertJsonPath('error', true)
            ->assertJsonPath('callback', 'removeVeil')
            ->assertJsonStructure(['mfw_ajax_messages' => [['danger']]]);

        Process::assertNothingRan();
    }

    private function user(string $role): GenericUser
    {
        return new class(['id' => 1, 'role' => $role]) extends GenericUser
        {
            public function hasRole(array|string $roles): bool
            {
                return in_array($this->role, (array)$roles, true);
            }
        };
    }
}
