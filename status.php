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
  // $pid = 8843;
  // $command = 'ps -p '.$pid;
  $command = "ps -ef | grep 'php finger_service.php ".$ip." ".$deviceid."' | awk '{print $8 $9 $10 $11}'";
  exec($command,$op);
  // print_r($op[0]."\n");
  $str_check = "phpfinger_service.php".$ip.$deviceid;
  if($op[0]==$str_check){
    echo "<b>SERVICE | php finger_service.php ".$ip." ".$deviceid." | RUNNING</b>";
  }
?>
