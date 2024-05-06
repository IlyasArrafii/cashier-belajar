<?php

if (!function_exists('generateSequentialNumber')) {
    function generateSequentialNumber(string $model, ?string $initials = null, string $column = 'order_number'): string
    {
        $lastRecord = $model::latest('id')->first();

        $lastNumber = $lastRecord ? intval(substr($lastRecord->$column, strlen($initials))) : 0;
        $newNumber = $lastNumber + mt_rand(100000, 999999);

        return $initials . str_pad($newNumber, 9, 'ORD' , STR_PAD_LEFT);
    }
}
