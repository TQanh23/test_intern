<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dev và test throw ngay khi có N+1, nên toàn bộ test cũng kiểm tra luôn việc eager loading.
        // Production tắt để một chỗ bị sót chỉ làm trang chậm hơn, không thành lỗi 500.
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
