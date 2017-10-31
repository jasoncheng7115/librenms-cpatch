<?php

use LibreNMS\Exceptions\HostUnreachableException;

$no_refresh = true;

if ($_SESSION['userlevel'] < 10) {
    include 'includes/error-no-perm.inc.php';

    exit;
}

$hostname = isset($_POST['hostname']) ? clean($_POST['hostname']) : false;
$snmp_enabled = isset($_POST['snmp']);

if ($hostname !== false) {
    echo '<div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6">';
    if ($_SESSION['userlevel'] > '5') {
        // Settings common to SNMPv2 & v3
        if ($_POST['port']) {
            $port = clean($_POST['port']);
        } else {
            $port = $config['snmp']['port'];
        }

        if ($_POST['transport']) {
            $transport = clean($_POST['transport']);
        } else {
            $transport = 'udp';
        }

        $additional = array();
        if (!$snmp_enabled) {
            $snmpver = 'v2c';
            $additional = array(
                'snmp_disable' => 1,
                'os'           => $_POST['os'] ? mres($_POST['os_id']) : "ping",
                'hardware'     => mres($_POST['hardware']),
            );
        } elseif ($_POST['snmpver'] === 'v2c' || $_POST['snmpver'] === 'v1') {
            if ($_POST['community']) {
                $config['snmp']['community'] = array(clean($_POST['community']));
            }

            $snmpver = clean($_POST['snmpver']);
            print_message("Adding host $hostname communit".(count($config['snmp']['community']) == 1 ? 'y' : 'ies').' '.implode(', ', $config['snmp']['community'])." 埠號 $port 使用 $transport");
        } elseif ($_POST['snmpver'] === 'v3') {
            $v3 = array(
                   'authlevel'  => clean($_POST['authlevel']),
                   'authname'   => clean($_POST['authname']),
                   'authpass'   => clean($_POST['authpass']),
                   'authalgo'   => clean($_POST['authalgo']),
                   'cryptopass' => clean($_POST['cryptopass']),
                   'cryptoalgo' => clean($_POST['cryptoalgo']),
                  );

            array_push($config['snmp']['v3'], $v3);

            $snmpver = 'v3';
            print_message("正在新增 SNMPv3 主機 $hostname 埠號 $port");
        } else {
            print_error('不支援的 SNMP 版本. There was a dropdown menu, how did you reach this error ?');
        }//end if
        $poller_group = clean($_POST['poller_group']);
        $force_add    = ($_POST['force_add'] == 'on');

        $port_assoc_mode = clean($_POST['port_assoc_mode']);
        try {
            $device_id = addHost($hostname, $snmpver, $port, $transport, $poller_group, $force_add, $port_assoc_mode, $additional);
            $link = generate_device_url(array('device_id' => $device_id));
            print_message("裝置已新增 <a href='$link'>$hostname ($device_id)</a>");
        } catch (HostUnreachableException $e) {
            print_error($e->getMessage());
            foreach ($e->getReasons() as $reason) {
                print_error($reason);
            }
        } catch (Exception $e) {
            print_error($e->getMessage());
        }
    } else {
        print_error("你沒有足夠的權限可以新增裝置。");
    }//end if
    echo '    </div>
            <div class="col-sm-3">
            </div>
        </div>';
}//end if

$pagetitle[] = 新增主機;

?>

<div class="row">
  <div class="col-sm-3">
  </div>
  <div class="col-sm-6">
