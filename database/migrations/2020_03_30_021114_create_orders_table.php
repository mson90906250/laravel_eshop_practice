<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_number');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('total');
            $table->unsignedInteger('shipping_fee'); //沒有用0表示
            $table->unsignedInteger('coupon_discount'); //沒有用0表示
            $table->unsignedInteger('order_status');
            $table->unsignedInteger('payment_status');
            $table->string('city');
            $table->string('district');
            $table->string('address');
            $table->unsignedInteger('payment_method');
            $table->text('data')->nullable(); //用於儲存第三方支付的訊息 json格式
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'order_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
