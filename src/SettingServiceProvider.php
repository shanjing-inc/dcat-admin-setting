<?php

namespace Shanjing\DcatAdminSetting;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Illuminate\Support\Facades\Route;
use Shanjing\DcatAdminSetting\Form\Jsoneditor;

class SettingServiceProvider extends ServiceProvider
{
    // 定义菜单
    protected $menu = [];

	protected $js = [];
	protected $css = [];

	public function register()
	{

	}

	public function init()
	{
		parent::init();

        Admin::asset()->alias('@shanjingJsoneditor', [
            'js'  => [
                "vendor/dcat-admin-extensions/shanjing/dcat-admin-setting/jsoneditor@9.5.6/dist/jsoneditor.min.js",
            ],
            'css' => [
                'vendor/dcat-admin-extensions/shanjing/dcat-admin-setting/jsoneditor@9.5.6/dist/jsoneditor.min.css',
            ],
        ]);

		Form::extend('shanjingJsoneditor', Jsoneditor::class);
	}

	public function settingForm()
	{
		return new Setting($this);
	}
}
