<?php
  $ip = $_GET['ip'];
  $deviceid = $_GET['deviceid'];
  // $ip = '192.168.1.1';
  // $deviceid = '21';
  $command = 'php check.php '.$ip.' '.$deviceid;
  $rslt = shell_exec($command);
  if (!empty($rslt)) {
    // sync_first
    $command_first = 'php sync_first.php '.$ip.' '.$deviceid;
    $rslt_first = shell_exec($command_first);
    if (!empty($rslt_first)) {
      // finger_service
      $command = 'nohup php finger_service.php '.$ip.' '.$deviceid.' > /dev/null 2>&1 & echo $!';

      // sync_ping
      // $command2 = 'nohup php ping.php '.$ip.' '.$deviceid.' > /dev/null 2>&1 & echo $!';

      // sync_time | Automatic Sync in 4 A.M
       $command2 = 'nohup php sync_time.php '.$ip.' '.$deviceid.' > /dev/null 2>&1 & echo $!';

      exec($command ,$op);
      exec($command2 ,$op2);
      $pid = (int)$op[0];
      $pid2 = (int)$op2[0];
      if ((!empty($pid)) and (!empty($pid2))) {
        echo "<b>SERVICE | php finger_service.php ".$ip." ".$deviceid." | START</b>";
      }
    }

    // $file = fopen("servicefile", "w") or die("Unable to open file!");
    // $txt = $ip."|".$deviceid;
    // fwrite($file, $txt);
    // fclose($file);
  }else {
    echo "<b>IP Address atau Device ID tidak sesuai dengan mesin !</b>";
  }

?>
