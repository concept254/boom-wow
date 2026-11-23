<?php

namespace App\Support;

/** Turns an admin-entered link into a safe href: http(s), mailto, tel, #anchor, or a site path. Anything else becomes "#". */
class Link
{
    public static function href(?string $value): string
    {
        $v = trim((string) $value);

        if ($v === '') {
            return '#';
        }
        if (preg_match('#^(https?://|mailto:|tel:)#i', $v) || str_starts_with($v, '#')) {
            return $v;
        }
        if (str_starts_with($v, '/') && ! str_starts_with($v, '//')) {
            return url($v);
        }

        return '#';
    }
}
