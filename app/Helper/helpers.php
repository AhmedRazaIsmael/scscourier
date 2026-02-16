<?php
use NumberFormatter;

if (!function_exists('numberToWords')) {
    function numberToWords($number)
    {
        $formatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        return ucwords($formatter->format($number));
    }
}
