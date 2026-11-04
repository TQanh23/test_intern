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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // Lọc theo level ở trang danh sách.
            $table->unsignedTinyInteger('level')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            // Mục lục lấy unit theo gói rồi sắp theo position.
            $table->index(['package_id', 'position']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['pdf', 'audio']);
            // Đường dẫn trong disk local (storage/app/private), không bao giờ trả ra API.
            $table->string('file_path');
            // Chỉ có ở PDF.
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->unsignedSmallInteger('preview_pages')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('units');
        Schema::dropIfExists('packages');
    }
};
