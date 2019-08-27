<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->bigIncrements('rule_id');
            $table->string('label');
            $table->string('alias')->index()->unique();
            $table->timestamps();
        });

        Schema::create('rule_users', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('rule_id')->unsigned();
            $table->timestamps();
            $table->foreign('user_id', 'fk_user_rule_users')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('rule_id', 'fk_rule_rule_users')->references('rule_id')->on('rules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rule_users');
        Schema::dropIfExists('rules');
    }
}
