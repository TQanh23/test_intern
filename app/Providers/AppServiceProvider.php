<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        // Mỗi user xin tối đa 10 link xem thử mỗi phút.
        RateLimiter::for('preview', fn (Request $request) => Limit::perMinute(10)->by($request->user()->id));
    }
}
