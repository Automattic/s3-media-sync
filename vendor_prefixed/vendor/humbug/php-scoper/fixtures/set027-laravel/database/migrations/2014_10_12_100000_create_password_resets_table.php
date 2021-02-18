<?php

namespace WPCOM_VIP;

use WPCOM_VIP\Illuminate\Support\Facades\Schema;
use WPCOM_VIP\Illuminate\Database\Schema\Blueprint;
use WPCOM_VIP\Illuminate\Database\Migrations\Migration;
class CreatePasswordResetsTable extends \WPCOM_VIP\Illuminate\Database\Migrations\Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \WPCOM_VIP\Illuminate\Support\Facades\Schema::create('password_resets', function (\WPCOM_VIP\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \WPCOM_VIP\Illuminate\Support\Facades\Schema::dropIfExists('password_resets');
    }
}
\class_alias('WPCOM_VIP\\CreatePasswordResetsTable', 'CreatePasswordResetsTable', \false);
