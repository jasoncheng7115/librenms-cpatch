<?php

$datas = array('mempool','processor','storage');
if ($used_sensors['temperature']) {
    $datas[] = 'temperature';
}
if ($used_sensors['charge']) {
    $datas[] = 'charge';
}
if ($used_sensors['humidity']) {
    $datas[] = 'humidity';
}
if ($used_sensors['fanspeed']) {
    $datas[] = 'fanspeed';
}
if ($used_sensors['voltage']) {
    $datas[] = 'voltage';
}
if ($used_sensors['frequency']) {
    $datas[] = 'frequency';
}
if ($used_sensors['runtime']) {
    $datas[] = 'runtime';
}
if ($used_sensors['current']) {
    $datas[] = 'current';
}
if ($used_sensors['power']) {
    $datas[] = 'power';
}
if ($used_sensors['dbm']) {
    $datas[] = 'dbm';
}
if ($used_sensors['load']) {
    $datas[] = 'load';
}
if ($used_sensors['state']) {
    $datas[] = 'state';
}
if ($used_sensors['signal']) {
    $datas[] = 'signal';
}
if ($used_sensors['snr']) {
    $datas[] = 'snr';
}
if ($used_sensors['pressure']) {
    $datas[] = 'pressure';
}
if ($used_sensors['cooling']) {
    $datas[] = 'cooling';
}

// FIXME generalize -> static-config ?
$type_text['overview'] = "概觀";
$type_text['temperature'] = "溫度";
$type_text['charge'] = "電池充電";
$type_text['humidity'] = "濕度";
$type_text['mempool'] = "記憶體";
$type_text['storage'] = "儲存";
$type_text['diskio'] = "磁碟 I/O";
$type_text['processor'] = "處理器";
$type_text['voltage'] = "電壓";
$type_text['fanspeed'] = "風扇轉速";
$type_text['frequency'] = "頻率";
$type_text['runtime'] = "Runtime";
$type_text['current'] = "數值";
$type_text['power'] = "電力";
$type_text['toner'] = "碳粉";
$type_text['dbm'] = "dBm";
$type_text['load'] = "負載";
$type_text['state'] = "狀態";
$type_text['signal'] = "訊號";
$type_text['snr'] = "SNR";
$type_text['pressure'] = "Pressure";
$type_text['cooling'] = "Cooling";

if (!$vars['metric']) {
    $vars['metric'] = "processor";
}
if (!$vars['view']) {
    $vars['view'] = "detail";
}

$link_array = array('page'    => 'health');

$pagetitle[] = "健康狀況";

print_optionbar_start('', '');

echo('<span style="font-weight: bold;">健康狀況</span> &#187; ');

$sep = "";
foreach ($datas as $texttype) {
    $metric = strtolower($texttype);
    echo($sep);
    if ($vars['metric'] == $metric) {
        echo("<span class='pagemenu-selected'>");
    }

    echo(generate_link($type_text[$metric], $link_array, array('metric'=> $metric, 'view' => $vars['view'])));

    if ($vars['metric'] == $metric) {
        echo("</span>");
    }

    $sep = ' | ';
}

unset($sep);

echo('<div style="float: right;">');

if ($vars['view'] == "graphs") {
    echo('<span class="pagemenu-selected">');
}
echo(generate_link("圖表", $link_array, array('metric'=> $vars['metric'], 'view' => "graphs")));
if ($vars['view'] == "graphs") {
    echo('</span>');
}

echo(' | ');

if ($vars['view'] != "graphs") {
    echo('<span class="pagemenu-selected">');
}

echo(generate_link("無圖表", $link_array, array('metric'=> $vars['metric'], 'view' => "detail")));

if ($vars['view'] != "graphs") {
    echo('</span>');
}

echo('</div>');

print_optionbar_end();

if (in_array($vars['metric'], array_keys($used_sensors))
    || $vars['metric'] == 'processor'
    || $vars['metric'] == 'storage'
    || $vars['metric'] == 'toner'
    || $vars['metric'] == 'mempool') {
    include('pages/health/'.$vars['metric'].'.inc.php');
} else {
    echo("No sensors of type " . $vars['metric'] . " found.");
}
