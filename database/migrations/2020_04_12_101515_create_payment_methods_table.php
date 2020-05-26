<?php

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique(); //LinePay, PayPal 需跟 class的命名做搭配
            $table->unsignedInteger('status'); //開啓 or 停用
            $table->string('logo_url');
        });

        //insert LinePay
        DB::table('payment_methods')->insert([
            'name' => 'LinePay',
            'status'   => PaymentMethod::STATUS_ON,
            'logo_url' => 'images/payment-icon/LinePay.png'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
}
