<?php

namespace App\Actions\Groups;

use App\Actions\Account\ExportAccountProfilesAction;
use DateTime;
use MetaFramework\Traits\Responses;
use Throwable;

class ExportGroupsWrapperAction
{

    use Responses;

    public function exportGroups(): array
    {
        try {
            $groupIds = array_filter(explode(',', request('ids')));
            $groupIds = array_unique($groupIds);
            $fields = request('exportFields');

            $action = new ExportGroupsAction($groupIds, $fields);
            $content = $action->exportToXlsx();

            $this->responseElement('file', base64_encode($content));
            $filename = 'export_' . (new DateTime())->format('Y-m-d_H-i-s') . '.xlsx';
            $this->responseElement('filename', $filename);
            return $this->fetchResponse();

        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->fetchResponse();
        }
    }

}