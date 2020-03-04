<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PermissionPermissionGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_permission_group', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('permission_group_id');
            $table->unsignedBigInteger('permission_id');

            $table->unique(['permission_group_id', 'permission_id'], 'ppg_permission_group_id_permission_id_unique');

            $table->foreign('permission_group_id', 'ppg_permission_group_id_foreign')
                ->references('id')
                ->on('permission_groups')
                ->onDelete('cascade');

            $table->foreign('permission_id', 'ppg_permission_id_foreign')
                ->references('id')
                ->on('permissions')
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
        Schema::table('permission_permission_group', function (Blueprint $table) {

            $table->dropForeign('ppg_permission_group_id_foreign');
            $table->dropForeign('ppg_permission_id_foreign');

        });

        Schema::dropIfExists('permission_permission_group');
    }
}
