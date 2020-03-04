<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brand_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('category_id');

            $table->unique(['brand_id', 'category_id']);

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
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
        Schema::table('brand_category', function ($table) {

            $table->dropForeign('brand_category_brand_id_foreign');

            $table->dropForeign('brand_category_category_id_foreign');

        });

        Schema::dropIfExists('brand_category');
    }
}
