<?php
  $start = $_GET['start'];
  $end   = $_GET['end'];
  $sub   = $_GET['sub'];
  $desc   = $_GET['desc'];
  $loc   = $_GET['loc'];

$vcs = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "duvallchamber.com
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:".date('Ymd',$start)."T".date('His',$start)."
DTEND:".date('Ymd',$end)."T".date('His',$end)."
SUMMARY:".$sub."
DESCRIPTION: https://duvallchamberofcommerce.com/duvall-events/#!event/".$desc."
LOCATION:".$loc."
END:VEVENT
END:VCALENDAR";

 //set correct content-type-header
header("Content-Type: text/x-vCalendar");
header("Content-Disposition: attachment; filename=calendar.vcs");

 echo $ical;
 exit;
?>