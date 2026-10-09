<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Support\Institutions;
use Livewire\Component;
use Livewire\WithPagination;

class OrderTable extends Component
{
    use WithPagination;

    public string $institution = 'semua';

    public string $bazar = 'semua';

    public string $status = 'semua';

    public string $search = '';

    protected $queryString = [
        'institution' => ['except' => 'semua'],
        'bazar' => ['except' => 'semua'],
        'status' => ['except' => 'semua'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingInstitution(): void
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

    public function setInstitution(string $value): void
    {
        $this->institution = $value;
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

        Order::whereKey($orderId)->update(['status' => $status]);
    }

    public function render()
    {
        $query = Order::query()
            ->with('items')
            ->institutionFilter($this->institution)
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
            'institutions' => Institutions::options(),
            'statuses' => Order::STATUSES,
        ]);
    }
}
