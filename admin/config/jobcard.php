<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of jobcard
 *
 * @author MD MAHAMUDUR ZAMAN BHUYIAN (Fahad)
 * Website For Help : http://www.fahadbhuyian.com
 */
class jobcard {

    public function TimeBetweenTwoDateTime($time1, $time2,$format) {
        define("SECONDS_PER_HOUR", 60 * 60);
        $start = strtotime($time1);
        $stop = strtotime($time2);
        $difference = $stop - $start;
        $hours = round($difference / SECONDS_PER_HOUR, 0, PHP_ROUND_HALF_DOWN);
        $minutes = floor(($difference % SECONDS_PER_HOUR) / 60);
        $second = intval(date('s', $difference));

        if (strlen($hours) == 1) {
            $hours = "0" . $hours;
        }

        if (strlen($minutes) == 1) {
            $minutes = "0" . $minutes;
        }

        if (strlen($second) == 1) {
            $second = "0" . $second;
        }



        return date($format, strtotime($hours . ":" . $minutes . ":" . $second));
        //return "12:00:10";
    }

    public function TimeDifference($temp_time) {
        //$std_ot_minute_buffer = "00:30:00";
        $std_ot_minute_buffer = "30";
        //If actual ot is bigger than std minute
        //Generate it as OT
        //Otherwise assign zero


        $time = $temp_time;
        $dt = new DateTime("1970-01-01 $time", new DateTimeZone('UTC'));
        $seconds = (int) $dt->getTimestamp();
        $minute = ($seconds / 60);
        //return $minute;

        if ($minute >= $std_ot_minute_buffer) {
            $OT = $temp_time;
        } else {
            $OT = "00:00:00";
        }

        return $OT;
    }

    public function OtDuductionBYHour($tem_time_diff) {
        $tiffinDeduct = 6;
        $actualOT = "";
        $tifin_time = explode(":", $tem_time_diff);
        if ($tifin_time[0] > $tiffinDeduct) {
            $actualOT = date("H:i:s", strtotime("$tem_time_diff -1 hour"));
        } else {
            //$con->debug(date("H:i:s", strtotime($tem_time_diff)));
            $actualOT = $tem_time_diff;
        }

        return $actualOT;
    }

}
