<?php

namespace App\MailTemplates\Templates;

use App\MailTemplates\Contracts\Template;
use \App\MailTemplates\Models\MailTemplate as Target;
use App\MailTemplates\Traits\MailTemplate;
use App\Models\Event;

class Courrier implements Template
{

    use MailTemplate;

    public function __construct(
        public Event  $event,
        public Target $template
    )
    {
    }

    public function signature(): string
    {
        return 'courrier';
    }

    public function variables(): array
    {
        return array_merge(
            array_flip(\App\MailTemplates\Groups\Event::variables()),
            array_flip(\App\MailTemplates\Groups\Group::variables()),
            array_flip(\App\MailTemplates\Groups\Manager::variables()),
            array_flip(\App\MailTemplates\Groups\Participant::variables()),
        );
    }

    public function summary()//: string
    {
        //return View::make('mailtemplates.mail.mailtemplate', ['template' => $this->template])->render();
    }

    public function setFilePath($file): static
    {
        $this->attachment['file'] = $file;
        return $this;
    }

    public function setFileOptions(array $options): static
    {
        $this->attachment['options'] = $options;
        return $this;
    }

    public function event(): Event
    {
        return $this->event;
    }

    public function template(): \App\MailTemplates\Models\MailTemplate
    {
        return $this->template;
    }

    public function Util_Prenom(): string
    {
        return $this->event->admin->names();
    }


}
