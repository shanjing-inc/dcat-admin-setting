<?php

namespace Shanjing\DcatAdminSetting\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Routing\Controller;
use Shanjing\DcatAdminSetting\SettingServiceProvider;
use Shanjing\DcatAdminSetting\Models\SystemSetting;

class DcatAdminSettingController extends AdminController
{
    protected function title()
    {
        return $this->title ?: SettingServiceProvider::trans('setting.title');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemSetting(), function (Grid $grid) {
            $grid->model()->orderByDesc('id');
            $grid->column('id')->sortable();
            $grid->column('key', '键名');
            $grid->column('value', '键值');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SystemSetting(), function (Form $form) {
            $form->display('id');
            $form->text('key', '键名');
            $form->jsoneditor2('value', '键值');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}