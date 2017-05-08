<?php
  error_reporting(~E_WARNING);
  require_once 'function.php';
  require_once 'zklib/fingerplib.php';
  require_once 'conf.php';

  $server = $argv[1];
  $port = 4370;
  $deviceid = $argv[2];

  // Time NOW
  $timeNow = date('H:i:s');

  if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
  {
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    die("Couldn't create socket: [$errorcode] $errormsg \n");
  }

  // option socket timeout
  socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>3, "usec"=>0));

  // connect command
  $command = CMD_CONNECT;
  $command_string = '';
  $chksum = 0;
  $session_id = 0;
  $reply_id = -1 + USHRT_MAX;

  $buf = createHeader($command, $chksum, $session_id, $reply_id, $command_string);
  socket_sendto($sock, $buf, strlen($buf), 0, $server, $port);
  try {
    socket_recvfrom($sock, $data_recv, 1024, 0, $server, $port);
    if(strlen($data_recv) > 0) {
      $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr( $data_recv, 0, 8 ) );
      $session_id =  hexdec( $u['h6'].$u['h5'] );

      //print_r(checkValid($data_recv ));
      print_r("STATUS : CONNECTED\n");
      print_r("TIME : ".$timeNow."\n");
      $status="CONNECTED";
    } else {

      // return FALSE;
      print_r("STATUS : DISCONNECTED\n");
      print_r("TIME : ".$timeNow."\n");
      $status="DISCONNECTED";
    }
  } catch(ErrorException $e) {

    // return FALSE;
    print_r("STATUS : DISCONNECTED\n");
    print_r("TIME : ".$timeNow."\n");
    $status="DISCONNECTED";
  } catch(exception $e) {

    // return FALSE;
    print_r("STATUS : DISCONNECTED\n");
    print_r("TIME : ".$timeNow."\n");
    $status="DISCONNECTED";
  }

  if($status == "CONNECTED") {
    print_r("SYNC !\n\n");

    $fp = new FingerPLib($server, $port);
    $ret = $fp->connect();
    sleep(1);
    if($ret):
      // $fp->disableDevice();
      // sleep(1);

      // GET LOG
      $attendance = $fp->getAttendance();
      print_r($attendance);

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
        print_r($_timeH."|".$_timeM."|".$_timeS."\n");

        print_r($fuserid."-".$fdate."-".$ftime."\n");

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
    endif;
  }

?>
