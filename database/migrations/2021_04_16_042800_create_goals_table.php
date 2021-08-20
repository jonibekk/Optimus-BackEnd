<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('team_id');
            $table->float('progress')->default(0);
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->string('color');
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
        Schema::dropIfExists('goals');
    }
}
