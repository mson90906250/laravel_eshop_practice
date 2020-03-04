<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('brand_id');
            $table->string('name');
            $table->unsignedBigInteger('original_price'); //用於作爲原價 跟 stocks表的價格做比較
            $table->unsignedInteger('status')->default(Product::STATUS_ON);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['brand_id', 'name']);

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
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
        Schema::table('products', function ($table) {

            $table->dropForeign('products_brand_id_foreign');

        });

        Schema::dropIfExists('products');
    }
}
