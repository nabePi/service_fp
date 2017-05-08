<html>
    <head>
        <title>Attendance</title>
    </head>

    <body>
<?php
    include("fingerplib.php");

    $fp = new FingerPLib("192.168.137.4", 4370);

    $ret = $fp->connect();
    sleep(1);
    if ( $ret ):
        $fp->disableDevice();
        sleep(1);
    ?>

	<table border="1" cellpadding="5" cellspacing="2">
            <tr>
                <th colspan="2">Data Attendance</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>DATE TIME</th>
            </tr>
            <?php
            $attendance = $fp->getAttendance();
            print_r($attendance);
            sleep(1);
            while(list($idx, $attendancedata) = each($attendance)):
            ?>
            <tr>
                <td><?php echo $attendancedata[0] ?></td>
                <td><?php echo $attendancedata[1] ?></td>
                </tr>
            <?php
            endwhile
            ?>
        </table>

		<?php
        $fp->enableDevice();
        sleep(1);
        $fp->disconnect();
    endif
?>
    </body>
</html>
