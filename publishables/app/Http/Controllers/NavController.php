<?php

namespace App\Http\Controllers;

use App\Accessors\Cached;
use App\Models\Nav;
use App\Printers\Nav\Table;
use App\Traits\Locale;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class NavController extends Controller
{
    use Locale;
    use ValidationTrait;

    public function index(): Renderable
    {
        $nav = new Nav;
        $data['zones'] = $nav->zones;
        $zones = array_keys($nav->zones);

        foreach($zones as $zone) {
            $data[$zone] = (new Table($nav->where('zone', $zone)->get()->sortBy('position')))();
        }

        return view('nav.index')->with($data);
    }

    public function create(): Renderable
    {
        $nav = new Nav;

        return view('nav.edit')->with([
            'data' => $nav,
            'route' => route('panel.nav.store'),
            'parent' => (int)request('parent') ? Nav::where('id', request('parent'))->first() : null,
            'selectables' => $nav->fetchSelectableInventory()
        ]);
    }

    public function store()//: RedirectResponse
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

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function edit(Nav $nav): Renderable
    {
        return view('nav.edit')->with([
            'data' => $nav,
            'route' => route('panel.nav.update', $nav),
            'parent' => $nav->parent ? Nav::where('id', $nav->parent)->first() : null,
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
    public function destroy(Nav $nav)
    {
        $nav->delete();
        $nav->clearCache();
        $this->responseSuccess("La suppression est effectuée");
        return $this->sendResponse();
    }

    private function basicValidation()
    {
        $multilang_dependent = [
          'title' => Cached::multilang() ? 'title.' . $this->defaultLocale() : 'title',
        ];

        $this->validation_rules = [
            'price' => 'numeric',
            $multilang_dependent['title']  => 'required',
            'vat_id' => 'integer',
        ];
        $this->validation_messages = [
            'price.numeric' => __('price.integer', ['attribute' => __('price')]),
            'vat_id.integer' => "Le taux de TVA n'est pas spécifié.",
            $multilang_dependent['title'] . '.required' => __('validation.required', ['attribute' => __('ui.title')])
        ];
    }
}
