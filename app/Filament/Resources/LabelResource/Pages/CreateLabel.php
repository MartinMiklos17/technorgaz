<?php

namespace App\Filament\Resources\LabelResource\Pages;

use App\Filament\Resources\LabelResource;
use App\Models\Label;
use Filament\Resources\Pages\CreateRecord;

class CreateLabel extends CreateRecord
{
    protected static string $resource = LabelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $payload = $data['payload'] ?? [];

        // Legacy: title = b1
        $data['title'] = $payload['b1'] ?? null;

        // Legacy: type_text
        $data['type_text'] = Label::TYPES[(int)($data['type'] ?? 1)] ?? null;

        // Legacy: data base64(json)
        $data['data'] = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        unset($data['payload']);

        // Legacy: date default
        $data['date'] = $data['date'] ?? now();

        return $data;
    }
}
