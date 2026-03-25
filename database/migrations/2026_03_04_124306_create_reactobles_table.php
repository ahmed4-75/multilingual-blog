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
        Schema::create('reactobles', function (Blueprint $table) {
            $table->foreignId("react_id")->constrained("reactos")->cascadeOnDelete();
            $table->morphs('reactoble');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['react_id', 'reactoble_id', 'reactoble_type','user_id']);
            $table->unique(['reactoble_id', 'reactoble_type', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactobles');
    }
};
