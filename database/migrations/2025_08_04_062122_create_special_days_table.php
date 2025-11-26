<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('special_days', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('type')->default('holiday'); // or 'no_office'
            $table->string('title'); // E.g. "Christmas", "No Office"
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_days');
    }
};
