<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Route model binding for warehouse-locations
        Route::bind('warehouse_location', function ($value) {
            return \App\Models\Warehouse::findOrFail($value);
        });

        // Sales: resolve by receipt_number (group header in sale_items)
        Route::bind('sale', function ($value) {
            $item = \App\Models\SaleItem::where('receipt_number', $value)->first();
            if (!$item) {
                $item = \App\Models\SaleItem::find($value);
            }
            if (!$item) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Sale not found.');
            }
            return $item;
        });

        // Stok benih per-baris = certification_reports (bukan tabel inventory_type_seeds yang sudah dihapus).
        Route::bind('seed', function ($value) {
            return \App\Models\CertificationReport::where('certification_report_id', $value)->firstOrFail();
        });

        Route::bind('booking', function ($value) {
            return \App\Models\BookingGeowisata::findOrFail($value);
        });
        Route::bind('pendaftaran', function ($value) {
            return \App\Models\PendaftaranMagang::findOrFail($value);
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
