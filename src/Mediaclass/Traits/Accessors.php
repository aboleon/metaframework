<?php

namespace MetaFramework\Mediaclass\Traits;

use MetaFramework\Mediaclass\Config;

trait Accessors
{

    /**
     * Retourne Meta/Model
     */
    public function bindedModel(): object
    {
        return $this->model->model()->instance;
    }

    public function settings(): array
    {

        $bindedModelSettings = $this->bindedModel()->mediaClassSettings();

        if ($bindedModelSettings && array_key_exists($this->group, $bindedModelSettings)) {
            return $bindedModelSettings[$this->group];
        }

        if (!is_array($this->bindedModel()->fillables)) {
            return [];
        }
        return (array)current(array_filter(array_filter($this->bindedModel()->fillables, fn($key) => $key == 'media', ARRAY_FILTER_USE_KEY), fn($item) => ($item['group'] ?? Config::defaultGroup()) == $this->group));
    }

}
