<?php

// NOTE: legacy template helper functions used inside eval() / template includes.

if (! function_exists('_s')) {
    /**
     * Legacy helper: safe string output (echo előtt).
     */
    function _s(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
        }

        return (string) $value;
    }
}

if (! function_exists('_n')) {
    /**
     * (Opcionális) Legacy helper: number formatting.
     */
    function _n(mixed $value, int $decimals = 0, string $decPoint = ',', string $thousandsSep = ' '): string
    {
        $v = is_numeric($value) ? (float) $value : 0.0;
        return number_format($v, $decimals, $decPoint, $thousandsSep);
    }
}
