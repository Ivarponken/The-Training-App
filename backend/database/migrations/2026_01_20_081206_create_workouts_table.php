<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('workouts', function (Blueprint $table) {
        $table->id();
        $table->dateTime('when');
        $table->string('activity', 100);
        $table->text('details')->nullable();
        $table->unsignedTinyInteger('borg_scale');
        $table->decimal('distance', 8, 2)->nullable()->unsigned();
        $table->unsignedInteger('duration');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
};
