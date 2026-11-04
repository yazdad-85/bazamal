<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->visibleTo($request->user())
            ->with('items')
            ->latest()
            ->paginate(20);

        return view('admin.reports.index', [
            'orders' => $orders,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'laporan-bazar-'.now()->format('Ymd-His').'.csv';

        $orders = Order::query()
            ->visibleTo($request->user())
            ->with('items')
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Kode',
                'Tanggal',
                'Nama',
                'Pengiriman',
                'Wilayah',
                'Alamat',
                'Hari Pengiriman',
                'Metode Bayar',
                'Bukti Pembayaran',
                'Status',
                'Subtotal',
                'Ongkir',
                'Total',
                'Item',
            ]);

            foreach ($orders as $order) {
                $items = $order->items
                    ->map(fn ($i) => $i->product_name.' x'.$i->qty)
                    ->implode('; ');

                fputcsv($out, [
                    $order->order_code,
                    $order->created_at->format('Y-m-d H:i'),
                    $order->full_name,
                    $order->pickup_method_label,
                    $order->delivery_area,
                    $order->delivery_address,
                    $order->delivery_date?->toDateString(),
                    $order->payment_method,
                    $order->payment_proof ? route('admin.orders.proof', $order) : '',
                    $order->status,
                    $order->subtotal,
                    $order->delivery_fee,
                    $order->total,
                    $items,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
