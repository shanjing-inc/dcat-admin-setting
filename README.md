### 这是一个 Dcat Admin 系统设置管理插件

### 环境
- dcat/laravel-admin ~2.0

### 安装

首先安装 dcat-admin [参考文档](https://learnku.com/docs/dcat-admin/2.x/install/8081)，

再安装本包 composer require shanjing/dcat-admin-setting，[参考文档](https://learnku.com/docs/dcat-admin/2.x/extended-basic-usage/9691#b7b0ca)，

安装完成后，打开链接 http://domain/admin/auth/extensions 访问 dcat 扩展列表，

点击 更新至xxx版本 按钮，

再点解 设置 按钮，进行配置相关信息，

最后再点击 启用 按钮之后方可正常使用。

### 使用

查看配置列表: https://domain/admin/system-setting

新增配置: https://domain/admin/system-setting/create

获取配置: SystemSetting::get($key);