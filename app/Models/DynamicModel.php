<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DynamicModel extends Model {

    public function setDynamicAttributes($attributes = [])
    {
        foreach ($attributes as $key => $value) {

            if (is_array($value) && !empty($value)) {

                $model = new DynamicModel();

                $model->setDynamicAttributes($value);

                $this->setAttribute($key, $model);

                continue;

            }

            $this->setAttribute($key, $value);

            continue;

        }
    }

}
