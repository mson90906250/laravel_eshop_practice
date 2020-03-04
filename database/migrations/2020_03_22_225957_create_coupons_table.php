<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('code')->unique(); //兌換用的代碼
            $table->unsignedInteger('status'); //狀態:可用,不可用
            $table->unsignedInteger('remain'); //可用的次數
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->unsignedInteger('value_type'); //折價的種類:數字,百分比
            $table->unsignedInteger('value');
            $table->unsignedInteger('required_value'); //需滿足的金額
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
