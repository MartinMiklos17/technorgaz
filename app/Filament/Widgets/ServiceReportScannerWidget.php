<?php

namespace App\Filament\Widgets;

use App\Models\ServiceReport;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use App\Filament\Resources\ServiceReportResource;

class ServiceReportScannerWidget extends Widget
{
    protected static string $view = 'filament.widgets.scanner-widget';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = -1;

    public string $serialNumber = '';
    public ?Collection $results = null;
    public bool $searched = false;

    public function getHeading(): string
    {
        return 'Szervíz napló keresés';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public function getResourceUrl(): string
    {
        return ServiceReportResource::getUrl('index');
    }

    public function search(): void
    {
        $query = trim($this->serialNumber);
        $this->searched = true;

        if ($query === '') {
            $this->results = collect();
            return;
        }

        $this->results = ServiceReport::query()
            ->visibleTo(auth()->user())
            ->where('serial_number', 'like', "%{$query}%")
            ->with(['product', 'creator'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (ServiceReport $report) => [
                'id' => $report->id,
                'serial_number' => $report->serial_number,
                'product_name' => $report->product?->name ?? '-',
                'customer_name' => $report->customer_name ?? '-',
                'created_at' => $report->created_at?->format('Y.m.d H:i'),
                'url' => ServiceReportResource::getUrl('edit', ['record' => $report]),
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
