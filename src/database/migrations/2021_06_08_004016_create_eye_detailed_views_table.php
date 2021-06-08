<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEyeDetailedViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eye_detailed_views', function (Blueprint $table) {
            $table->id();
            $table->string('page_id');
            $table->string('page_type');
            $table->unsignedBigInteger('user_count');
            $table->unsignedBigInteger('page_count');
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
        Schema::dropIfExists('eye_detailed_views');
    }
}
