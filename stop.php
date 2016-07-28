<?php
  // $file = fopen("servicefile","r");
  // $readfile = fread($file,filesize("servicefile"));
  // fclose($file);
  // $_readfile = explode("|", $readfile);
  // $ip = $_readfile[0];
  // $deviceid = $_readfile[1];

  $ip = $_GET['ip'];
  $deviceid = $_GET['deviceid'];

  // $pid = $_GET['pid'];
  // $command = 'kill '.$pid;
  // $command = 'killall -9 finger_service.php';
  $command = "pkill -f 'php finger_service.php ".$ip." ".$deviceid."'";
  exec($command);
  echo "<b>SERVICE | php finger_service.php ".$ip." ".$deviceid." | STOP<b>";
?>
