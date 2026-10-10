<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\Institutions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $locked = $request->user()->isKoordinator();
        $institution = $locked ? $request->user()->institution : $request->query('lembaga', 'semua');

        $ordersQuery = Order::query()
            ->visibleTo($request->user())
            ->institutionFilter($institution);

        $countedOrders = (clone $ordersQuery)->where('status', '!=', 'batal');

        $stats = [
            'total_menu' => $this->categoryTotal($countedOrders, Product::TYPE_MENU),
            'total_infak' => $this->categoryTotal($countedOrders, Product::TYPE_INFAK),
            'menu_products' => Product::where('bazar_type', Product::TYPE_MENU)->where('is_active', true)->count(),
            'infak_products' => Product::where('bazar_type', Product::TYPE_INFAK)->where('is_active', true)->count(),
            'menunggu' => (clone $ordersQuery)->where('status', 'menunggu_verifikasi')->count(),
            'total_orders' => (clone $ordersQuery)->count(),
            'omzet' => (clone $countedOrders)->sum('subtotal'),
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
            'lockedInstitution' => $locked,
        ]);
    }

    private function categoryTotal($orders, string $type): int
    {
        return (int) OrderItem::query()
            ->whereIn('order_id', (clone $orders)->select('orders.id'))
            ->whereHas('product', fn ($query) => $query->where('bazar_type', $type))
            ->sum('line_total');
    }
}
