<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChampionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('champions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->string('toc');
            $table->string('he');
            $table->string('skillname');
            $table->string('img_skill_link');
            $table->string('passiveoractive');
            $table->longText('skilldescription');
            $table->longText('recommend_item');
            $table->string('mana');
            $table->longText('skilldetails');
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
        // Schema::dropIfExists('champions');
    }
}
