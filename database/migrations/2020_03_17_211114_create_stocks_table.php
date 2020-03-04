<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('attribute')->nullable(); //color...等之類的屬性, 依據商品而有所改變
            $table->string('quantity');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('image_id')->nullable(); //有別於images表, 此圖片專門用來呈現此product的各個樣式
            $table->timestamps();

            $table->unique(['product_id', 'attribute']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
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
        Schema::table('stocks', function ($table) {

            $table->dropForeign('stocks_product_id_foreign');

        });

        Schema::dropIfExists('stocks');
    }
}
