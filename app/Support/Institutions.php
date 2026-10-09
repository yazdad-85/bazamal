<?php

namespace App\Support;

class Institutions
{
    public const ALL = ['MTs', 'SMP', 'MA', 'SMA', 'SMK'];

    public static function options(): array
    {
        return self::ALL;
    }
}
