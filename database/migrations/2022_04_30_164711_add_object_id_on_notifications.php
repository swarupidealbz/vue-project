<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddObjectIdOnNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('object_from_type')->nullable()->after('details');
            $table->integer('object_from_id')->nullable()->after('object_from_type');
            $table->string('object_to_type')->nullable()->after('object_from_id');
            $table->integer('object_to_id')->nullable()->after('object_to_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('object_from_type');
            $table->dropColumn('object_from_id');
            $table->dropColumn('object_to_type');
            $table->dropColumn('object_to_id');
        });
    }
}