<form name="form1" method="post" action="" class="form-horizontal" role="form">
  <div><h2>新增裝置</h2></div>
  <div class="alert alert-info">Devices will be checked for Ping/SNMP reachability before being probed.</div>
  <div class="well well-lg">
    <div class='form-group'>
        <label for='hardware' class='col-sm-3 control-label'>SNMP</label>
        <div class='col-sm-4'>
            <input type="checkbox" id="snmp" name="snmp" data-size="small" onChange="disableSnmp(this);" checked>
        </div>
    </div>
    <div class="form-group">
      <label for="hostname" class="col-sm-3 control-label">主機名稱</label>
      <div class="col-sm-9">
        <input type="text" id="hostname" name="hostname" class="form-control input-sm" placeholder="主機名稱或 IP 位址">
      </div>
    </div>
    <div id='snmp_override' style="display: none;">
        <div class='form-group'>
            <label for='hardware' class='col-sm-3 control-label'>Hardware (optional)</label>
            <div class='col-sm-9'>
                <input id='hardware' class='form-control' name='hardware' placeholder="Hardware (optional)"/>
            </div>
        </div>
        <div class='form-group'>
            <label for='os' class='col-sm-3 control-label'>OS (optional)</label>
            <div class='col-sm-9'>
                <input id='os' class='form-control' name='os' placeholder="OS (optional)"/>
                <input type='hidden' id='os_id' class='form-control' name='os_id' />
            </div>
        </div>
    </div>
    <div id="snmp_conf" style="display: block;">
        <div class="form-group">
          <label for="snmpver" class="col-sm-3 control-label">SNMP 版本</label>
          <div class="col-sm-3">
            <select name="snmpver" id="snmpver" class="form-control input-sm" onChange="changeForm();">
              <option value="v1">v1</option>
              <option value="v2c" selected>v2c</option>
              <option value="v3">v3</option>
            </select>
          </div>
          <div class="col-sm-3">
            <input type="text" name="port" placeholder="埠號" class="form-control input-sm">
          </div>
          <div class="col-sm-3">
            <select name="transport" id="transport" class="form-control input-sm">
<?php
foreach ($config['snmp']['transports'] as $transport) {
    echo "<option value='".$transport."'";
    if ($transport == $device['transport']) {
        echo " selected='selected'";
    }

    echo '>'.$transport.'</option>';
}
?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="port_association_mode" class="col-sm-3 control-label">Port 協議模式</label>
          <div class="col-sm-3">
            <select name="port_assoc_mode" id="port_assoc_mode" class="form-control input-sm">
<?php


foreach (get_port_assoc_modes() as $mode) {
    $selected = "";
    if ($mode == $config['default_port_association_mode']) {
        $selected = "selected";
    }

    echo "              <option value=\"$mode\" $selected>$mode</option>\n";
}
?>
            </select>
          </div>
        </div>
        <div id="snmpv1_2">
          <div class="form-group">
            <div class="col-sm-12 alert alert-info">
              <label class="control-label text-left input-sm">SNMPv1/2c 組態設定</label>
            </div>
          </div>
          <div class="form-group">
            <label for="community" class="col-sm-3 control-label">Community</label>
            <div class="col-sm-9">
              <input type="text" name="community" id="community" placeholder="請輸入 Community 名稱" class="form-control input-sm">
            </div>
          </div>
        </div>
        <div id="snmpv3">
          <div class="form-group">
            <div class="col-sm-12 alert alert-info">
              <label class="control-label text-left input-sm">SNMPv3 組態設定</label>
            </div>
          </div>
          <div class="form-group">
            <label for="authlevel" class="col-sm-3 control-label">Auth Level</label>
            <div class="col-sm-3">
              <select name="authlevel" id="authlevel" class="form-control input-sm">
                <option value="noAuthNoPriv" selected>noAuthNoPriv</option>
                <option value="authNoPriv">authNoPriv</option>
                <option value="authPriv">authPriv</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="authname" class="col-sm-3 control-label">認證帳號</label>
            <div class="col-sm-9">
              <input type="text" name="authname" id="authname" class="form-control input-sm">
            </div>
          </div>
          <div class="form-group">
            <label for="authpass" class="col-sm-3 control-label">認證密碼</label>
            <div class="col-sm-9">
              <input type="text" name="authpass" id="authpass" placeholder="AuthPass" class="form-control input-sm">
            </div>
          </div>
          <div class="form-group">
            <label for="authalgo" class="col-sm-3 control-label">認證演算法</label>
            <div class="col-sm-9">
              <select name="authalgo" id="authalgo" class="form-control input-sm">
                <option value="MD5" selected>MD5</option>
                <option value="SHA">SHA</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="cryptopass" class="col-sm-3 control-label">加密用密碼</label>
            <div class="col-sm-9">
              <input type="text" name="cryptopass" id="cryptopass" placeholder="Crypto Password" class="form-control input-sm">
            </div>
          </div>
          <div class="form-group">
            <label for="cryptoalgo" class="col-sm-3 control-label">加密演算法</label>
            <div class="col-sm-9">
              <select name="cryptoalgo" id="cryptoalgo" class="form-control input-sm">
                <option value="AES" selected>AES</option>
                <option value="DES">DES</option>
              </select>
            </div>
          </div>
        </div>
      </div>
