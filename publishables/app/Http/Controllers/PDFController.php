<?php

namespace App\Http\Controllers;
class PDFController extends Controller
{
    public function distribute(string $type, string $identifier)
    {

        $printer = '\App\Printers\PDF\\'.ucfirst($type);

        if(class_exists($printer)) {
            return (new $printer($identifier))();
        } else {
            abort(404,"PDF inconnu.");
        }

    }
}
