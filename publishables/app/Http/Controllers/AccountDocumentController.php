<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountDocumentRequest;
use App\Models\Account;
use App\Models\AccountDocument;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Traits\DateManipulator;
use Throwable;

class AccountDocumentController extends Controller
{
    use DateManipulator;
    use ValidationTrait;

    /**
     * Show the form for creating a new resource.
     */
    public function create(Account $account): Renderable
    {
        return view('accounts.document')->with([
            'route' => route('panel.accounts.documents.store', $account),
            'title' => 'Ajouter un document pour ' . $account->names(),
            'account' => $account,
            'user_route' => 'panel.accounts',
            'redirect_to' => request()->input('redirect_to'),
            'data' => new AccountDocument()
        ]);
    }


    public function store(Account $account, AccountDocumentRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request, 'documents')) {
            return $this->sendResponse();
        }

        try {
            $this->dateFormatter();
            $account->documents()->save(new AccountDocument($this->validated_data['documents']));

            $this->responseSuccess("Le document est ajouté.");
            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));

        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            return $this->sendResponse();
        }
    }

    private function dateFormatter(): void
    {
        $this->validated_data['documents']['emitted_at'] = $this->parseDate($this->validated_data['documents']['emitted_at']);
        $this->validated_data['documents']['expires_at'] = $this->parseDate($this->validated_data['documents']['expires_at']);
    }

    public function edit(Account $account, AccountDocument $document): Renderable
    {
        return view('accounts.document')->with([
            'account' => $account,
            'data' => $document,
            'route' => route('panel.accounts.documents.update', [$account, $document]),
            'method' => 'put',
            'redirect_to' => request()->input('redirect_to'),
            'title' => 'Éditer un document',
        ]);
    }

    public function update(Account $account, AccountDocument $document, AccountDocumentRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request, 'documents')) {
            return $this->sendResponse();
        }

        try {
            $this->dateFormatter();
            $document->update($this->validated_data['documents']);
            $this->responseSuccess("Le document est mis à jour.");
        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));
            return $this->sendResponse();
        }
    }

    public function destroy(Account $account, AccountDocument $document): RedirectResponse
    {
        try {
            $document->delete();
            $this->responseSuccess("Le document a été supprimé");
        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }
}
