<?php

namespace Shanjing\DcatAdminSetting;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;

class DcatAdminSettingServiceProvider extends ServiceProvider
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
        'js/index.js',
    ];
	protected $css = [
		'css/index.css',
	];

	public function register()
	{
		//
	}

	public function init()
	{
		parent::init();

		//

	}

	public function settingForm()
	{
		return new Setting($this);
	}
}
