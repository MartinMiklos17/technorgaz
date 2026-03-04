<?php

namespace App\Filament\Widgets;

use App\Models\CommissioningLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use App\Filament\Resources\CommissioningLogResource;

class CommissioningLogScannerWidget extends Widget
{
    protected static string $view = 'filament.widgets.scanner-widget';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = -2;

    public string $serialNumber = '';
    public ?Collection $results = null;
    public bool $searched = false;

    public function getHeading(): string
    {
        return 'Beüzemelési napló keresés';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-book-open';
    }

    public function getResourceUrl(): string
    {
        return CommissioningLogResource::getUrl('index');
    }

    public function search(): void
    {
        $query = trim($this->serialNumber);
        $this->searched = true;

        if ($query === '') {
            $this->results = collect();
            return;
        }

        $this->results = CommissioningLog::query()
            ->visibleTo(auth()->user())
            ->where('serial_number', 'like', "%{$query}%")
            ->with(['product', 'creator'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (CommissioningLog $log) => [
                'id' => $log->id,
                'serial_number' => $log->serial_number,
                'product_name' => $log->product?->name ?? '-',
                'customer_name' => $log->customer_name ?? '-',
                'created_at' => $log->created_at?->format('Y.m.d H:i'),
                'url' => CommissioningLogResource::getUrl('edit', ['record' => $log]),
            ]);
    }

    public function setScannedCode(string $code): void
    {
        $this->serialNumber = $code;
        $this->search();
    }

    public function clearSearch(): void
    {
        $this->serialNumber = '';
        $this->results = null;
        $this->searched = false;
    }
}
