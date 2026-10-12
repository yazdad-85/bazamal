<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderTable extends Component
{
    use WithPagination;

    public string $bazar = 'semua';

    public string $status = 'semua';

    public string $search = '';

    protected $queryString = [
        'bazar' => ['except' => 'semua'],
        'status' => ['except' => 'semua'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBazar(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function updateStatus(int $orderId, string $status): void
    {
        abort_unless(auth()->check(), 403);

        if (! in_array($status, Order::STATUSES, true)) {
            return;
        }

        $order = Order::query()->visibleTo(auth()->user())->whereKey($orderId)->first();

        if (! $order) {
            return;
        }

        $order->update(['status' => $status]);
        $this->dispatch('incoming-orders-changed');
    }

    public function render()
    {
        $user = auth()->user();

        $query = Order::query()
            ->with('items')
            ->visibleTo($user)
            ->latest();

        if ($this->status !== 'semua') {
            $query->where('status', $this->status);
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('order_code', 'like', '%'.$this->search.'%')
                    ->orWhere('full_name', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->bazar !== 'semua') {
            $query->whereHas('items.product', function ($q) {
                $q->where('bazar_type', $this->bazar);
            });
        }

        return view('livewire.admin.order-table', [
            'orders' => $query->paginate(15),
            'statuses' => Order::STATUSES,
        ]);
    }
}
