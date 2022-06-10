<?php

namespace Shanjing\DcatAdminSetting\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    public const CACHE_KEY = 'system_setting';

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
        Cache::store()->forget(static::CACHE_KEY);
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
        $cacheKey = static::CACHE_KEY;
        $cacheData = Cache::store()->rememberForever($cacheKey, function () {
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
}
