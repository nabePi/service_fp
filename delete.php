<?php
  require_once 'conf.php';
  $id = $_GET['id'];
  $del = $con->query("DELETE FROM device WHERE id = $id");
  if($del){
    echo "ok";
  }
?>
