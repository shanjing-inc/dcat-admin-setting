<?php

namespace Shanjing\DcatAdminSetting;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Illuminate\Support\Facades\Route;
use Shanjing\DcatAdminSetting\Form\Jsoneditor;
use Shanjing\DcatAdminSetting\Form\JsonSchema;

class SettingServiceProvider extends ServiceProvider
{
    // 定义菜单
    protected $menu = [];

	protected $js = [
	    'js/jsonschema.min.js'
    ];
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
        Admin::asset()->alias('@shanjingJsonSchema', [
            'js'  => [
                "vendor/dcat-admin-extensions/shanjing/dcat-admin-setting/js/jsonschema.min.js",
            ]
        ]);

		Form::extend('shanjingJsoneditor', Jsoneditor::class);
		Form::extend('shanjingJsonSchema', JsonSchema::class);
	}

	public function settingForm()
	{
		return new Setting($this);
	}
}
