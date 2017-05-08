<?php

  $ip = $_GET['ip'];
  $deviceid = $_GET['deviceid'];

  $command = 'php sync_time_manual.php '.$ip.' '.$deviceid;
  $rslt = shell_exec($command);
  if (!empty($rslt)) {
    echo "<b>SYNC MANUALLY SUCCESS</b>";
  }

?>
