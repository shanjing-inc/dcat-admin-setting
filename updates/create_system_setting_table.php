<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemSettingTable extends Migration
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
        if (! Schema::hasTable('system_setting')) {
            Schema::create('system_setting', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100)->comment('标题');
                $table->string('key', 100)->unique()->comment('键名');
                $table->longText('value')->comment('键值');
                $table->timestamps();
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
        Schema::dropIfExists('system_setting');
    }
}
