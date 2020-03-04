<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_stock', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('stock_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['order_id', 'stock_id']);

            $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->onDelete('cascade');

            $table->foreign('stock_id')
                    ->references('id')
                    ->on('stocks')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_stock', function ($table) {

            $table->$table->dropForeign('order_stock_order_id_foreign');

            $table->$table->dropForeign('order_stock_stock_id_foreign');

        });

        Schema::dropIfExists('order_stock');
    }
}
