<?php

namespace Shanjing\DcatAdminSetting\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Shanjing\DcatAdminSetting\SettingServiceProvider;

class SystemSetting extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'system_setting';

    public function __construct(array $attributes = [])
    {
        // $this->connection = config('database.connection') ?: config('database.default');

        parent::__construct($attributes);
    }

    protected $casts  = [
        'data' => 'array'
    ];

    /**
     * 使用模型的闭包删除缓存
     */
    protected static function booted()
    {
        // 数据更新后 - 删除缓存
        static::created(function () {
            static::flush();
        });
        // 数据更新后 - 删除缓存
        static::updated(function () {
            static::flush();
        });
        // 数据删除后 - 删除缓存
        static::deleted(function () {
            static::flush();
        });
    }

    /**
     * 删除缓存
     *
     * @return bool
     * @author 王衍生 <wys@shanjing-inc.com>
     */
    public static function flush()
    {
        $cacheKey = static::getCacheKey();
        static::getCacheStore()->forget($cacheKey);
    }

    /**
     * 获取配置
     *
     * @param [type] $name
     * @param [type] $key
     * @param [type] $default
     * @return mixed
     * @author 王衍生 <wys@shanjing-inc.com>
     */
    public static function get($name = null, $key = null, $default = null)
    {
        $cacheKey = static::getCacheKey();
        $cacheData = static::getCacheStore()->rememberForever($cacheKey, function () {
            return static::where('key', '!=', '')->pluck('value', 'key')->toArray();
        });

        if (is_null($name)) {
            return $cacheData ?? $default;
        }

        $value = array_get($cacheData, $name);

        if (is_null($key)) {
            return $value ?? $default;
        }

        return array_get($value, $key, $default);
    }

    /**
     * 获取缓存驱动
     *
     * @return \Illuminate\Contracts\Cache\Repository
     * @author 王衍生 <wys@shanjing-inc.com>
     */
    public static function getCacheStore()
    {
        $storeName = SettingServiceProvider::setting('cache_store');
        return Cache::store($storeName);
    }

    /**
     * 获取缓存键名
     *
     * @return string
     * @author 王衍生 <wys@shanjing-inc.com>
     */
    public static function getCacheKey()
    {
        return SettingServiceProvider::setting('cache_key');
    }
}
