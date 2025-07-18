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
        'value' => 'array',
        'json_schema' => 'array',
        'history_data' => 'array',
        'status' => 'integer',
    ];

    protected $fillable = ['title', 'key', 'value', 'json_schema', 'history_data', 'status'];

    // 状态常量
    const STATUS_ENABLED = 1;   // 开启
    const STATUS_DISABLED = 2;  // 关闭

    /**
     * 获取状态选项
     *
     * @return array
     */
    public static $getStatusOptions = [
        self::STATUS_ENABLED => '开启',
        self::STATUS_DISABLED => '关闭',
    ];

    /**
     * 使用模型的闭包删除缓存
     */
    protected static function booted()
    {
        // 数据更新前 - 保存历史版本
        static::updating(function ($model) {
            $model->saveHistoryVersion();
        });

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
     * 获取设置
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
            return static::where('key', '!=', '')->where('status', self::STATUS_ENABLED)->pluck('value', 'key')->toArray();
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

    /**
     * 保存历史版本
     */
    protected function saveHistoryVersion()
    {
        // 检查 value 字段是否发生变化
        if (!$this->isDirty('value')) {
            return;
        }

        $originalValue = $this->getOriginal('value');
        if ($originalValue === null) {
            return; // 新记录不需要保存历史版本
        }

        $historyData = $this->history_data ?? [];

        // 添加新的历史版本
        $newVersion = [
            'version' => 'v' . (count($historyData) + 1),
            'data' => $originalValue,
            'created_at' => now()->format('Y-m-d H:i:s')
        ];

        array_push($historyData, $newVersion);

        // 只保留最近的2个版本 (v1, v2)
        if (count($historyData) > 2) {
            // 移除最旧的版本，重新编号
            $historyData = array_slice($historyData, -2);
            // 重新编号为 v1, v2
            foreach ($historyData as $index => &$version) {
                $version['version'] = 'v' . ($index + 1);
            }
        }

        $this->history_data = $historyData;
    }

    /**
     * 获取历史版本列表
     *
     * @return array
     */
    public function getHistoryVersions()
    {
        return $this->history_data ?? [];
    }

    /**
     * 恢复到指定版本
     *
     * @param string $version
     * @return bool
     */
    public function restoreToVersion($version)
    {
        $historyData = $this->getHistoryVersions();

        foreach ($historyData as $history) {
            if ($history['version'] === $version) {
                $this->value = $history['data'];
                return $this->save();
            }
        }

        return false;
    }
}
