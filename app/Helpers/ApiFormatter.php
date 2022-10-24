<?php

namespace App\Helpers;

class ApiFormatter
{

    public static function createApi($data = null)
    {
        return response()->json($data);
    }

    public static function IDGenerator($model, $throw, $length = 4, $prefix, $branch)
    {
        $data = $model::where('FC_BRANCH', $branch)->orderBy('NOROUTING', 'desc')->first();
        if (!$data) {
            $og_length = 4;
            $last_number = 1;
        } else {
            $code = substr($data->$throw, strlen($prefix) + 1);
            $actial_last_number = ($code / 1) * 1;
            $increment_last_number = $actial_last_number + 1;
            $last_number_length = strlen($increment_last_number);
            $og_length = $length - $last_number_length;
            $last_number = $increment_last_number;
        }
        $zeros = "";
        for ($i = 0; $i < $og_length; $i++) {
            $zeros .= "0";
        }
        return $prefix . "-" . $zeros . $last_number;
    }
}
