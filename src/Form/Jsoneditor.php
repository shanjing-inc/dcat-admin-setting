<?php

namespace Shanjing\DcatAdminSetting\Form;

use Dcat\Admin\Form\Field;

class Jsoneditor extends Field
{
    protected $view = 'shanjing.dcat-admin-setting::form.jsoneditor';

    /**
     * Field constructor.
     *
     * @param  string|array  $column
     * @param  array  $arguments
     */
    public function __construct($column, $arguments = [])
    {
        parent::__construct(...func_get_args());
        $this->default('{}');
        $this->customFormat(function ($value) {
            return json_encode($value ?: []);
        })->saving(function ($value) {
            return json_decode($value ?: '', true);
        });
    }
}
