<?php

namespace App\Http\Controllers;

use App\Accessors\Dictionnaries;
use App\Actions\EventManager\HotelAssociate;
use App\DataTables\HotelDataTable;
use App\Enum\Stars;
use App\Http\Requests\HotelRequest;
use App\Models\Hotel;
use App\Models\HotelAddress;
use App\Traits\DataTables\MassDelete;
use App\Traits\Locale;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use MetaFramework\Actions\Suppressor;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class HotelController extends Controller
{
    use Locale;
    use MassDelete;
    use ValidationTrait;

    public function index(HotelDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('hotels.index');
    }

    public function create(): Renderable|RedirectResponse
    {
        $dicoHotelServices = Dictionnaries::dictionnary('hotel_service');
        $stars = Stars::toArray();
        $rating = array_combine($stars, $stars);
        return view('hotels.edit')->with([
            'data' => new Hotel,
            'services' => $dicoHotelServices->entries,
            'stars' => $rating,
        ]);
    }


    public function store(HotelRequest $request): RedirectResponse
    {
        $this->ensureDataIsValid($request, 'hotel');
        $this->ensureDataIsValid($request, 'wa_geo');

        if ($this->hasErrors()) {
            return $this->sendResponse();
        }
        try {
            $hotel = Hotel::create($this->validatedData('hotel'));
            $hotel->save();
            $hotel->address()->save(
                new HotelAddress($this->validatedData('wa_geo'))
            );

            $hotel->processMedia();
            $this->responseSuccess(__('ui.record_created'));
            $this->redirect_to = route('panel.hotels.index');
            $this->saveAndRedirect(route('panel.hotels.index'));

            if (request()->filled('post_action')) {
                switch (request('post_action')) {
                    case 'event_hotel_association':
                        $record = (new HotelAssociate($hotel->id, (int)request('event_id')))->associate();
                        $this->redirectTo(route('panel.manager.event.accommodation.edit', ['event' => (int)request('event_id'), 'accommodation' => $record['id']]));
                        break;
                    default:
                }
            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->sendResponse();
    }


    public function edit(Hotel $hotel)
    {
        $dicoHotelServices = Dictionnaries::dictionnary('hotel_service');
        $stars = Stars::toArray();
        $rating = array_combine($stars, $stars);
        return view('hotels.edit')->with([
            'data' => $hotel,
            'services' => $dicoHotelServices->entries,
            'stars' => $rating,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param HotelRequest $request
     * @param Hotel $hotel
     * @return RedirectResponse
     */
    public function update(HotelRequest $request, Hotel $hotel): RedirectResponse
    {
        $this->ensureDataIsValid($request, 'hotel');
        $this->ensureDataIsValid($request, 'wa_geo');

        if ($this->hasErrors()) {
            return $this->sendResponse();
        }
        /*
        d($this->validatedData('hotel'));
        de($this->validatedData());
        */
        try {
            $hotel->update($this->validatedData('hotel'));
            $hotel->address()->update($this->validatedData('wa_geo'));

            $hotel->processMedia();

            $this->responseSuccess(__('ui.record_updated'));
            $this->redirect_to = route('panel.hotels.index');
            $this->saveAndRedirect(route('panel.hotels.index'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->sendResponse();


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Hotel $hotel
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(Hotel $hotel): RedirectResponse
    {
        return (new Suppressor($hotel))
            ->remove()
            ->redirectRoute('panel.hotels.index')
            ->responseSuccess(__('ui.record_deleted'))
            ->whitout('object')
            ->sendResponse();
    }
}
