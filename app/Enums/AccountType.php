<?php

namespace App\Enums;

enum AccountType: string
{
    case ServicePartner = 'service_partner';
    case Handover = 'handover';
    case Wholesale = 'wholesale';
    case Retail = 'retail';
    case Service = 'service';
    case Consumer = 'consumer';
    public function label(): string
    {
        return match ($this) {
            self::ServicePartner => 'Szervizpartner',
            self::Handover => 'Átadó partner',
            self::Wholesale => 'Nagykereskedő',
            self::Retail => 'Kiskereskedő',
            self::Service => 'Szerviz',
            self::Consumer => 'Fogyasztó',
        };
    }
    public static function options(): array
    {
        return collect(self::cases())
        ->mapWithKeys(fn($case) => [$case->value => $case->label()])
        ->toArray();
    }
        public static function casesAsLabels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
