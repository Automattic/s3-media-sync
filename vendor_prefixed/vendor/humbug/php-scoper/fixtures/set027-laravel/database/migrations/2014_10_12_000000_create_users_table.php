<?php

namespace WPCOM_VIP;

use WPCOM_VIP\Illuminate\Support\Facades\Schema;
use WPCOM_VIP\Illuminate\Database\Schema\Blueprint;
use WPCOM_VIP\Illuminate\Database\Migrations\Migration;
class CreateUsersTable extends \WPCOM_VIP\Illuminate\Database\Migrations\Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \WPCOM_VIP\Illuminate\Support\Facades\Schema::create('users', function (\WPCOM_VIP\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
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
        \WPCOM_VIP\Illuminate\Support\Facades\Schema::dropIfExists('users');
    }
}
\class_alias('WPCOM_VIP\\CreateUsersTable', 'CreateUsersTable', \false);
