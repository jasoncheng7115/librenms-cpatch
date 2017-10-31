<?php

use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;

echo '<div class="container-fluid">';
echo "<div class='row'>
      <div class='col-md-12'>
          <div class='panel panel-default panel-condensed'>
            <div class='panel-heading'>";

if ($config['overview_show_sysDescr']) {
    echo '<i class="fa fa-id-card fa-lg icon-theme" aria-hidden="true"></i> <strong>'.$device['sysDescr'].'</strong>';
}

echo '</div>
      <table class="table table-hover table-condensed table-striped">';

$uptime = formatUptime($device['uptime']);
$uptime_text = 'Uptime';
if ($device['status'] == 0) {
    // Rewrite $uptime to be downtime if device is down
    $uptime = formatUptime(time() - strtotime($device['last_polled']));
    $uptime_text = 'Downtime';
}

if ($device['os'] == 'ios') {
    formatCiscoHardware($device);
}

if ($device['features']) {
    $device['features'] = '('.$device['features'].')';
}

$device['os_text'] = $config['os'][$device['os']]['text'];

echo '<tr>
        <td>系統名稱</td>
        <td>'.$device['sysName'].' </td>
      </tr>';

if (!empty($device['ip'])) {
     echo "<tr><td>反解 IP</td><td>{$device['ip']}</td></tr>";
} elseif ($config['force_ip_to_sysname'] === true) {
    try {
        $ip = IP::parse($device['hostname']);
        echo "<tr><td>IP 位址</td><td>$ip</td></tr>";
    } catch (InvalidIpException $e) {
        // don't add an ip line
    }
}

if ($device['purpose']) {
    echo '<tr>
        <td>Description</td>
        <td>'.display($device['purpose']).'</td>
      </tr>';
}

if ($device['hardware']) {
    echo '<tr>
        <td>硬體</td>
        <td>'.$device['hardware'].'</td>
      </tr>';
}

echo '<tr>
        <td>作業系統</td>
        <td>'.$device['os_text'].' '.$device['version'].' '.$device['features'].' </td>
      </tr>';

if ($device['serial']) {
    echo '<tr>
        <td>序號</td>
        <td>'.$device['serial'].'</td>
      </tr>';
}

if ($device['sysObjectID']) {
    echo '<tr>
        <td>Object ID</td>
        <td>'.$device['sysObjectID'].'</td>
      </tr>';
}

if ($device['sysContact']) {
    echo '<tr>
        <td>聯絡人</td>';
    if (get_dev_attrib($device, 'override_sysContact_bool')) {
        echo '
        <td>'.htmlspecialchars(get_dev_attrib($device, 'override_sysContact_string')).'</td>
      </tr>
      <tr>
        <td>SNMP Contact</td>';
    }

    echo '
        <td>'.htmlspecialchars($device['sysContact']).'</td>
      </tr>';
}

if ($device['location']) {
    echo '<tr>
        <td>位置</td>
        <td>'.$device['location'].'</td>
      </tr>';
    if (get_dev_attrib($device, 'override_sysLocation_bool') && !empty($device['real_location'])) {
        echo '<tr>
        <td>SNMP Location</td>
        <td>'.$device['real_location'].'</td>
      </tr>';
    }
}

$loc = parse_location($device['location']);
if (!is_array($loc)) {
    $loc = dbFetchRow("SELECT `lat`,`lng` FROM `locations` WHERE `location`=? LIMIT 1", array($device['location']));
}
if (is_array($loc)) {
    echo '<tr>
        <td>經緯度</td>
        <td>['.$loc['lat'].','.$loc['lng'].'] <div class="pull-right"><a href="https://maps.google.com/?q='.$loc['lat'].'+'.$loc['lng'].'" target="_blank" class="btn btn-success btn-xs" role="button"><i class="fa fa-map-marker" style="color:white" aria-hidden="true"></i> 地圖</button></div></a></td>
    </tr>';
}

if ($uptime) {
    echo "<tr>
        <td>運作時間</td>
        <td>".$uptime."</td>
      </tr>";
}

echo '</table>
      </div>
      </div>
      </div>
      </div>';
