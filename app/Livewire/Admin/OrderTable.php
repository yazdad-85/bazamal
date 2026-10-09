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
        if (auth()->user()?->isKoordinator()) {
            return;
        }

        $this->institution = $value;
        $this->resetPage();
    }

    public function mount(): void
    {
        if (auth()->user()?->isKoordinator()) {
            $this->institution = (string) auth()->user()->institution;
        }
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
    }

    public function render()
    {
        $user = auth()->user();
        if ($user->isKoordinator()) {
            $this->institution = (string) $user->institution;
        }

        $query = Order::query()
            ->with('items')
            ->visibleTo($user)
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
            'lockedInstitution' => $user->isKoordinator(),
        ]);
    }
}
