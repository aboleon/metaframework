<?php

namespace App\MailTemplates\Models;

use MetaFramework\Traits\Responses;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes};
use MetaFramework\Actions\Translator;
use MetaFramework\Polyglote\Traits\Translation;
use MetaFramework\Traits\AccessKey;

class MailTemplate extends Model
{
    use AccessKey;
    use Responses;
    use Translation;
    use SoftDeletes;


    protected $guarded = [];
    protected $table = 'mailtemplates';

    public object $instance;

    public array $fillables = [
        'subject' => [
            'label' => 'Sujet',
        ],
        'content' => [
            'type' => 'textarea',
            'label' => 'Contenu',
        ],
    ];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->translatable = array_keys($this->fillables);
        $this->instance = $this;
    }

    public function model(): static
    {
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function process(): object
    {
        $this->identifier = request('identifier');
        $this->format = request('format');
        $this->orientation = request('orientation');

        (new Translator($this))
            ->update();

        return $this;
    }

}
