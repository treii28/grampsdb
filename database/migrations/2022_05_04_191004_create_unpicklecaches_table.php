<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnpicklecachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unpicklecaches', function (Blueprint $table) {
            $table->id();
            $table->string("dataType");
            $table->string("gramps_id");
            $table->string("hash");
            //$table->binary("raw");
            $table->binary("json");
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
        Schema::dropIfExists('unpicklecaches');
    }
}
