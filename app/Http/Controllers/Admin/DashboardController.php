<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Support\Institutions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $institution = $request->query('lembaga', 'semua');

        $ordersQuery = Order::query()->institutionFilter($institution);

        $stats = [
            'total_besar' => Product::where('bazar_type', 'besar')->where('is_active', true)->count(),
            'total_kecil' => Product::where('bazar_type', 'kecil')->where('is_active', true)->count(),
            'menunggu' => (clone $ordersQuery)->where('status', 'menunggu_verifikasi')->count(),
            'total_orders' => (clone $ordersQuery)->count(),
            'omzet' => (clone $ordersQuery)->where('status', '!=', 'batal')->sum('subtotal'),
        ];

        $recentOrders = (clone $ordersQuery)
            ->with('items')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'institution' => $institution,
            'institutions' => Institutions::options(),
        ]);
    }
}
