<?php
require_once 'includes/object-cache.inc.php';

$temp_output = '
<div class="panel panel-default panel-condensed table-responsive">
<table class="table table-hover table-condensed table-striped">
  <thead>
    <tr>
      <th>總覽</th>
      <th><a href="devices/">裝置</a></th>
      <th><a href="ports/">連接埠</a></th>
';

if ($config['show_services']) {
    $temp_output .= '
      <th><a href="services/">服務</a></th>
';
}

$temp_output .= '
    </tr>
  </thead>
  <tbody>
    <tr>
      <th><span class="green">上線</span></th>
      <td><a href="devices/format=list_detail/state=up/"><span class="green">'. $devices['up'] .'</span></a></td>
      <td><a href="ports/format=list_detail/state=up/"><span class="green">'. $ports['up'] .'</span></a></td>
';
if ($config['show_services']) {
    $temp_output .= '
      <td><a href="services/view=details/state=ok/"><span class="green">'. $services['up'] .'</span></a></td>
';
}

$temp_output .= '
    </tr>
    <tr>
      <th><span class="red">離線</span></th>
      <td><a href="devices/format=list_detail/state=down/"><span class="red">'. $devices['down'] .'</span></a></td>
      <td><a href="ports/format=list_detail/state=down/"><span class="red">'. $ports['down'] .'</span></a></td>
';

if ($config['show_services']) {
    $temp_output .= '
      <td><a href="services/view=details/state=critical/"><span class="red">'. $services['down'] .'</span></a></td>
';
}

$temp_output .= '
    </tr>
    <tr>
      <th><span class="grey">忽略</span></th>
      <td><a href="devices/format=list_detail/ignore=1/"><span class="grey">'. $devices['ignored'] .'</span></a></td>
      <td><a href="ports/format=list_detail/ignore=1/"><span class="grey">'. $ports['ignored'] .'</span></a></td>
';

if ($config['show_services']) {
    $temp_output .= '
      <td><a href="services/view=details/ignore=1/"><span class="grey">'. $services['ignored'] .'</span></a></td>
';
}

$temp_output .= '
    </tr>
    <tr>
      <th><span class="black">取消/關閉</span></th>
      <td><a href="devices/format=list_detail/disabled=1/"><span class="black">'. $devices['disabled'] .'</span></a></td>
      <td><a href="ports/format=list_detail/state=admindown/"><span class="black">'. $ports['shutdown'] .'</span></a></td>
';

if ($config['show_services']) {
    $temp_output .= '
      <td><a href="services/view=details/disabled=1/"><span class="black">'. $services['disabled'] .'</span></a></td>
';
}

if ($config['summary_errors']) {
    $temp_output .= '
    </tr>
    <tr>
      <th><span class="black">錯誤</span></th>
      <td>-</td>
      <td><a href="ports/format=list_detail/errors=1/"><span class="black"> '.$ports['errored'].'</span></a></td>
';
    if ($config['show_services']) {
        $temp_output .= '
      <td>-</td>
';
    }
}

$temp_output .= '
    </tr>
    <tr>
      <th><span class="grey">總計</span></th>
      <td><a href="devices/"><span>'. $devices['count'] .'</span></a></td>
      <td><a href="ports/"><span>'. $ports['count'] .'</span></a></td>
';

if ($config['show_services']) {
    $temp_output .= '
      <td><a href="services/"><span>'. $services['count'] .'</span></a></td>
';
}

$temp_output .= '
    </tr>
  </tbody>
</table>
</div>
';

unset($common_output);
$common_output[] = $temp_output;
