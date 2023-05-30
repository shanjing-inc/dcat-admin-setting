## 这是一个 Dcat Admin 系统设置管理插件

## 环境
- dcat/laravel-admin ~2.0

## 安装

首先安装 dcat-admin [参考文档](https://learnku.com/docs/dcat-admin/2.x/install/8081)，

再安装本包 composer require shanjing/dcat-admin-setting，[参考文档](https://learnku.com/docs/dcat-admin/2.x/extended-basic-usage/9691#b7b0ca)，

安装完成后，打开链接 http://domain/admin/auth/extensions 访问 dcat 扩展列表，

点击 更新至xxx版本 按钮，

再点解 设置 按钮，进行设置相关信息，

目前有 3 个设置项
cache_store：缓存驱动（列表选择）
cache_key：缓存键名（起一个唯一缓存 key 就可以，）
page_route：后台编辑缓存的页面路由

最后再点击 启用 按钮之后方可正常使用。

## 使用

### 设置菜单
打开 系统管理->菜单 设置菜单。

- 标题：随意，一般为 系统设置；

- 路径：page_route 的值；

- 其他项：无特殊要有，就像设置普通菜单一样即可；

最终菜单 url 为：https://domain/admin/{page_route}

### 新增设置
点击上一步设置的菜单打开列表页点击新增按钮；
- 标题：随意，只为后台便于理解说明
- 键名：用来获取数据的键名，保证唯一性，比如 ```site_info```
- 键值：json 格式，比如 ```{
  "name": "我的网站",
  "keyword": "技术博客，PHP 博客",
}```

### 获取设置

获取设置
```php
use Shanjing\DcatAdminSetting\Models\SystemSetting

// 比如获取站点名称
$key = 'site_info'; // 设置数据的键名
$name = 'name'; // 设置数据的 json 格式的 key
$default = '我的网站';
$siteName = SystemSetting::get($key, $name, $default);
print($siteName); // 输出：我的网站

// 也可以获取站点 key 下的所有设置
$key = 'site_info'; // 设置数据的键名
$siteInfo = SystemSetting::get($key);
print($siteInfo); // 输出：[ "name" => "我的网站", "keyword" => "技术博客，PHP 博客"]
print($siteInfo['name']); // 输出：我的网站
```