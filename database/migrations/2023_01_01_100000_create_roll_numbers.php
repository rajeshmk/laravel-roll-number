<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roll_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('model_name', 250)->nullable();
            $table->string('prefix', 7)->nullable();
            $table->timestamps();
        });

        Schema::create('roll_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('roll_types');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('next_number');
            $table->unsignedBigInteger('rollover_limit')->nullable();
            $table->timestamps();

            $table->uniqid([
                'type_id',
                'model_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roll_numbers');
        Schema::dropIfExists('roll_types');
    }
};
