<?php
define( 'USHRT_MAX', 65535 );

// add modify for realtime
define('CMD_REG_EVENT', 500);
define('EF_ATTLOG', 1);
define('EF_VERIFY', 256);


define( 'CMD_CONNECT', 1000 );
define( 'CMD_EXIT', 1001 );
define( 'CMD_ENABLEDEVICE', 1002 );
define( 'CMD_DISABLEDEVICE', 1003 );

define( 'CMD_ACK_OK', 2000 );
define( 'CMD_ACK_ERROR', 2001 );
define( 'CMD_ACK_DATA', 2002 );

define( 'CMD_PREPARE_DATA', 1500 );
define( 'CMD_DATA', 1501 );

define( 'CMD_USERTEMP_RRQ', 9 );
define( 'CMD_ATTLOG_RRQ', 13 );
// define( 'CMD_CLEAR_DATA', 14 );
// define( 'CMD_CLEAR_ATTLOG', 15 );
//
// define( 'CMD_WRITE_LCD', 66 );
//
// define( 'CMD_GET_TIME', 201 );
// define( 'CMD_SET_TIME', 202 );
//
// define( 'CMD_VERSION', 1100 );
// define( 'CMD_DEVICE', 11 );
//
// define( 'CMD_CLEAR_ADMIN', 20 );
// define( 'CMD_SET_USER', 8 );

define( 'LEVEL_USER', 0 );
define( 'LEVEL_ADMIN', 14 );

function getSizeUser($data_recv) {
		/*Checks a returned packet to see if it returned CMD_PREPARE_DATA,
		indicating that data packets are to be sent

		Returns the amount of bytes that are going to be sent*/
		$u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr( $data_recv, 0, 8 ) );
		$command = hexdec( $u['h2'].$u['h1'] );

		if ( $command == CMD_PREPARE_DATA ) {
				$u = unpack('H2h1/H2h2/H2h3/H2h4', substr( $data_recv, 8, 4 ) );
				$size = hexdec($u['h4'].$u['h3'].$u['h2'].$u['h1']);
				return $size;
		} else
				return FALSE;
}

function createChkSum($p) {
    /*This function calculates the chksum of the packet to be sent to the
    time clock

    Copied from zkemsdk.c*/

    $l = count($p);
    $chksum = 0;
    $i = $l;
    $j = 1;
    while ($i > 1) {
        $u = unpack('S', pack('C2', $p['c'.$j], $p['c'.($j+1)] ) );

        $chksum += $u[1];

        if ( $chksum > USHRT_MAX )
            $chksum -= USHRT_MAX;
        $i-=2;
        $j+=2;
    }

    if ($i)
        $chksum = $chksum + $p['c'.strval(count($p))];

    while ($chksum > USHRT_MAX)
        $chksum -= USHRT_MAX;

    if ( $chksum > 0 )
        $chksum = -($chksum);
    else
        $chksum = abs($chksum);

    $chksum -= 1;
    while ($chksum < 0)
        $chksum += USHRT_MAX;

    return pack('S', $chksum);
}

function createHeader($command, $chksum, $session_id, $reply_id, $command_string) {
    /*This function puts a the parts that make up a packet together and
    packs them into a byte string*/
    $buf = pack('SSSS', $command, $chksum, $session_id, $reply_id).$command_string;

    $buf = unpack('C'.(8+strlen($command_string)).'c', $buf);

    $u = unpack('S', createChkSum($buf));

    if ( is_array( $u ) ) {
        while( list( $key ) = each( $u ) ) {
            $u = $u[$key];
            break;
        }
    }
    $chksum = $u;

    $reply_id += 1;

    if ($reply_id >= USHRT_MAX)
        $reply_id -= USHRT_MAX;

    $buf = pack('SSSS', $command, $chksum, $session_id, $reply_id);

    return $buf.$command_string;

}

function checkValid($reply) {
    /*Checks a returned packet to see if it returned CMD_ACK_OK,
    indicating success*/
    $u = unpack('H2h1/H2h2', substr($reply, 0, 8) );

    $command = hexdec( $u['h2'].$u['h1'] );
    if ($command == CMD_ACK_OK)
        return TRUE;
    else
        return FALSE;
}
?>
