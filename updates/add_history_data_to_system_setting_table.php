<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHistoryDataToSystemSettingTable extends Migration
{
    // 这里可以指定你的数据库连接
    // public function getConnection()
    // {
    //     return config('database.connection') ?: config('database.default');
    // }
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('system_setting') && !Schema::hasColumn('system_setting', 'history_data')) {
            Schema::table('system_setting', function (Blueprint $table) {
                $table->text('history_data')->nullable()->after('json_schema')->comment('历史版本数据');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('system_setting', 'history_data')) {
            Schema::table('system_setting', function (Blueprint $table) {
                $table->dropColumn('history_data');
            });
        }
    }
}