<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dictionnary_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dictionnary_id')->constrained('dictionnaries')->onDelete('cascade');
            $table->unsignedInteger('parent')->nullable()->index();
            $table->unsignedInteger('position')->default(0)->index();
            $table->json('name');
            $table->longText('custom')->nullable();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dictionnary_entries');
    }
};
