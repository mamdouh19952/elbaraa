<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horses', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('breed_en')->nullable();
            $table->string('breed_ar')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['available', 'reserved', 'sold'])->default('available');
            $table->string('video_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('status');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horses');
    }
};
