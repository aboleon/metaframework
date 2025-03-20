<?php

namespace App\Http\Controllers;

use DB;
use MetaFramework\Actions\Suppressor;
use MetaFramework\Actions\Translator;
use App\Enum\DictionnaryType;
use App\Models\Dictionnary;
use App\Models\DictionnaryEntry;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use MetaFramework\Traits\Responses;
use Throwable;

class DictionnaryEntryController extends Controller
{
    use Responses;

    private string $view;
    private object $data;

    public function __construct()
    {
        $this->view = 'dictionnary.entries.index';
    }

    public function index(?Dictionnary $dictionnary): Renderable
    {
        return view($this->view($dictionnary))->with([
            'entries' => $dictionnary->id ? $dictionnary->entries()->orderBy(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.fr"))'), 'asc')->paginate() : collect(),
            'label' => ($dictionnary->id ? '<span class="text-secondary">Entrées du dictionnaire</span> ' . $dictionnary->name : 'Entrées des dictionnaires'),
            'dictionnary' => $dictionnary,
        ]);
    }

    public function create(Dictionnary $dictionnary): Renderable|RedirectResponse
    {
        return view('dictionnary.create')->with([
            'data' => new DictionnaryEntry,
            'subclass' => $dictionnary->entrySubClass(),
            'route_index' => $this->routeIndex($dictionnary),
            'dictionnary' => $dictionnary,
            'label' => '<span class="text-secondary">Ajouter une entrée au dictionnaire</span> ' . $dictionnary->name,
            'route' => route('panel.dictionnary.entries.store', $dictionnary)
        ]);
    }

    public function edit(DictionnaryEntry $dictionnaryentry): Renderable
    {
        return view('dictionnary.create')->with([
            'data' => $dictionnaryentry,
            'subclass' => $dictionnaryentry->dictionnary->entrySubClass(),
            'route_index' => $this->routeIndex($dictionnaryentry->dictionnary),
            'dictionnary' => $dictionnaryentry->dictionnary,
            'label' => '<span class="text-secondary">Éditer une entrée du dictionnaire </span> ' . $dictionnaryentry->dictionnary->name,
            'route' => route('panel.dictionnaryentry.update', $dictionnaryentry)
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store(Dictionnary $dictionnary): RedirectResponse
    {
        (new DictionnaryController)->basicValidation();

        $entry = new DictionnaryEntry;

        try {
            $entry->dictionnary_id = $dictionnary->id;

            if (request()->has('subentry')) {
                $entry->parent = request('subentry');
            }
            if (request()->has('custom')) {
                $entry->custom = (array)(request('custom'));
            }

            $entry = (new Translator($entry))
                ->update()
                ->fetchModel();

            $entry->processMedia();

            $entry->responseSuccess(__('ui.record_created'))
                ->redirectTo(route('panel.dictionnary.entries.index', $dictionnary));

            \App\Accessors\Dictionnaries::reset($dictionnary->slug);

        } catch (Throwable $e) {
            $entry->responseException($e);
        }

        return $entry->sendResponse();
    }

    /**
     * @throws \Exception
     */
    public function update(DictionnaryEntry $dictionnaryentry): RedirectResponse
    {
        $dictionnaryentry->processMedia();

        (new DictionnaryController)->basicValidation();

        try {

            if (request()->has('custom')) {
                $dictionnaryentry->custom = (array)request('custom');
            }

            \App\Accessors\Dictionnaries::reset($dictionnaryentry->dictionnary->slug);

            return (new Translator($dictionnaryentry))
                ->update()
                ->responseSuccess(__('ui.record_updated'))
                ->redirectTo(route('panel.dictionnary.entries.index', $dictionnaryentry->dictionnary))
                ->sendResponse();

        } catch (Throwable $e) {
            $dictionnaryentry->responseException($e);
            return $dictionnaryentry->sendResponse();
        }
    }

    /**
     * @throws \Exception
     */
    public function destroy(DictionnaryEntry $dictionnaryentry): RedirectResponse
    {
        try {
            $dictionnary = $dictionnaryentry->dictionnary;

            $dictionnaryentry->delete();
            $dictionnaryentry->deleteModelMedia();


            \App\Accessors\Dictionnaries::reset($dictionnaryentry->dictionnary->slug);

            $this->redirectTo(route('panel.dictionnary.entries.index', $dictionnary))
                ->responseSuccess(__('ui.record_deleted'))
                ->whitout('object');
        } catch (Throwable $e) {
            $this->responseException($e, "Cette entrée ne peut pas être supprimée car elle est reliée à des contacts.");
        }

        return $this->sendResponse();
    }

    public function subentry(DictionnaryEntry $dictionnaryentry)
    {
        return view('dictionnary.create')->with([
            'data' => new DictionnaryEntry,
            'subclass' => $dictionnaryentry->dictionnary->entrySubClass(),
            'route_index' => $this->routeIndex($dictionnaryentry->dictionnary),
            'dictionnary' => $dictionnaryentry->dictionnary,
            'label' => '<span class="text-secondary">Ajouter une entrée à la catégorie</span> ' . $dictionnaryentry->name .
                '<span class="text-secondary"> du dictionnaire </span> ' . $dictionnaryentry->dictionnary->name,
            'route' => route('panel.dictionnary.entries.store', $dictionnaryentry->dictionnary),
            'subentry' => $dictionnaryentry
        ]);
    }


    private function routeIndex(?Dictionnary $dictionnary): string
    {
        if ($dictionnary->id) {
            return route('panel.dictionnary.entries.index', $dictionnary);
        }
        return route('panel.dictionnaryentry.index');
    }

    private function view(?Dictionnary $dictionnary): string
    {
        if ($dictionnary->id) {
            return match ($dictionnary->type) {
                DictionnaryType::META->value => 'dictionnary.entries.index_meta',
                default => $this->view
            };
        }
        return $this->view;
    }

}
