<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use ReflectionClass;

class DatatablesMassDelete extends Component
{

    public string $normalized_model;
    public string $default_question = 'Est-ce que vous confirmez la suppression des lignes sélectionnées ?';

    /**
     * Create a new component instance.
     * @throws \ReflectionException
     */
    public function __construct(
        public Model|string $model,
        public string       $controllerPath = '',
        public string       $modelPath = '',
        public string       $deletedMessage = '',
        public string       $name = 'name',
        public ?string      $question = null
    )
    {
        $this->model = is_string($this->model) ? $this->model : (new ReflectionClass($this->model))->getShortName();
        $this->normalized_model = str_replace(['/', '\\'], '-', strtolower($this->model));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.datatables-mass-delete');
    }
}
