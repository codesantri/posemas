<?php
if (!function_exists('spelledOut')) {
    function spelledOut($number)
    {
        $formatter = new \NumberFormatter('id', \NumberFormatter::SPELLOUT);
        return ucwords($formatter->format($number)) . ' Rupiah';
    }
}
