<?php
namespace App\Helpers;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class CarbonRange
{
    public static function fillMissingDates($data, $start, $end){
        
        $period = CarbonPeriod::create($start, $end);

        $filledData = collect($data)->keyBy(function($item) {
            return Carbon::parse($item->date)->format('Y-m-d');
        });

        return collect($period)->map(function($date) use ($filledData) {
            $formatted = $date->format('Y-m-d');
            return [
                'date' => $formatted,
                'total_amount' => $filledData[$formatted]->total_amount ?? 0,
                'total' => $filledData[$formatted]->total ?? 0,
            ];
        });

    }
   
}