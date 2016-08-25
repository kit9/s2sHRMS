<?php 
$date1=date_create("2013-03-15");
$date2=date_create("2014-03-01");
$diff=date_diff($date1,$date2);
$job_duration = $diff->m;
echo $job_duration;


