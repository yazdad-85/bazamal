<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;

class WhatsAppLinkService
{
    public function build(Order $order, ?string $target = null): string
    {
        $number = match ($target) {
            'bendahara' => Setting::bendaharaWhatsApp(),
            'lembaga' => Setting::institutionWhatsApp($order->institution),
            default => Setting::whatsappForOrder($order),
        };

        if ($number === '') {
            $number = preg_replace('/\D+/', '', (string) config('bazar.admin_whatsapp', '6281234567890'));
        }

        $text = rawurlencode($this->message($order));

        return "https://wa.me/{$number}?text={$text}";
    }

    /**
     * @return array<int, array{label: string, url: string, target: string}>
     */
    public function linksForOrder(Order $order): array
    {
        $links = [];
        $primaryNumber = Setting::whatsappForOrder($order);
        $bendahara = Setting::bendaharaWhatsApp();

        if ($order->buyer_type === 'siswa' && $order->institution) {
            $lembagaNumber = Setting::institutionWhatsApp($order->institution);
            $links[] = [
                'label' => 'Kirim ke WA Panitia '.$order->institution,
                'url' => $this->build($order, 'lembaga'),
                'target' => 'lembaga',
                'number' => $lembagaNumber,
            ];

            if ($bendahara !== '' && $bendahara !== $lembagaNumber) {
                $links[] = [
                    'label' => 'Kirim ke WA Bendahara Inti',
                    'url' => $this->build($order, 'bendahara'),
                    'target' => 'bendahara',
                    'number' => $bendahara,
                ];
            }
        } else {
            $links[] = [
                'label' => 'Kirim ke WA Bendahara Inti',
                'url' => $this->build($order, 'bendahara'),
                'target' => 'bendahara',
                'number' => $primaryNumber ?: $bendahara,
            ];
        }

        return $links;
    }

    public function message(Order $order): string
    {
        $order->loadMissing('items');
        $site = Setting::siteName();

        $lines = [
            '*PESANAN '.$site.'*',
            'Kode: '.$order->order_code,
            'Nama: '.$order->full_name,
            'Tipe: '.($order->buyer_type === 'siswa' ? 'Siswa' : 'Umum'),
        ];

        if ($order->buyer_type === 'siswa') {
            $lines[] = 'Lembaga: '.$order->institution;
            $lines[] = 'Kelas: '.$order->class_name;
        }

        if ($order->phone) {
            $lines[] = 'HP: '.$order->phone;
        }

        $lines[] = 'Ambil: '.$order->pickup_method_label;

        if ($order->pickup_method === 'kirim_alamat' && $order->delivery_address) {
            $lines[] = 'Alamat: '.$order->delivery_address;
            $lines[] = 'Ongkir: Gratis, diantar tim panitia';
        }
        $lines[] = 'Bayar: '.$order->payment_method_label;
        $lines[] = '';
        $lines[] = '*Item:*';

        foreach ($order->items as $item) {
            $lines[] = sprintf(
                '- %s x%d = Rp %s',
                $item->product_name,
                $item->qty,
                number_format($item->line_total, 0, ',', '.')
            );
        }

        $lines[] = '';
        $lines[] = 'Total: '.$order->formatted_subtotal;
        $lines[] = 'Status: '.$order->status_label;

        return implode("\n", $lines);
    }
}
