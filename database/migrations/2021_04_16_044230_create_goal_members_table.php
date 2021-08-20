<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goal_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('team_id');
            $table->foreignId('goal_id');
            $table->integer('member_type')->default(0);
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
        Schema::dropIfExists('goal_members');
    }
}
