<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonSchemaToSystemSettingTable extends Migration
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
        if (Schema::hasTable('system_setting') && !Schema::hasColumn('system_setting', 'json_schema')) {
            Schema::table('system_setting', function (Blueprint $table) {
                $table->longText('json_schema')->nullable()->after('value')->comment('JSON Schema定义');
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
        if (Schema::hasColumn('system_setting', 'json_schema')) {
            Schema::table('system_setting', function (Blueprint $table) {
                $table->dropColumn('json_schema');
            });
        }
    }
}