<?php
error_reporting(~E_WARNING);
require_once 'function.php';
require_once 'zklib/fingerplib.php';
require_once 'conf.php';

$server = $argv[1];
$port = 4370;
$deviceid = $argv[2];

$fp = new FingerPLib($server, $port);
$ret = $fp->connect();
sleep(1);
if($ret):
  // $fp->disableDevice();
  // sleep(1);

  // GET LOG
  $attendance = $fp->getAttendance();
  // print_r($attendance);

  foreach ($attendance as $af) {
    $fuserid = $af[0];
    $unpack_datetime = explode(" ", $af[1]);
    $fdate = $unpack_datetime[0];
    $ftime = $unpack_datetime[1];

    //stuff var modify

    // unpack $ftime
    $unpack_ftime = explode(':', $ftime);
    $_timeH = $unpack_ftime[0];
    $_timeM = $unpack_ftime[1];
    $_timeS = $unpack_ftime[2];


    $newDateNow = new DateTime($fdate, new DateTimeZone("Asia/Jakarta"));

    $newDateNow->modify("-1 day");
    $dateMinusOne = $newDateNow->format("Y-m-d");

    // date_default_timezone_set("Asia/Jakarta");
    // $dateSystem = date('Y-m-d');
    // $timeSystem = date('H:i:s');

    // $ftime = $_timeH.":".$_timeM.":".$_timeS;
    // print_r($_timeH."|".$_timeM."|".$_timeS."\n");
    //
    // print_r($fuserid."-".$fdate."-".$ftime."\n");

    $countInsert = 0;

    // SQL query
    $query = $con->query("SELECT * FROM presensi WHERE kode_device = '$fuserid' AND tanggal = '$fdate'");
    // print_r($query);
    if($query->num_rows < 1){
      if(intval($_timeH) <= 4) {
        $queryC = $con->query("SELECT jam_keluar FROM presensi WHERE kode_device = '$fuserid' AND tanggal = '$dateMinusOne'");
        $jam_keluarC = $queryC->fetch_row();
        if(empty($jam_keluarC[0])){
          $con->query("UPDATE presensi SET jam_keluar = '$ftime' WHERE kode_device = '$fuserid' AND tanggal = '$dateMinusOne'");
          $countInsert = $countInsert + 1;
        }
      }
      else if(intval($_timeH) >= 5){
        $in = $con->query("INSERT INTO presensi VALUES(NULL, '$fuserid', '$fdate', '', '')");
        $countInsert = $countInsert + 1;
        if(intval($_timeH) < 14) {
          $con->query("UPDATE presensi SET jam_masuk = '$ftime' WHERE kode_device = '$fuserid' AND tanggal = '$fdate'");
          $countInsert = $countInsert + 1;
        }
        else {
          $con->query("UPDATE presensi SET jam_keluar = '$ftime' WHERE kode_device = '$fuserid' AND tanggal = '$fdate'");
          $countInsert = $countInsert + 1;
        }
      }
    } else {
      if($_timeH >= 14) {
        $con->query("UPDATE presensi SET jam_keluar = '$ftime' WHERE kode_device = '$fuserid' AND tanggal = '$fdate'");
        $countInsert = $countInsert + 1;
      }
      // print_r("dada");
    }
    // print_r("Insert Data :".$countInsert."\n\n");
    // END SQL query
  }

  sleep(1);

  // $fp->enableDevice();
  // sleep(1);
  $fp->disconnect();

  // true
  print_r(1);
endif;
?>
