<?php

namespace App\Services;

use App\Contracts\PushSender;
use App\Models\Order;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function __construct(private PushSender $sender) {}

    public function notifyAdminsOfNewOrder(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            return;
        }

        $subscriptions = PushSubscription::query()
            ->where('enabled', true)
            ->whereNotNull('user_id')
            ->whereHas('user', function (Builder $query) use ($order): void {
                $query->where('role', User::ROLE_INTI);

                if ($order->buyer_type === 'siswa' && $order->institution) {
                    $query->orWhere(function (Builder $query) use ($order): void {
                        $query->where('role', User::ROLE_KOORDINATOR)
                            ->where('institution', $order->institution);
                    });
                }
            })
            ->get();

        if ($subscriptions->isEmpty()) {
            Log::warning('New order notification skipped: no admin device is registered.', [
                'order_code' => $order->order_code,
            ]);
        }

        $this->send(
            $subscriptions,
            'Pesanan baru dari '.$order->full_name,
            $order->formatted_total.' · '.$order->order_code,
            route('admin.orders.show', $order),
        );
    }

    public function notifyCustomerOfStatusChange(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            return;
        }

        $subscriptions = PushSubscription::query()
            ->where('enabled', true)
            ->whereNull('user_id')
            ->whereHas('device.orders', fn (Builder $query): Builder => $query->whereKey($order->getKey()))
            ->get();

        [$title, $message] = $this->statusNotificationText($order);

        $this->send(
            $subscriptions,
            $title,
            $message.' · '.$order->order_code,
            route('orders.show', ['orderCode' => $order->order_code]),
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function statusNotificationText(Order $order): array
    {
        return match ($order->status) {
            'menunggu_verifikasi' => ['Pembayaran sedang diperiksa', 'Panitia sedang memeriksa pembayaran Anda.'],
            'diproses' => ['Pesanan sedang diproses', 'Pembayaran sudah diterima dan pesanan sedang disiapkan.'],
            'siap_diantar' => ['Pesanan siap diantar', 'Pesanan Anda segera diantar oleh panitia.'],
            'selesai' => ['Pesanan selesai', 'Terima kasih sudah berbelanja sambil beramal.'],
            'batal' => ['Pesanan dibatalkan', 'Hubungi panitia jika ada pertanyaan.'],
            default => ['Status pesanan diperbarui', 'Status pesanan Anda sekarang '.$order->status_label.'.'],
        };
    }

    /**
     * @param  Collection<int, PushSubscription>  $subscriptions
     */
    private function send(Collection $subscriptions, string $title, string $body, string $url): void
    {
        $tokens = $subscriptions
            ->pluck('token')
            ->filter(fn (mixed $token): bool => is_string($token) && $token !== '')
            ->unique()
            ->values()
            ->all();

        $invalidTokens = $this->sender->send($tokens, $title, $body, $url);

        if ($invalidTokens !== []) {
            PushSubscription::query()
                ->whereIn('token_hash', array_map(
                    fn (string $token): string => PushSubscription::hashToken($token),
                    $invalidTokens,
                ))
                ->delete();
        }
    }
}
