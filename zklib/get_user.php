<html>
    <head>
        <title>User</title>
    </head>
    
    <body>
<?php
    include("fingerplib.php");
    
    $fp = new FingerPLib("192.168.1.1", 4370);
    
    $ret = $fp->connect();
    sleep(1);
    if ( $ret ): 
        $fp->disableDevice();
        sleep(1);
    ?>
        <table border="1" cellpadding="5" cellspacing="2">
            <tr>
                <th colspan="5">Data User</th>
            </tr>
            <tr>
                <th>UID</th>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Password</th>
            </tr>
            <?php
            try {
                
                $user = $fp->getUser();
                sleep(1);
                while(list($uid, $userdata) = each($user)):
                    if ($userdata[2] == LEVEL_ADMIN)
                        $role = 'ADMIN';
                    elseif ($userdata[2] == LEVEL_USER)
                        $role = 'USER';
                    else
                        $role = 'Unknown';
                ?>
                <tr>
                    <td><?php echo $uid ?></td>
                    <td><?php echo $userdata[0] ?></td>
                    <td><?php echo $userdata[1] ?></td>
                    <td><?php echo $role ?></td>
                    <td><?php echo $userdata[3] ?>&nbsp;</td>
                </tr>
                <?php
                endwhile;
            } catch (Exception $e) {
                header("HTTP/1.0 404 Not Found");
                header('HTTP', true, 500);                
            }
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