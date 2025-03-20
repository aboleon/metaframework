<?php

namespace App\Actions\Order;

use App\Http\Requests\OrderNoteRequest;
use App\Models\Order;
use App\Models\Order\Note;
use MetaFramework\Traits\Ajax;
use MetaFramework\Traits\Responses;
use Throwable;

class OrderNoteActions
{
    use Ajax;
    use Responses;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();
        $this->fetchCallback();
    }

    public function addNote(): array
    {
        // Update Note

        if ((int)request('id') > 0) {
            try {
                $note = Note::findOrFail(request('id'));
                $note->updated_by = auth()->id();
                $message = "La note a été modifiée";
            } catch (Throwable $e) {
                $this->responseException($e, "La note n'a pas pu être récupérée.");
                return $this->fetchResponse();
            }

        } else {

            // Create Note

            if (!request()->filled('order_id')) {
                $this->responseWarning("L'ordre n'est pas indiqué.");
                return $this->fetchResponse();
            }

            try {
                $order = Order::findOrFail(request('order_id'));
                $message = "La note a été ajoutée";
            } catch (Throwable $e) {
                $this->responseException($e, "L'ordre n'a pas pu être récupéré");
                return $this->fetchResponse();
            }

            $note = new Note();
            $note->created_by = auth()->id();;
            $note->order_id = $order->id;
        }


        // Request validation
        $validation = new OrderNoteRequest();
        $note->addValidationRules($validation->rules());
        $note->addValidationMessages($validation->messages());
        $note->validation();

        try {
            // Note Save
            $note->note = request('note');
            $note->save();

            $this->responseSuccess($message);
            $this->responseElement('title', "Note créée le " . now()->format('d/m/Y à H:i') . ' par ' . auth()->user()->names());
            $this->responseElement('note_id', $note->id);

        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            return $this->fetchResponse();
        }
    }

    public function removeNote(): array
    {

        try {
            $note = Note::findOrFail(request('id'));
            $note->delete();
            $this->responseSuccess("La note a été supprimée");
        } catch (Throwable $e) {
            $this->responseException($e, "La note n'a pas pu être supprimée.");
        }
        return $this->fetchResponse();
    }
}
