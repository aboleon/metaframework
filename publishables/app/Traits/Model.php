<?php

namespace App\Traits;

trait Model {
    public function unsetAllRelations(): static
    {
        foreach ($this->getRelations() as $relation => $value) {
            $this->unsetRelation($relation);
        }

        return $this;
    }
}
