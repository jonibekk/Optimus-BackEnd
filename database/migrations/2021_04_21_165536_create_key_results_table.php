<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeyResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('goal_id');
            $table->foreignId('unit');
            $table->string('name');
            $table->integer('currency_type')->default(-1);
            $table->float('target_value', 15, 2);
            $table->float('start_value', 15, 2)->default(0);
            $table->float('current_value', 15, 2)->default(0);
            $table->float('progress')->default(0);
            $table->boolean('completed')->default(false);
            $table->boolean('del_flg')->default(false);
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('key_results');
    }
}
