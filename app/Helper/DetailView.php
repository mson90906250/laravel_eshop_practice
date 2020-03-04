<?php

namespace App\Helper;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class DetailView {

    private static $tableStart, $trStart, $thStart, $tdStart;

    public static function get(Model $model,array $data)
    {
        // $data = [
        //     'table'     => [
        //         'class' => 'test-table',
        //         'style' => [
        //             'width' => '100px',
        //         ],
        //     ],
        //     'tr'        => [],
        //     'th'        => [],
        //     'td'        => [],
        //     'columns'   => [
        //         'column-name',
        //         [
        //             'attribute'     => 'username',
        //             'label'         => '用戶名稱',
        //             'value'         => '',
        //             'visibility'    => TRUE,
        //             'options'       => [
        //                 'class'     => 'btn btn-primary',
        //                 'style' => [
        //                     'width' => '10px',
        //                     'margin' => '10 10 10 10',
        //                 ]
        //             ]
        //         ]
        //     ],
        // ];

        if (!isset($data['columns'])) {

            throw new \ErrorException('columns必須設置');

        }

        static::$tableStart = static::setTable($data);

        static::$trStart =  static::setTr($data);

        static::$thStart = static::setTh($data);

        static::$tdStart = static::setTd($data);

        $content = '';

        foreach ($data['columns'] as $column) {

            $content = sprintf('%s%s', $content, static::setColumn($model, $column));

        }

        return sprintf('%s%s</table>', static::$tableStart, $content);
    }

    private static function setTable($data)
    {
        if (!isset($data['table'])) {

            return '<table>';

        }

        $start = '<table';

        foreach ($data['table'] as $option => $value) {

            switch ($option) {

                case 'style':

                    $optionStr = '';

                    foreach ($value as $property => $value) {

                        $optionStr = sprintf('%s%s:%s; ', $optionStr, $property, $value);

                    }

                    $start = sprintf('%s style="%s"', $start, $optionStr);

                    break;

                default:

                    $optionStr = sprintf('%s="%s"', $option, $value);

                    $start = sprintf('%s %s', $start, $optionStr);

                    break;

            }

        }

        return sprintf('%s>', $start);
    }

    private static function setTr($data)
    {
        if (!isset($data['tr'])) {

            return '<tr>';

        }

        // TODO
    }

    private static function setTh($data)
    {
        if (!isset($data['th'])) {

            return '<th>';

        }

        // TODO
    }

    private static function setTd($data)
    {
        if (!isset($data['td'])) {

            return '<td>';

        }

        // TODO
    }

    private static function setColumn(Model $model, $columnOptions)
    {
        $contentTemplate = '%s%s%s</th>%s%s</td></tr>';

        if (!is_array($columnOptions)) {

            return sprintf($contentTemplate,
                            static::$trStart,
                            static::$thStart,
                            $model->getAttributeLabelsForShow()[$columnOptions] ?? $columnOptions,
                            static::$tdStart,
                            $model->$columnOptions);

        }

        $attributeValue = '';

        //是否顯示
        if (isset($columnOptions['visibility']) && !$columnOptions['visibility']) {

            return '';

        }

        //attribute的值
        $attribute = $columnOptions['attribute'];

        if (isset($columnOptions['value'])) {

            if (is_callable($columnOptions['value'])) {

                $attributeValue = $columnOptions['value']($columnOptions['attribute'], $model->$attribute, $model);

            } else {

                $attributeValue = $columnOptions['value'];

            }

        } else {

            $attributeValue = $model->$attribute;

        }

        $label = $columnOptions['label'] ?? $model->getAttributeLabelsForShow()[$attribute] ?? $attribute;

        //特定的td style
        if (isset($columnOptions['options'])) {

            $tdStart = '<td';

            foreach ($columnOptions['options'] as $option => $value) {

                switch ($option) {

                    case 'style':

                        $optionStr = '';

                        foreach ($value as $property => $value) {

                            $optionStr = sprintf('%s%s:%s; ', $optionStr, $property, $value);

                        }

                        $tdStart = sprintf('%s style="%s"', $tdStart, $optionStr);

                        break;

                    default:

                        $optionStr = sprintf('%s="%s"', $option, $value);

                        $tdStart = sprintf('%s %s', $tdStart, $optionStr);

                        break;

                }

            }

            $tdStart = sprintf('%s>', $tdStart);

        }

        return sprintf($contentTemplate,
                            static::$trStart,
                            static::$thStart,
                            $label,
                            $tdStart ?? static::$tdStart,
                            $attributeValue);


    }

}

