<?php

error_reporting(~E_WARNING);
// require_once 'conf.php';
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

// connect command
$command = CMD_CONNECT;
$command_string = '';
$chksum = 0;
$session_id = 0;
$reply_id = -1 + USHRT_MAX;

$buf = createHeader($command, $chksum, $session_id, $reply_id, $command_string);
socket_sendto($sock, $buf, strlen($buf), 0, $server, $port);
socket_recvfrom($sock, $data_recv, 1024, 0, $server, $port);
if ( strlen( $data_recv ) > 0 ) {
		$u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr( $data_recv, 0, 8 ) );
		$session_id =  hexdec( $u['h6'].$u['h5'] );
		// var_dump(checkValid($data_recv));
		print_r("# CONNECTED # \n\n");
} else{
		// var_dump(FALSE);
		print_r("# DISCONNECT # \n\n");
}

// get user command
$command = CMD_USERTEMP_RRQ;
$command_string = chr(5);
$chksum = 0;
$session_id = $session_id;
$u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr( $data_recv, 0, 8) );
$reply_id = hexdec( $u['h8'].$u['h7'] );
$buf = createHeader($command, $chksum, $session_id, $reply_id, $command_string);
socket_sendto($sock, $buf, strlen($buf), 0, $server, $port);
socket_recvfrom($sock, $data_recv, 1024, 0, $server, $port);
$u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6', substr( $data_recv, 0, 8 ) );
$userdata = array();
if (getSizeUser($data_recv)) {
		$bytes = getSizeUser($data_recv);
		while ( $bytes > 0 ) {
				socket_recvfrom($sock, $data_recv, 1032, 0, $server, $port);
				array_push($userdata, $data_recv);
				$bytes -= 1024;
		}

		$session_id =  hexdec( $u['h6'].$u['h5'] );
		socket_recvfrom($sock, $data_recv, 1024, 0, $server, $port);
}


$users = array();
if ( count($userdata) > 0 ) {
		for ( $x=0; $x<count($userdata); $x++) {
				if ( $x > 0 )
						$userdata[$x] = substr( $userdata[$x], 8 );
		}

		$userdata = implode('', $userdata );

		$userdata = substr( $userdata, 11 );

		while ( strlen($userdata) > 72 ) {

				$u = unpack( 'H144', substr( $userdata, 0, 72) );

				$uid = hexdec( substr($u[1], 0, 4) );
				$role = hexdec( substr($u[1], 4, 4) );
				$password = hex2bin( substr( $u[1], 8, 16 ) ).' ';
				$name = hex2bin( substr( $u[1], 24, 74 ) ). ' ';
				$userid = hex2bin( substr( $u[1], 98, 72) ).' ';

				//Clean up some messy characters from the user name
				$password = explode( chr(0), $password, 2 );
				$password = $password[0];
				$userid = explode( chr(0), $userid, 2);
				$userid = $userid[0];
				$name = explode(chr(0), $name, 3);
				$name = $name[0];

				if ( $name == "" ){
					$name = $uid;
				}

				if($role == LEVEL_ADMIN){
					$role = 'Administrator';
				} else if ($role == LEVEL_USER) {
					$role = 'User';
				} else{
					$role = $role;
				}


				$users[$userid] = array($uid, $userid, $name, $role, $password);

				$userdata = substr( $userdata, 72 );
		}
}
// print_r($users);


// realtime reg command
$command = CMD_REG_EVENT;
$command_string = 1;
$chksum = 0;
$session_id = $session_id;
$u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr( $data_recv, 0, 8) );
$reply_id = hexdec( $u['h8'].$u['h7'] );
$buf = createHeader($command, $chksum, $session_id, $reply_id, $command_string);
socket_sendto($sock, $buf, strlen($buf), 0, $server, $port);

while(1)
{

	if(socket_recvfrom ( $sock , $data_recv , 1024, MSG_WAITALL, $server, $port ) === FALSE)
	{
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);

		die("Could not receive data: [$errorcode] $errormsg \n");
	}

	$u = unpack('H88', substr( $data_recv, 0, 44) );
	if ($u) {
		// var_dump($u);
		// print_r("test");
		print_r($u);
		$_userid = substr($u[1],16,18);
		$create_userid = '';
		for ($i=0; $i < strlen($_userid); $i++) {
			if($i%2==0){
				$create_userid .= chr(hexdec(substr($_userid,$i,2)));
			}
		}

		$_dateY = "20".hexdec(substr($u[1],68,2));
		$_dateM = hexdec(substr($u[1],70,2));
		$_dateD = hexdec(substr($u[1],72,2));

		$_timeH = hexdec(substr($u[1],74,2));
		$_timeM = hexdec(substr($u[1],76,2));
		$_timeS = hexdec(substr($u[1],78,2));

		$fuserid = $create_userid;
		$fname = $users[intval($create_userid)][2];
		$fdate = $_dateY."-".$_dateM."-".$_dateD;
		$ftime = $_timeH.":".$_timeM.":".$_timeS;
		$fstatus = substr($u[1],67,1);

		print_r("User ID = ".$fuserid."\n");
		print_r("Name = ".$fname."\n");
		print_r("Date = ".$fdate."\n");
		print_r("Time = ".$ftime."\n");
		print_r("Status = ".$fstatus."\n");
		print_r("=======================================\n");

		//new modify
		$dateNow = $_dateY.'-'.$_dateM.'-'.$_dateD;
		$newDateNow = new DateTime($dateNow, new DateTimeZone("Asia/Jakarta"));

		$newDateNow->modify("-1 day");
		$dateMinusOne = $newDateNow->format("Y-m-d");

		date_default_timezone_set("Asia/Jakarta");
		$dateSystem = date('Y-m-d');
		$timeSystem = date('H:i:s');

		// SQL query
		$query = $con->query("SELECT * FROM presensi WHERE kode_device = '$fuserid' AND tanggal = '$fdate'");
		// print_r($query);
		if($query->num_rows < 1){
			if(intval($_timeH) <= 4) {
				$queryC = $con->query("SELECT jam_keluar FROM presensi WHERE kode_device = '$fuserid' AND tanggal = '$dateMinusOne'");
				$jam_keluarC = $queryC->fetch_row();
				if(empty($jam_keluarC[0])){
					$con->query("UPDATE presensi SET jam_keluar = '$ftime' WHERE kode_device = '$fuserid' AND tanggal = '$dateMinusOne'");
				}
			}
			else if(intval($_timeH) >= 5){
				$in = $con->query("INSERT INTO presensi VALUES(NULL, '$fuserid', '$fdate', '', '')");
				if(intval($_timeH) < 14) {
					$con->query("UPDATE presensi SET jam_masuk = '$ftime' WHERE kode_device = '$fuserid' AND tanggal = '$fdate'");
				}
				else {
					$con->query("UPDATE presensi SET jam_keluar = '$ftime' WHERE kode_device = '$fuserid' AND tanggal = '$fdate'");
				}
			}
		} else {
			if($_timeH >= 14) {
				$con->query("UPDATE presensi SET jam_keluar = '$ftime' WHERE kode_device = '$fuserid' AND tanggal = '$fdate'");
			}
			// print_r("dada");
		}

		// END SQL query

	}
}

?>
