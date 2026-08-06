<?php

declare(strict_types=1);

namespace MetaFramework\Controllers;

use Illuminate\Support\Facades\DB;
use MetaFramework\Actions\DevMaintenanceActions;
use MetaFramework\Actions\TranslatableActions;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Support\Traits\Ajax;
use MetaFramework\Support\UserRoles;

class AjaxController extends Controller
{
    use Ajax;
    use ValidationTrait;

    /**
     * Statut de publication d'un élément ayant la propriété "published" en DB
     * via la fonctionnalité JS agnostique dédiée
     */
    protected function publishedStatus(): array
    {
        $result = [];
        if (request()->filled('class') && request()->filled('id') && class_exists(request('class'))) {
            $class = request('class');
            $object = new $class;
            $object = $object->find(request('id'));
            $object->published = (request('from') == 'online' ? null : now());
            $object->save();
            $result['success'] = 1;
        } else {
            $result['error'] = 1;
        }

        return $result;
    }

    /**
     * Fonction générique drag&drop sur des éléments ayant la classe '.sortable"
     */
    protected function sortable(): array
    {
        $targets = ['content'];

        if (in_array(request('target'), $targets) && request()->filled('data')) {
            DB::beginTransaction();
            foreach (request('data') as $item) {
                DB::table(request('target'))->where('id', $item['id'])->update(['position' => $item['index']]);
            }
            DB::commit();
            $this->responseSuccess("L'ordre a été mis à jour");

            return $this->fetchResponse();
        }

        return [];
    }

    protected function translate_translatables(): array
    {
        return (new TranslatableActions)
            ->ajaxMode()
            ->translateTranslatables()
            ->fetchResponse();
    }

    public function artisanOptimize(): array
    {
        if (! $this->canRunMaintenanceActions()) {
            return $this->denyMaintenanceAccess();
        }

        return new ArtisanController()->ajaxMode()->optimizeClear();
    }

    public function artisanMigrate(): array
    {
        if (! $this->canRunMaintenanceActions()) {
            return $this->denyMaintenanceAccess();
        }

        return new ArtisanController()->ajaxMode()->migrate((bool) request('rollback'), (bool) request('confirmed'));
    }

    public function restartQueueWorkers(): array
    {
        return (new DevMaintenanceActions)->ajaxMode()->restartQueueWorkers()->fetchResponse();
    }

    public function runArtisanCommand(): array
    {
        return (new DevMaintenanceActions)->ajaxMode()->runArtisanCommand()->fetchResponse();
    }

    public function composerUpdateDev(): array
    {
        if (! $this->canRunMaintenanceActions()) {
            return $this->denyMaintenanceAccess();
        }

        return new ArtisanController()->ajaxMode()->composerUpdateDev();
    }

    public function composerUpdateProd(): array
    {
        if (! $this->canRunMaintenanceActions()) {
            return $this->denyMaintenanceAccess();
        }

        return new ArtisanController()->ajaxMode()->composerUpdateProd();
    }

    public function composerInstallProd(): array
    {
        if (! $this->canRunMaintenanceActions()) {
            return $this->denyMaintenanceAccess();
        }

        return new ArtisanController()->ajaxMode()->composerInstallProd();
    }

    public function composerDumpAutoload(): array
    {
        if (! $this->canRunMaintenanceActions()) {
            return $this->denyMaintenanceAccess();
        }

        return new ArtisanController()->ajaxMode()->composerDumpAutoload();
    }

    private function canRunMaintenanceActions(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && method_exists($user, 'hasRole')
            && $user->hasRole([UserRoles::CORE_DEV_KEY, UserRoles::CORE_SUPER_ADMIN_KEY]);
    }

    private function denyMaintenanceAccess(): array
    {
        $this->responseError(__('mfw::mfw-users.errors.access_denied'));

        return $this->fetchResponse();
    }
}
