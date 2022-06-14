<?php

namespace Shanjing\DcatAdminSetting;

use Dcat\Admin\Extend\Setting as Form;

class Setting extends Form
{
    // 返回表单弹窗标题
    public function title()
    {
        return $this->trans('setting.setting_title');
    }

    public function form()
    {
        $stores = array_keys(config('cache.stores'));
        $this->select('cache_store', '缓存驱动')->options(array_combine($stores, $stores))->required();
        $this->text('cache_key', '缓存键名')->required();
        $this->text('page_route', '页面路由')->help('不带 admin 前缀，就像 dcat 配置菜单一样');
    }
}
