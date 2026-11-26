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
        Schema::create('bvn_lookup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->text('last_name');
            $table->text('bvn');
            $table->string('bvn_hash');
            $table->text('raw_response');
            $table->text('raw_request');
             $table->string('provider');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bvn_lookup_logs');
    }
};