<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.orders.index');
    }

    public function show(Order $order): View
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', Order::STATUSES)],
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('success', 'Status pesanan diperbarui.');
    }

    public function proof(Order $order): BinaryFileResponse
    {
        $relative = $order->payment_proof;
        abort_unless(is_string($relative) && $relative !== '' && ! str_contains($relative, '..'), 404);

        $local = Storage::disk('local');
        $public = Storage::disk('public');

        if (! $local->exists($relative) && $public->exists($relative)) {
            $local->put($relative, $public->get($relative));
            $public->delete($relative);
        }

        abort_unless($local->exists($relative), 404);

        return response()->file($local->path($relative), [
            'Content-Type' => $local->mimeType($relative) ?: 'image/jpeg',
            'Content-Disposition' => 'inline; filename="bukti-'.$order->order_code.'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
