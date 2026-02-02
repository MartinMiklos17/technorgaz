<?php

use App\Services\Label\LabelRenderService;

/**
 * Legacy template-ek ezt hívják: clabel::calc_class(...)
 * Fontos: NINCS namespace.
 */
class clabel
{
    public static function calc_class($d, $number = 0, $type = 1)
    {
        /** @var LabelRenderService $svc */
        $svc = app(LabelRenderService::class);
        return $svc->calcClass($d, (int) $number, (int) $type);
    }
}
