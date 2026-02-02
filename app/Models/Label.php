<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $table = 'label';

    public $timestamps = false; // legacy: date mező van, nem created_at/updated_at

    protected $fillable = [
        'status',
        'date',
        'data',
        'title',
        'type',
        'type_text',
    ];

    protected $casts = [
        'date' => 'datetime',
        'status' => 'integer',
        'type' => 'integer',
    ];

    public const TYPES = [
        1 => 'Helyiségfűtő',
        2 => 'Egyedi helyiségfűtő',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', 100);
    }

    public function getDecodedDataAttribute(): array
    {
        if (empty($this->data)) {
            return [];
        }

        $json = base64_decode($this->data, true);
        if ($json === false) {
            return [];
        }

        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    public function setDecodedData(array $payload): void
    {
        // Legacy kompatibilis: base64(json)
        $this->data = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    public function syncTypeText(): void
    {
        $this->type_text = self::TYPES[$this->type] ?? null;
    }
}
