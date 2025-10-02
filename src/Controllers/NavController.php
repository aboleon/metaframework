<?php

namespace MetaFramework\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use MetaFramework\Accessors\Locale;
use MetaFramework\Models\Nav;
use MetaFramework\Printers\Nav\Table;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Support\Traits\Responses;
use Throwable;

class NavController extends Controller
{
    use Responses;
    use ValidationTrait;

    public function index(): Renderable
    {
        $nav = new Nav;
        $data['zones'] = $nav->zones;
        $zones = array_keys($nav->zones);

        foreach($zones as $zone) {
            $data[$zone] = (new Table($nav->query()->where('zone', $zone)->get()->sortBy('position')))();
        }

        return view('mfw::nav.index')->with($data);
    }

    public function create(): Renderable
    {
        $nav = new Nav;

        return view('mfw::nav.edit')->with([
            'data' => $nav,
            'route' => route('mfw.nav.store'),
            'parent' => (int)request('parent') ? Nav::query()->where('id', request('parent'))->first() : null,
            'selectables' => $nav->fetchSelectableInventory()
        ]);
    }

    public function store(): RedirectResponse
    {
        $this->basicValidation();
        $this->validation();

        try {
            $nav = new Nav;
            return $nav->process()->sendResponse();

        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->sendResponse();
        }
    }

    public function edit(Nav $nav): Renderable
    {
        return view('mfw::nav.edit')->with([
            'data' => $nav,
            'route' => route('mfw.nav.update', $nav),
            'parent' => $nav->parent ? Nav::query()->where('id', $nav->parent)->first() : null,
            'selectables' => $nav->fetchSelectableInventory()
        ]);
    }

    public function update(Nav $nav): RedirectResponse
    {
        $this->basicValidation();
        $this->validation();

        try {
            return $nav->process()
                ->sendResponse();

        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->sendResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \Exception
     */
    public function destroy(Nav $nav): RedirectResponse
    {
        $nav->delete();
        $nav->clearCache();
        $this->responseSuccess("La suppression est effectuée");
        return $this->sendResponse();
    }

    private function basicValidation()
    {
        $multilang_dependent = [
          'title' => Locale::multilang() ? 'title.' . Locale::defaultLocale() : 'title',
        ];

        $this->validation_rules = [
            $multilang_dependent['title']  => 'required',
        ];
        $this->validation_messages = [
            $multilang_dependent['title'] . '.required' => __('validation.required', ['attribute' => __('mfw.title')])
        ];
    }
}
