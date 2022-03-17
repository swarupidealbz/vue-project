<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitCostOnUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->double('unit_cost')->nullable()->after('cost');
            $table->integer('job_units')->nullable()->after('unit_cost');
            $table->integer('monthly_goal')->nullable()->after('job_units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
            $table->dropColumn('job_units');
            $table->dropColumn('monthly_goal');
        });
    }
}
