<?php
  $ip = $_GET['ip'];
  $deviceid = $_GET['deviceid'];
  // $ip = '192.168.1.1';
  // $deviceid = '21';
  $command = 'php check.php '.$ip.' '.$deviceid;
  $rslt = shell_exec($command);
  if (!empty($rslt)) {
    $command = 'nohup php finger_service.php '.$ip.' '.$deviceid.' > /dev/null 2>&1 & echo $!';
    exec($command ,$op);
    $pid = (int)$op[0];
    if (!empty($pid)) {
      echo "<b>SERVICE | php finger_service.php ".$ip." ".$deviceid." | START</b>";
    }

    $file = fopen("servicefile", "w") or die("Unable to open file!");
    $txt = $ip."|".$deviceid;
    fwrite($file, $txt);
    fclose($file);
  }else {
    echo "<b>IP Address atau Device ID tidak sesuai dengan mesin !</b>";
  }

?>
