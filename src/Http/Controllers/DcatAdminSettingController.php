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
            $grid->column('title', '标题');
            $grid->column('key', '键名');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->disableViewButton();
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
            $form->disableViewButton();
            $form->display('id');
            $form->text('title', '标题')->required();
            $form->text('key', '键名')->required();
            // 获取当前记录
            $model = $form->model();
            $isEdit = $model && $model->exists;

            $hasJsonSchema = $isEdit && !empty($model->json_schema);

            if ($isEdit && $hasJsonSchema) {
                // 编辑模式且有JSON Schema，使用JsonSchema组件
                $form->shanjingJsonSchema('combined_data', '配置数据')
                    ->help('基于JSON Schema的表单编辑器')
                    ->value(json_encode([
                            'schema' => $model->json_schema ?? [],
                            'data' => $model->value ?? [],
                        ]));
                $form->hidden('value')->customFormat(function ($value) {
                    return is_array($value) ? json_encode($value) : $value;
                });
                $form->hidden('json_schema')->customFormat(function ($value) {
                    return is_array($value) ? json_encode($value) : $value;
                });
                // 保存时分离数据
                $form->saving(function (Form $form) {
                    $combinedData = $form->combined_data;
                    if (is_string($combinedData)) {
                        $combinedData = json_decode($combinedData, true);
                    }

                    if (isset($combinedData['schema'])) {
                        $form->input('json_schema', $combinedData['schema']);
                    }
                    if (isset($combinedData['data'])) {
                        $form->input('value', $combinedData['data']);
                    }

                    // 移除临时字段
                    $form->deleteInput('combined_data');
                });
            } else {
                // 新增模式或编辑模式但无JSON Schema，使用普通JSON编辑器
                $form->shanjingJsoneditor('value', '键值')->attribute('style', 'margin-bottom:20px')
                    ->help('JSON格式的配置数据');

                $form->shanjingJsoneditor('json_schema', 'JSON Schema')->attribute('style', 'margin-bottom:20px')
                    ->help('非必填。可用 <a href="https://json.ophir.dev/" target="_blank">https://json.ophir.dev</a> 生成 json schema，为键值字段生成表单样式，方便编辑');
            }

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
