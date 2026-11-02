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
            'Pesanan baru masuk',
            $order->order_code.' · '.$order->full_name.' · '.$order->formatted_subtotal,
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

        $this->send(
            $subscriptions,
            'Status pesanan diperbarui',
            $order->order_code.' sekarang '.$order->status_label.'.',
            route('orders.show', ['orderCode' => $order->order_code]),
        );
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
