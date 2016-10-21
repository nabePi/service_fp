<?php
  require_once 'conf.php';
  $ip = $_GET['ip'];
  $deviceid = $_GET['deviceid'];
  $in = $con->query("INSERT INTO device VALUES(NULL, '$ip', '$deviceid')");
  if($in){
    echo $ip.$deviceid;
  }
?>
