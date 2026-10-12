<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Attributes\On;
use Livewire\Component;

class IncomingOrderBadge extends Component
{
    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    #[On('incoming-orders-changed')]
    public function refreshBadge(): void
    {
    }

    public function render()
    {
        $count = Order::query()
            ->visibleTo(auth()->user())
            ->where('status', 'menunggu_verifikasi')
            ->count();

        return view('livewire.admin.incoming-order-badge', [
            'count' => $count,
        ]);
    }
}
