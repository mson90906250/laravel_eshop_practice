<?php

use App\Models\Admin;
use App\Models\PermissionGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminPermissionGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_permission_group', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('permission_group_id');

            $table->unique(['admin_id', 'permission_group_id']);

            $table->foreign('admin_id', 'apg_admin_id_foreign')
                ->references('id')
                ->on('admins')
                ->onDelete('cascade');

            $table->foreign('permission_group_id', 'apg_permission_group_id_foreign')
                ->references('id')
                ->on('permission_groups')
                ->onDelete('cascade');

        });

        //insert superadmin

        DB::table('admin_permission_group')->insert([
            'admin_id' => Admin::SUPER_ADMIN_ID, //SuperAdmin
            'permission_group_id' => PermissionGroup::SUPER_ADMIN_GROUP_ID //superadmin
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_permission_group', function (Blueprint $table) {

            $table->dropForeign('apg_admin_id_foreign');
            $table->dropForeign('apg_permission_group_id_foreign');

        });

        Schema::dropIfExists('admin_permission_group');
    }
}
