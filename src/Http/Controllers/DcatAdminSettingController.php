<?php

namespace Shanjing\DcatAdminSetting\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Shanjing\DcatAdminSetting\SettingServiceProvider;
use Shanjing\DcatAdminSetting\Models\SystemSetting;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

            // 状态列
            $grid->column('status', '状态')->using(SystemSetting::$getStatusOptions)->label([
                SystemSetting::STATUS_ENABLED => 'success',
                SystemSetting::STATUS_DISABLED => 'danger',
            ]);

            // 添加历史版本展开列
            $grid->column('历史版本')
            ->if(function() {
                return !empty($this->getHistoryVersions());
            })
            ->display(function() {
                return '查看详情';
            })
            ->expand(function () {
                $historyVersions = $this->getHistoryVersions();

                if (empty($historyVersions)) {
                    return '<div class="alert alert-info">暂无历史版本</div>';
                }

                $historyVersions = array_reverse($historyVersions);
                $route = SettingServiceProvider::setting('page_route');
                $url = admin_url($route . '-restore');

                $html = '<div class="table-responsive">';
                $html .= '<table class="table table-striped table-bordered">';
                $html .= '<thead><tr><th>版本号</th><th>创建时间</th><th>数据预览</th><th>操作</th></tr></thead>';
                $html .= '<tbody>';

                foreach ($historyVersions as $version) {
                    $dataPreview = json_encode($version['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                    $html .= '<tr>';
                    $html .= '<td><span class="badge badge-primary">' . $version['version'] . '</span></td>';
                    $html .= '<td>' . $version['created_at'] . '</td>';
                    $html .= '<td><pre class="dump">' . htmlspecialchars($dataPreview) . '</pre></td>';
                    $html .= '<td>';
                    $html .= '<button type="button" class="btn btn-sm btn-success restore-version-btn" ';
                    $html .= 'data-id="' . $this->id . '" ';
                    $html .= 'data-version="' . $version['version'] . '">';
                    $html .= '恢复到此版本</button>';
                    $html .= '</td>';
                    $html .= '</tr>';
                }

                $html .= '</tbody></table></div>';

                // 添加 JavaScript 处理恢复逻辑，使用 Dcat 的确认弹框
                $html .= '<script>
                    $(document).on("click", ".restore-version-btn", function() {
                        var btn = $(this);
                        var id = btn.data("id");
                        var version = btn.data("version");

                        Dcat.confirm("确定要恢复到版本 " + version + " 吗？", "此操作将覆盖当前数据，请谨慎操作。", function() {
                            $.ajax({
                                url: "' . $url . '",
                                type: "POST",
                                data: {
                                    id: id,
                                    version: version,
                                    _token: "' . csrf_token() . '"
                                },
                                success: function(response) {
                                    if (response.status) {
                                        Dcat.success(response.message || "恢复成功");
                                        setTimeout(function() {
                                            location.reload();
                                        }, 1000);
                                    } else {
                                        Dcat.error(response.message || "恢复失败");
                                    }
                                },
                                error: function() {
                                    Dcat.error("恢复失败，请重试");
                                }
                            });
                        });
                    });
                </script>';

                return $html;
            });

            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if ($this->status == SystemSetting::STATUS_ENABLED) {
                    $actions->disableDelete();
                }
            });

            $grid->disableViewButton();
            $grid->disableBatchDelete();
        });
    }

    /**
     * 恢复到指定版本
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Request $request)
    {
        try {
            $id = $request->input('id');
            $version = $request->input('version');

            $setting = SystemSetting::findOrFail($id);
            $result = $setting->restoreToVersion($version);

            if ($result) {
                return response()->json([
                    'status' => true,
                    'message' => "成功恢复到版本 {$version}"
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => '恢复失败，版本不存在'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => '恢复失败：' . $e->getMessage()
            ]);
        }
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
            $form->disableDeleteButton();
            $form->display('id');
            $form->text('title', '标题')->required();
            $form->text('key', '键名')->required();

            // 状态选择
            $form->radio('status', '状态')
                ->options(SystemSetting::$getStatusOptions)
                ->default(SystemSetting::STATUS_ENABLED)
                ->required();
            // 获取当前记录
            $model = $form->model();
            if ($form->isEditing() && !empty($model->json_schema)) {
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

            if ($form->isDeleting()) {
                $records = $model->toArray();
                foreach ($records as $record) {
                    if ($record['status'] == SystemSetting::STATUS_ENABLED) {
                        throw new BadRequestHttpException('配置开启状态不允许删除');
                    }
                    if (now()->subDays(7)->isBefore($record['updated_at'])) {
                        throw new BadRequestHttpException('设置关闭状态 7 天内不允许删除');
                    }
                }
            }

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
