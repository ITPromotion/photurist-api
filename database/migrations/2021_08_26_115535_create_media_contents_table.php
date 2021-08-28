<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MediaContentType;

class CreateMediaContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_contents', function (Blueprint $table) {
            $table->id();
            $table->enum('media_content_type', MediaContentType::values())->nullable();
            $table->unsignedBigInteger('postcard_id')->nullable();
            $table->foreign('postcard_id')->references('id')->on('postcards')->onDelete('cascade');
            $table->softDeletes();
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
        Schema::dropIfExists('media_contents');
    }
}
