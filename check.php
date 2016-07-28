<?php
error_reporting(~E_WARNING);
require_once 'function.php';

$server = $argv[1];
$port = 4370;
$deviceid = $argv[2];

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
    print_r(checkValid($data_recv ));
  } else {
    return FALSE;
  }
} catch(ErrorException $e) {
  return FALSE;
} catch(exception $e) {
  return FALSE;
}
?>