<?php
if ($config['distributed_poller'] === true) {
    echo '
          <div class="form-group">
              <label for="poller_group" class="col-sm-3 control-label">Poller 群組</label>
              <div class="col-sm-9">
                  <select name="poller_group" id="poller_group" class="form-control input-sm">
                      <option value="0"> 預設 poller 群組</option>
    ';

    foreach (dbFetchRows('SELECT `id`,`group_name` FROM `poller_groups`') as $group) {
        echo '<option value="'.$group['id'].'">'.$group['group_name'].'</option>';
    }

    echo '
                  </select>
              </div>
          </div>
    ';
}//endif
?>
      <div class="form-group">
          <div class="col-sm-offset-3 col-sm-9">
              <div class="checkbox">
                  <label>
                      <input type="checkbox" name="force_add" id="force_add"> 強制增加 - 不做 ICMP 或 SNMP 狀態確認
                  </label>
              </div>
          </div>
      </div>
    <hr>
    <center><button type="submit" class="btn btn-default" name="Submit">新增裝置</button></center>
  </div>
</form>
  </div>
  <div class="col-sm-3">
  </div>
</div>
<script>
    function changeForm() {
        snmpVersion = $("#snmpver").val();
        if(snmpVersion == 'v1' || snmpVersion == 'v2c') {
            $('#snmpv1_2').show();
            $('#snmpv3').hide();
        }
        else if(snmpVersion == 'v3') {
            $('#snmpv1_2').hide();
            $('#snmpv3').show();
        }
    }
    $('#snmpv3').toggle();

    function disableSnmp(e) {
        if(e.checked) {
            $('#snmp_conf').show();
            $('#snmp_override').hide();
        } else {
            $('#snmp_conf').hide();
            $('#snmp_override').show();
        }
    }

    var os_suggestions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: "ajax_ossuggest.php?term=%QUERY",
            filter: function (output) {
                return $.map(output, function (item) {
                    return {
                        text: item.text,
                        os: item.os,
                    };
                });
            },
            wildcard: "%QUERY"
        }
    });
    os_suggestions.initialize();
    $('#os').typeahead({
            hint: true,
            highlight: true,
            minLength: 1,
            classNames: {
                menu: 'typeahead-left'
            }
        },
        {
            source: os_suggestions.ttAdapter(),
            async: true,
            displayKey: 'text',
            valueKey: 'os',
            templates: {
                suggestion: Handlebars.compile('<p>&nbsp;{{text}}</p>')
            },
            limit: 20
        });

    $("#os").on("typeahead:selected typeahead:autocompleted", function(e,datum) {
        $("#os_id").val(datum.os);
        $("#os").html('<mark>' + datum.text + '</mark>');
    });

    $("[name='snmp']").bootstrapSwitch('offColor','danger');
<?php
if ($hostname !== false && !$snmp_enabled) {
    echo '  $("[name=\'snmp\']").trigger(\'click\');';
}
?>
</script>
