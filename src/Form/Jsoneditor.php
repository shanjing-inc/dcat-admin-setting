<?php

namespace Shanjing\DcatAdminSetting\Form;

use Dcat\Admin\Form\Field;

class Jsoneditor extends Field
{
    protected $view = 'shanjing.dcat-admin-setting::form.jsoneditor';

    public function render()
    {
        $json = old($this->column, $this->value());
        if (empty($json)) {
            $json = '{}';
        }
        if (!is_string($json)) {
            $json = json_encode($json);
        } else {
            $json = json_encode(json_decode($json));   //兼容json里有类似</p>格式，首次初始化显示会丢失的问题
        }
        $this->value = $json;
        return parent::render();
    }
}
