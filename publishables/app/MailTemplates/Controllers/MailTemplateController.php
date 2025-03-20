<?php

namespace App\MailTemplates\Controllers;

use App\Http\Controllers\Controller;
use App\MailTemplates\DataTables\MailTemplateDataTable;
use App\MailTemplates\Enum\MailTemplateFormat;
use App\MailTemplates\Enum\MailTemplateMode;
use App\MailTemplates\Models\MailTemplate;
use App\MailTemplates\PdfPrinter;
use App\MailTemplates\Templates\Courrier;
use App\Models\Event;
use App\Traits\Locale;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use MetaFramework\Actions\Suppressor;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class MailTemplateController extends Controller
{
    use Locale;
    use ValidationTrait;

    public function index(MailTemplateDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('mailtemplates.index');
    }

    public function create(): Renderable
    {
        $data = [
            'data' => new MailTemplate(),
            'route' => route('panel.mailtemplates.store'),
            'page' => null
        ];

        return view('mailtemplates.edit')->with($data);
    }

    public function store()
    {
        $mailtemplate = new MailTemplate();

        $this->basicValidation($mailtemplate);
        $this->validation();

        try {
            $mailtemplate->process();
            $this->responseSuccess(__('mfw.record_created'));

            $this->redirect_to = route('panel.mailtemplates.index');
            $this->saveAndRedirect(route('panel.mailtemplates.index'));

        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    public function getDatatable(Request $request): JsonResponse
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $data = MailTemplate::all();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('subject', function ($data) {
                return $data->subject;
            })
            ->addColumn('identifier', function ($data) {
                return $data->identifier;
            })
            ->addColumn('format', function ($data) {
                return MailTemplateFormat::translated($data->format);
            })
            ->addColumn('orientation', function ($data) {
                return MailTemplateMode::translated($data->orientation);
            })
            ->addColumn('created_at', function ($data) {
                return $data->created_at;
            })
            ->addColumn('action', function ($data) {
                return view('mailtemplates.datatable.action')->with([
                    'data' => $data,
                ])->render();
            })
            ->rawColumns(['action'])
            ->make();
    }

    public function show(MailTemplate $mailtemplate)
    {
        $lg = app()->getLocale();
        $as = 'mail';

        if (in_array(request('lg'), config('mfw.translatable.locales'))) {
            $lg = request('locale');
        }
        if (in_array(request('as'), ['mail', 'pdf'])) {
            $as = request('as');
        }

        $parsed = (new Courrier(Event::inRandomOrder()->first(), $mailtemplate))->serve();

        if ($as == 'pdf') {
            return (new PdfPrinter($parsed))->stream();
        }

        return view('mailtemplates.show')->with([
            'parsed' => $parsed
        ]);
    }

    public function edit(MailTemplate $mailtemplate): Renderable
    {
        $data = [
            'data' => $mailtemplate,
            'route' => route('panel.mailtemplates.update', $mailtemplate),
        ];

        return view('mailtemplates.edit')->with($data);
    }


    public function update(MailTemplate $mailtemplate): RedirectResponse
    {
        $this->basicValidation($mailtemplate);
        $this->validation();

        try {
            $mailtemplate->process();
            $this->responseSuccess(__('mfw.record_updated'));

            $this->redirect_to = route('panel.mailtemplates.index');
            $this->saveAndRedirect(route('panel.mailtemplates.index'));

        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->sendResponse();
    }

    /**
     * @throws \Exception
     */
    public function destroy(MailTemplate $mailtemplate): RedirectResponse
    {
        return (new Suppressor($mailtemplate))
            ->remove()
            ->responseSuccess(__('ui.record_deleted'))
            ->redirectRoute('panel.mailtemplates.index')
            ->whitout('object')
            ->sendResponse();
    }

    public function duplicate(MailTemplate $template): RedirectResponse
    {
        $cloned = $template->replicate();
        $cloned->identifier = Str::random(10);
        $cloned->setTranslation('subject', app()->getLocale(), $cloned->subject . (' (dupliqué)'));
        $cloned->touch();
        $cloned->save();
        $this->redirectTo(route('panel.mailtemplates.edit', $cloned));
        return $this->sendResponse();
    }

    private function basicValidation(MailTemplate $mailtemplate)
    {
        $this->validation_rules = [
            'identifier' => 'required|unique:mailtemplates,identifier' . ($mailtemplate->id ? ',' . $mailtemplate->id : ''),
            'subject.' . $this->defaultLocale() => 'required',
            'content.' . $this->defaultLocale() => 'required',
            'orientation' => 'required',
            'format' => 'required',
        ];
        $this->validation_messages = [
            'identifier.required' => __('validation.required', ['attribute' => "L'identifiant"]),
            'orientation.required' => __('validation.required', ['attribute' => "L'orientation"]),
            'format.required' => __('validation.required', ['attribute' => "Le format"]),
            'identifier.unique' => __('validation.unique', ['attribute' => "L'identifiant"]),
            'subject.' . $this->defaultLocale() . '.required' => __('validation.required', ['attribute' => "Le sujet du mail"]),
            'content.' . $this->defaultLocale() . '.required' => __('validation.required', ['attribute' => "Le contenu du mail"]),
        ];
    }
}
