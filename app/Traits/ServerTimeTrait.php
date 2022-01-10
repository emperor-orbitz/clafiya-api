<?php

namespace App\Traits;

trait ServerTimeTrait{
     function getServerTime(){
        $timestamp = time();
        $date_time = date("d-m-Y (D) H:i:s", $timestamp);
        return $date_time;
    }
}