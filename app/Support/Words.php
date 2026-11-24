<?php

namespace App\Support;

class Words
{
    private const MAP = [1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve'];

    /** 6 => "Six" (digits above twelve), so headings stay correct as the group adds businesses. */
    public static function number(int $n): string
    {
        return self::MAP[$n] ?? (string) $n;
    }
}
