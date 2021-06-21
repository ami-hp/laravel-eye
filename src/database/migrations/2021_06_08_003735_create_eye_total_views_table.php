<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEyeTotalViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('eye.tables.total'), function (Blueprint $table) {
            $table->id();
            $table->string('type');
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
        Schema::dropIfExists('eye_total_views');
    }
}
