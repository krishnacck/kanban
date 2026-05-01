<?php

if (!function_exists('countryFlag')) {
    /**
     * Convert a 2-letter ISO country code to its emoji flag.
     * Falls back to a globe emoji for unknown/empty codes.
     */
    function countryFlag(?string $code): string
    {
        if (!$code || strlen($code) !== 2) {
            return '🌍';
        }
        $code = strtoupper($code);
        $flag = '';
        foreach (str_split($code) as $char) {
            $flag .= mb_chr(ord($char) - ord('A') + 0x1F1E6, 'UTF-8');
        }
        return $flag;
    }
}
