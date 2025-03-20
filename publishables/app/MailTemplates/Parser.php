<?php

namespace App\MailTemplates;

use App\MailTemplates\Models\MailTemplate;
use Illuminate\Support\Str;
use MetaFramework\Traits\Responses;
use Throwable;

class Parser
{
    use Responses;

    public MailTemplate $template;
    private static $_instance;

    public function __construct(MailTemplate $mailTemplate)
    {
        $this->template = $mailTemplate;
    }

    public function instance()
    {
        if (is_null(self::$_instance)) {
            try {
                $class = '\App\MailTemplates\Templates\\' . Str::studly($this->template->identifier);
                $reflection = new \ReflectionClass($class);

                if ($reflection->implementsInterface('\App\MailTemplates\Contracts\Template')) {
                    self::$_instance = new $class;
                    if ($reflection->hasMethod('params')) {
                        collect($reflection->getMethod('params')->getParameters())->each(fn($item, $i = 0) => $this->response['params'][] = '[ ' . $i . ' ] ' . $item->getType() . ' <b>$' . current($item) . '</b>');
                    }
                    $this->response['signature'] = self::$_instance->signature();
                }
            } catch (Throwable $e) {
                $this->responseException($e, "La classe d'éxecution du mail: <b>" . $class . "</b> n'est pas définie ");
            }
        }
        return self::$_instance;
    }
    /*
        if (is_null(self::$_instance)) {
            throw new Exception("Template Instance for idenfifier " . $this->template->identifier . "not found");
        }
    */


}
