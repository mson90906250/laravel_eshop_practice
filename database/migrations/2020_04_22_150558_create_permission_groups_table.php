<?php

use App\Models\PermissionGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->boolean('has_all_permissions')->default(FALSE);
            $table->unsignedInteger('status')->default(PermissionGroup::STATUS_ON);
        });

        //insert superadmin group
        DB::table('permission_groups')->insert([
            'name' => 'superadmin',
            'has_all_permissions' => TRUE
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_groups');
    }
}
