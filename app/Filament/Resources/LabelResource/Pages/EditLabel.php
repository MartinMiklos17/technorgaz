<?php

namespace App\Filament\Resources\LabelResource\Pages;

use App\Filament\Resources\LabelResource;
use App\Models\Label;
use App\Filament\Pages\BaseEditRecord;

class EditLabel extends BaseEditRecord
{
    protected static string $resource = LabelResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // data -> payload vissza (legacy decode)
        $payload = [];
        if (!empty($data['data'])) {
            $json = base64_decode($data['data'], true);
            $arr = $json ? json_decode($json, true) : null;
            $payload = is_array($arr) ? $arr : [];
        }

        $data['payload'] = $payload;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $payload = $data['payload'] ?? [];

        $data['title'] = $payload['b1'] ?? null;
        $data['type_text'] = Label::TYPES[(int)($data['type'] ?? 1)] ?? null;
        $data['data'] = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        unset($data['payload']);

        return $data;
    }
}
