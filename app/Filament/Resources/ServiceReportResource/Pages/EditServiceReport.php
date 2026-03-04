<?php

namespace App\Filament\Resources\ServiceReportResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Storage;
use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\ServiceReportResource;

class EditServiceReport extends BaseEditRecord
{
    protected static string $resource = ServiceReportResource::class;
    protected function afterSave(): void
    {
        $report = $this->record;
        $disk   = Storage::disk('private');
        $paths  = (array) $report->photo_paths;

        if (! $paths) return;

        $targetDir = "service_reports/{$report->id}";
        if (! $disk->exists($targetDir)) {
            $disk->makeDirectory($targetDir);
        }

        $new = [];
        foreach ($paths as $p) {
            $src = str_replace('\\','/',$p);
            $dest = (str_starts_with($src, $targetDir))
                ? $src
                : "{$targetDir}/" . basename($src);

            if ($src !== $dest && $disk->exists($src)) {
                $disk->move($src, $dest);
            }
            $new[] = $dest;
        }

        $report->photo_paths = $new;
        $report->save();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    public function getHeading(): string
    {
        return 'Szervíznapló szerkesztése';
    }
    public function getBreadcrumb(): string
    {
        return 'Szervíznapló szerkesztése';
    }
}
