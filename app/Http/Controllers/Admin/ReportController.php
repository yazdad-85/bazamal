<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Institutions;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $locked = $request->user()->isKoordinator();
        $institution = $locked ? $request->user()->institution : $request->query('lembaga', 'semua');

        $orders = Order::query()
            ->visibleTo($request->user())
            ->institutionFilter($institution)
            ->with('items')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.index', [
            'orders' => $orders,
            'institution' => $institution,
            'institutions' => Institutions::options(),
            'lockedInstitution' => $locked,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $locked = $request->user()->isKoordinator();
        $institution = $locked ? $request->user()->institution : $request->query('lembaga', 'semua');
        $filename = 'laporan-bazar-'.now()->format('Ymd-His').'.csv';

        $orders = Order::query()
            ->visibleTo($request->user())
            ->institutionFilter($institution)
            ->with('items')
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Kode',
                'Tanggal',
                'Nama',
                'Tipe',
                'Lembaga',
                'Kelas',
                'Metode Ambil',
                'Alamat',
                'Metode Bayar',
                'Status',
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
                    $order->buyer_type,
                    $order->institution,
                    $order->class_name,
                    $order->pickup_method,
                    $order->delivery_address,
                    $order->payment_method,
                    $order->status,
                    $order->subtotal,
                    $items,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
