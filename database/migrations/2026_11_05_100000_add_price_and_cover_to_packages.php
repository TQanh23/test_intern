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
        Schema::table('packages', function (Blueprint $table) {
            // Giá tính bằng VND, số nguyên vì VND không có phần lẻ.
            $table->unsignedInteger('price')->default(0)->after('level');
            // Đường dẫn tương đối trong public/, ví dụ images/packages/level-1.svg.
            $table->string('cover_image')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['price', 'cover_image']);
        });
    }
};
