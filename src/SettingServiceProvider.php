<?php

namespace Shanjing\DcatAdminSetting;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Shanjing\DcatAdminSetting\Form\Jsoneditor;

class SettingServiceProvider extends ServiceProvider
{
    // 定义菜单
    protected $menu = [
        [
            'title' => '系统设置',
            'uri'   => 'system-setting',
            'icon'  => '', // 图标可以留空
        ],
    ];

	protected $js = [
        'jsoneditor@9.5.6/dist/jsoneditor.min.js',
    ];
	protected $css = [
		'jsoneditor@9.5.6/dist/jsoneditor.min.css',
	];

	public function register()
	{

	}

	public function init()
	{
		parent::init();

        Admin::asset()->alias('@jsoneditor2', [
            'js'  => [
                "vendor/dcat-admin-extensions/shanjing/dcat-admin-setting/jsoneditor@9.5.6/dist/jsoneditor.min.js",
            ],
            'css' => [
                'vendor/dcat-admin-extensions/shanjing/dcat-admin-setting/jsoneditor@9.5.6/dist/jsoneditor.min.css',
            ],
        ]);

		Form::extend('jsoneditor2', Jsoneditor::class);
	}

	public function settingForm()
	{
		return new Setting($this);
	}
}
