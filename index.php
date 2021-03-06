<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service FP</title>

    <link rel="stylesheet" href="bootstrap/css/bootstrap.css">

    <style>
      body{
        padding: 20px;
      }
    </style>

  </head>

  <body>
    <?php
      require_once 'conf.php';
      $query = $con->query("SELECT * FROM device");
    ?>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">ADD DEVICE</button>
    <br><br>
    <table class="table table-striped">
      <tr>
        <th>No</th>
        <th>IP address</th>
        <th>Device ID</th>
        <th>Status</th>
        <th>Action</th>
        <th>Service</th>
      </tr>
      <?php $n=1; ?>
      <?php
while($row = $query->fetch_row()){?>
      <tr>
        <td><?php echo $n; ?></td>
        <td><?php echo $row[1]; ?></td>
        <td><?php echo $row[2]; ?></td>
        <td><b>-</b></td>
        <td>
          <span class="btn btn-danger" id="delete" onclick="fdelete('<?php echo $row[0]; ?>')">DELETE DEVICE</span>
        </td>
        <td>
          <span class="btn btn-primary" onclick="fsync('<?php echo $row[1].'#'.$row[2]; ?>', this)">SYNC</span>
          <span class="btn btn-success" onclick="fconnect('<?php echo $row[1].'#'.$row[2]; ?>', this)">CONNECT</span>
          <span class="btn btn-warning" onclick="fdisconnect('<?php echo $row[1].'#'.$row[2]; ?>', this)">DISCONNECT</span>
          <span class="btn btn-info" onclick="fstatus('<?php echo $row[1].'#'.$row[2]; ?>', null, this)">STATUS</span>
        </td>
      </tr>
      <?php $n++; ?>
      <?php } ?>
    </table>

    <img src="spinner.gif" style="display:none;margin-battom:5px;" id="spinner">

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Add Device</h4>
          </div>
          <div class="modal-body">
            <div class="alert alert-danger" id="notif" role="alert" style="display:none;">
              <strong>Warning!</strong> Better check yourself, you're not looking too good.
            </div>
            IP Address : <input type="text" name="ip" id="ip"><br><br>
            Device ID &nbsp;&nbsp;: <input type="text" name="deviceid" id="deviceid"><br><br>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" id="save">Save</button>
          </div>
        </div>
      </div>
    </div>

    <!-- IP Address : <input type="text" name="ip" id="ip"><br><br>
    Device ID &nbsp;&nbsp;: <input type="text" name="deviceid" id="deviceid"><br><br>
    <span class="btn btn-success" id="connect">CONNECT</span>
    <span class="btn btn-danger" id="disconnect">DISCONNECT</span>
    <span class="btn btn-info" id="cek">STATUS</span>
    <br><br>
    <img src="spinner.gif" style="display:none;margin-battom:5px;" id="spinner">
    <div class="alert alert-info" id="status">&nbsp;</div> -->

    <script src="jquery-1.11.3.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script>
      function fdelete(id){
        console.log(id);
        var req = 'id='+id;
        $.ajax({
          url: 'delete.php',
          data: req,
          type: "GET",
          success: function(res){
            if(res != ""){
              location.reload();
            }
          }
        });
      }

      function ftest(cmd, elm){
        console.log(cmd);
        console.log(elm.parentNode.parentNode.rowIndex); //rowIndex
        console.log(elm.parentNode.cellIndex); //cellIndex
      }

      function fconnect(val, elm){
        console.log(val);
        var sval = val.split("#");
        var ip = sval[0];
        var deviceid = sval[1];
        var req = 'ip='+ip+'&deviceid='+deviceid;
        var row = elm.parentNode.parentNode.rowIndex + 1;
        if ((ip != "") && (deviceid != "")) {
          $.ajax({
            url: 'status.php',
            data: req,
            type: "GET",
            success: function(res){
              if (res == "") {
                $.ajax({
                  beforeSend: function() { $("#spinner").css("display", "block"); },
                  complete: function() { $("#spinner").css("display", "none"); },
                  url: 'start.php',
                  data: req,
                  type: "GET",
                  success: function(res){
                    console.log(res);
                    // $("#status").html(res);
                    $('table tr:nth-child('+row+') td').eq(3).html(res);

                  }
                });
              } else {
                  // $("#status").html("<b>SERVICE | RUNNING</b>");
                  $('table tr:nth-child('+row+') td').eq(3).html('<b> SERVICE | RUNNING</b>');

              }
            }
          });
        } else {
          // $("#status").html("<b>IP Address</b> dan <b>Device ID</b> tidak boleh kosong !");
        }
      }

      function fdisconnect(val, elm){
        console.log(val);
        var sval = val.split("#");
        var ip = sval[0];
        var deviceid = sval[1];
        var req = 'ip='+ip+'&deviceid='+deviceid;
        var row = elm.parentNode.parentNode.rowIndex + 1;
        $.ajax({
          url: 'status.php',
          data: req,
          type: "GET",
          success: function(res){
            if (res != "") {
              $.ajax({
                url: 'stop.php',
                data: req,
                type: "GET",
                success: function(res){
                    // $("#status").html(res);
                    $('table tr:nth-child('+row+') td').eq(3).html(res);

                }
              });
            } else {
                //$("#status").html("<b>SERVICE | Not Found</b>");
                $('table tr:nth-child('+row+') td').eq(3).html('<b> SERVICE | NOT FOUND</b>');
            }
          }
        });
      }

      function fsync(val, elm){
        console.log(val);
        var sval = val.split("#");
        var ip = sval[0];
        var deviceid = sval[1];
        var req = 'ip='+ip+'&deviceid='+deviceid;
        var row = elm.parentNode.parentNode.rowIndex + 1;
        $.ajax({
          beforeSend: function() { $("#spinner").css("display", "block"); },
          complete: function() { $("#spinner").css("display", "none"); },
          url: 'start_sync_time_manual.php',
          data: req,
          type: "GET",
          success: function(res){
            console.log(res);
            // $("#status").html(res);
            $('table tr:nth-child('+row+') td').eq(3).html(res);

          }
        });
      }

      function fstatus(val, n, elm){
        console.log(val);
        var sval = val.split("#");
        var ip = sval[0];
        var deviceid = sval[1];
        var req = 'ip='+ip+'&deviceid='+deviceid;
        if(n == null){
            var row = elm.parentNode.parentNode.rowIndex + 1;
        }else{
            var row = n;
        }
        $.ajax({
          url: 'status.php',
          data: req,
          type: "GET",
          success: function(res){
            if (res != "") {
                // $("#status").html(res);
                console.log(row);
                $('table tr:nth-child('+row+') td').eq(3).html(res);
            } else {
                // $("#status").html("<b>SERVICE | Not Found</b>");
                $('table tr:nth-child('+row+') td').eq(3).html('<b> SERVICE | NOT FOUND</b>');
            }
          }
        });
      }

      $(document).ready(function(){
        var c_row = $('table tr').length;
        for(var i = 2; i <= c_row; i++){
            var ip = $('table tr:nth-child('+i+') td').eq(1).text();
            var device = $('table tr:nth-child('+i+') td').eq(2).text();
            var val = ip+"#"+device;
            console.log(val);
            fstatus(val, i);
        }



        /* $('table tr').each(function(index){
            // var rowP = row + 2;
            var ip = $('table tr:nth-child('+index+') td').eq(1).text();
            var device = $('table tr:nth-child('+index+') td').eq(2).text();
            var val = ip+"#"+device;
            console.log(val);
        }) */

        $("#save").click(function(){
          var ip = $("#ip").val();
          var deviceid = $("#deviceid").val();
          var req = 'ip='+ip+'&deviceid='+deviceid;
          if ((ip != "") && (deviceid != "")) {
            $.ajax({
              url: 'add.php',
              data: req,
              type: "GET",
              success: function(res){
                if(res != ""){
                  $("#ip").val("");
                  $("#deviceid").val("");
                  location.reload();
                }
              }
            });
          } else {
            $("#notif").html("<b>IP Address</b> dan <b>Device ID</b> tidak boleh kosong !");
            $("#notif").show();
          }
        });

        $("#connect").click(function(){
          var ip = $("#ip").val();
          var deviceid = $("#deviceid").val();
          var req = 'ip='+ip+'&deviceid='+deviceid;
          if ((ip != "") && (deviceid != "")) {
            $.ajax({
              url: 'status.php',
              type: "GET",
              success: function(res){
                if (res == "") {
                  $.ajax({
                    beforeSend: function() { $("#spinner").css("display", "block"); },
                    complete: function() { $("#spinner").css("display", "none"); },
                    url: 'start.php',
                    data: req,
                    type: "GET",
                    success: function(res){
                      console.log(res);
                      $("#status").html(res);
                    }
                  });
                } else {
                  $("#status").html("<b>SERVICE | RUNNING</b>");
                }
              }
            });
          } else {
            $("#status").html("<b>IP Address</b> dan <b>Device ID</b> tidak boleh kosong !");
          }
        });

        $("#cek").click(function(){
          $.ajax({
            url: 'status.php',
            type: "GET",
            success: function(res){
              if (res != "") {
                $("#status").html(res);
              } else {
                $("#status").html("<b>SERVICE | Not Found</b>");
              }
            }
          });
        });

        $("#disconnect").click(function(){
          $.ajax({
            url: 'status.php',
            type: "GET",
            success: function(res){
              if (res != "") {
                $.ajax({
                  url: 'stop.php',
                  type: "GET",
                  success: function(res){
                    $("#status").html(res);
                  }
                });
              } else {
                $("#status").html("<b>SERVICE | Not Found</b>");
              }
            }
          });
        });
      });
    </script>
  </body>
</html>
