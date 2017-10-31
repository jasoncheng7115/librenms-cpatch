<?php

// Set Defaults here

if (!isset($vars['format'])) {
    $vars['format'] = "list_detail";
}

$pagetitle[] = "Devices";

print_optionbar_start();

echo '<span class="devices-font-bold">列表: </span>';

$menu_options = array('basic' => '基本', 'detail' => '詳細');

$sep = "";
foreach ($menu_options as $option => $text) {
    echo($sep);
    if ($vars['format'] == "list_" . $option) {
        echo '<span class="pagemenu-selected">';
    }
    echo '<a href="' . generate_url($vars, array('format' => "list_" . $option)) . '">' . $text . '</a>';
    if ($vars['format'] == "list_" . $option) {
        echo '</span>';
    }
    $sep = " | ";
}

echo ' | <span class="devices-font-bold">圖表: </span>';

$menu_options = array('bits' => '位元',
    'processor' => 'CPU',
    'ucd_load' => '負載',
    'mempool' => '記憶體',
    'uptime' => '運作時間',
    'storage' => '儲存',
    'diskio' => '磁碟 I/O',
    'poller_perf' => 'Poller',
    'ping_perf' => 'Ping',
    'temperature' => '溫度'
);
$sep = "";
foreach ($menu_options as $option => $text) {
    echo $sep;
    if ($vars['format'] == 'graph_' . $option) {
        echo '<span class="pagemenu-selected">';
    }
    echo '<a href="' . generate_url($vars, array('format' => 'graph_' . $option, 'from' => '-24h', 'to' => 'now')) . '">' . $text . '</a>';
    if ($vars['format'] == 'graph_' . $option) {
        echo '</span>';
    }
    $sep = " | ";
}

echo '<div class="devices-float-right">';

$graphs_types = '<select name="type" id="type" onchange="window.open(this.options[this.selectedIndex].value,\'_top\')" class="devices-graphs-select">';
$type = 'device';
foreach (get_graph_subtypes($type) as $avail_type) {
    $display_type = is_mib_graph($type, $avail_type) ? $avail_type : nicecase($avail_type);
    if ('graph_' . $avail_type == $vars['format']) {
        $is_selected = 'selected';
    } else {
        $is_selected = '';
    }
    $graphs_types .= '<option value="' . generate_url($vars, array('format' => 'graph_' . $avail_type, 'from' => $vars['from'] ?: $config['time']['day'], 'to' => $vars['to'] ?: $config['time']['now'])) . '" ' . $is_selected . '>' . $display_type . '</option>';
}
$graphs_types .= '</select>';

echo $graphs_types;

if (isset($vars['searchbar']) && $vars['searchbar'] == "hide") {
    echo('<a href="' . generate_url($vars, array('searchbar' => '')) . '">還原搜尋列</a>');
} else {
    echo('<a href="' . generate_url($vars, array('searchbar' => 'hide')) . '">移除搜尋列</a>');
}

echo("  | ");

if (isset($vars['bare']) && $vars['bare'] == "yes") {
    echo('<a href="' . generate_url($vars, array('bare' => '')) . '">還原標題列</a>');
} else {
    echo('<a href="' . generate_url($vars, array('bare' => 'yes')) . '">移除標題列</a>');
}

print_optionbar_end();

echo '</div>';

list($format, $subformat) = explode("_", $vars['format'], 2);

if ($format == "graph") {
    if (empty($vars['from'])) {
        $graph_array['from'] = $config['time']['day'];
    } else {
        $graph_array['from'] = $vars['from'];
    }
    if (empty($vars['to'])) {
        $graph_array['to'] = $config['time']['now'];
    } else {
        $graph_array['to'] = $vars['to'];
    }

    echo "
    <div class='well well-sm'>
        <div class='container-fluid'>
            <div class='row'>
                <div class='col-md-12'>
    ";
    include_once 'includes/print-date-selector.inc.php';
    echo '
                </div>
            </div>
        </div>
    </div>
    ';

    $sql_param = array();

    if (isset($vars['state'])) {
        if ($vars['state'] == 'up') {
            $state = '1';
        } elseif ($vars['state'] == 'down') {
            $state = '0';
        }
    }

    if (!empty($vars['hostname'])) {
        $where .= " AND hostname LIKE ?";
        $sql_param[] = "%" . $vars['hostname'] . "%";
    }
    if (!empty($vars['os'])) {
        $where .= " AND os = ?";
        $sql_param[] = $vars['os'];
    }
    if (!empty($vars['version'])) {
        $where .= " AND version = ?";
        $sql_param[] = $vars['version'];
    }
    if (!empty($vars['hardware'])) {
        $where .= " AND hardware = ?";
        $sql_param[] = $vars['hardware'];
    }
    if (!empty($vars['features'])) {
        $where .= " AND features = ?";
        $sql_param[] = $vars['features'];
    }

    if (!empty($vars['type'])) {
        if ($vars['type'] == 'generic') {
            $where .= " AND ( type = ? OR type = '')";
            $sql_param[] = $vars['type'];
        } else {
            $where .= " AND type = ?";
            $sql_param[] = $vars['type'];
        }
    }
    if (!empty($vars['state'])) {
        $where .= " AND status= ?";
        $sql_param[] = $state;
        $where .= " AND disabled='0' AND `ignore`='0'";
        $sql_param[] = '';
    }
    if (!empty($vars['disabled'])) {
        $where .= " AND disabled= ?";
        $sql_param[] = $vars['disabled'];
    }
    if (!empty($vars['ignore'])) {
        $where .= " AND `ignore`= ?";
        $sql_param[] = $vars['ignore'];
    }
    if (!empty($vars['location']) && $vars['location'] == "Unset") {
        $location_filter = '';
    }
    if (!empty($vars['location'])) {
        $location_filter = $vars['location'];
    }
    if (!empty($vars['group'])) {
        require_once('../includes/device-groups.inc.php');
        $where .= " AND ( ";
        foreach (GetDevicesFromGroup($vars['group']) as $dev) {
            $where .= "device_id = ? OR ";
            $sql_param[] = $dev;
        }
        $where = substr($where, 0, strlen($where) - 3);
        $where .= " )";
    }

    $query = "SELECT * FROM `devices` WHERE 1 ";

    if (isset($where)) {
        $query .= $where;
    }

    $query .= " ORDER BY hostname";

    $row = 1;
    foreach (dbFetchRows($query, $sql_param) as $device) {
        if (is_integer($row / 2)) {
            $row_colour = $list_colour_a;
        } else {
            $row_colour = $list_colour_b;
        }

        if (device_permitted($device['device_id'])) {
            if (!$location_filter || $device['location'] == $location_filter) {
                $graph_type = "device_" . $subformat;

                if ($_SESSION['widescreen']) {
                    $width = 270;
                } else {
                    $width = 315;
                }

                $graph_array_new = array();
                $graph_array_new['type'] = $graph_type;
                $graph_array_new['device'] = $device['device_id'];
                $graph_array_new['height'] = '110';
                $graph_array_new['width'] = $width;
                $graph_array_new['legend'] = 'no';
                $graph_array_new['title'] = 'yes';
                $graph_array_new['from'] = $graph_array['from'];
                $graph_array_new['to'] = $graph_array['to'];

                $graph_array_zoom = $graph_array_new;
                $graph_array_zoom['height'] = '150';
                $graph_array_zoom['width'] = '400';
                $graph_array_zoom['legend'] = 'yes';

                $link_array         = $graph_array;
                $link_array['page'] = 'graphs';
                $link_array['type'] = $graph_type;
                $link_array['device'] = $device['device_id'];
                unset($link_array['height'], $link_array['width']);
                $overlib_link = generate_url($link_array);

                echo '<div class="devices-overlib-box" style="min-width:' . ($width + 90) . '; max-width: ' . ($width + 90) . '">';
                echo '<div class="panel panel-default">';
                echo overlib_link($overlib_link, generate_lazy_graph_tag($graph_array_new), generate_graph_tag($graph_array_zoom), null);
                echo "</div></div>\n\n";
            }
        }
    }
} else {
    if (is_admin() === true || is_read() === true) {
        $os = "SELECT `os` FROM `devices` AS D WHERE 1 GROUP BY `os` ORDER BY `os`";
        $ver = "SELECT `version` FROM `devices` AS D WHERE 1 GROUP BY `version` ORDER BY `version`";
        $platform = "SELECT `hardware` FROM `devices` AS D WHERE 1 GROUP BY `hardware` ORDER BY `hardware`";
        $features = "SELECT `features` FROM `devices` AS D WHERE 1 GROUP BY `features` ORDER BY `features`";
        $types = "SELECT `type` FROM `devices` AS D WHERE 1 GROUP BY `type` ORDER BY `type`";
    } else {
        $os = "SELECT `os` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `os` ORDER BY `os`";
        $ver = "SELECT `version` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `version` ORDER BY `version`";
        $platform = "SELECT `hardware` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `hardware` ORDER BY `hardware`";
        $features = "SELECT `features` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `features` ORDER BY `features`";
        $types = "SELECT `type` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `type` ORDER BY `type`";
        $param[] = $_SESSION['user_id'];
    }


    $os_options = '<select name="os" id="os" class="form-control input-sm">';
    $os_options .= '<option value="">所有作業系統</option>';
    foreach (dbFetch($os, $param) as $data) {
        if ($data['os']) {
            $tmp_os = clean_bootgrid($data['os']);
            if ($tmp_os == $vars['os']) {
                $os_selected = 'selected';
            } else {
                $os_selected = '';
            }
            $os_options .= '<option value="' . $tmp_os . '" ' . $os_selected . '>' . $config['os'][$tmp_os]['text'] . '</option>';
        }
    }
    $os_options .= '<select>';

    $ver_options = '<select name="version" id="version" class="form-control input-sm">';
    $ver_options .= '<option value="">所有版本</option>';
    foreach (dbFetch($ver, $param) as $data) {
        if ($data['version']) {
            $tmp_version = clean_bootgrid($data['version']);
            if ($tmp_version == $vars['version']) {
                $ver_selected = 'selected';
            } else {
                $ver_selected = '';
            }
            $ver_options .= '<option value="' . $tmp_version . '" ' . $ver_selected . '> ' . $tmp_version . '</option>';
        }
    }
    $ver_options .= '</select>';

    $platform_options = '<select name="hardware" id="hardware" class="form-control input-sm">';
    $platform_options .= '<option value="">所有平台</option>';
    foreach (dbFetch($platform, $param) as $data) {
        if ($data['hardware']) {
            $tmp_hardware = clean_bootgrid($data['hardware']);
            if ($tmp_hardware == $vars['hardware']) {
                $platform_selected = 'selected';
            } else {
                $platform_selected = '';
            }
            $platform_options .= '<option value="' . $tmp_hardware . '" ' . $platform_selected . '>' . $tmp_hardware . '</option>';
        }
    }
    $platform_options .= '</select>';


    $features_options = '<select name="features" id="features" class="form-control input-sm">';
    $features_options .= '<option value="">所有功能集</option>';
    foreach (dbFetch($features, $param) as $data) {
        if ($data['features']) {
            $tmp_features = clean_bootgrid($data['features']);
            if ($tmp_features == $vars['features']) {
                $feature_selected = 'selected';
            } else {
                $feature_selected = '';
            }
            $features_options .= '<option value="' . $tmp_features . '" ' . $feature_selected . '>' . $tmp_features . '</option>';
        }
    }
    $features_options .= '</select>';

    $locations_options = '<select name="location" id="location" class="form-control input-sm">';
    $locations_options .= '<option value="">所有位置</option>';
    foreach (getlocations() as $location) {
        if ($location) {
            $location = clean_bootgrid($location);
            if ($location == $vars['location']) {
                $location_selected = 'selected';
            } else {
                $location_selected = '';
            }
            $locations_options .= '<option value="' . $location . '" ' . $location_selected . '>' . $location . '</option>';
        }
    }
    $locations_options .= '</select>';

    $types_options = '<select name="type" id="type" class="form-control input-sm">';
    $types_options .= '<option value="">所有裝置種類</option>';
    foreach (dbFetch($types, $param) as $data) {
        if ($data['type']) {
            if ($data['type'] == $vars['type']) {
                $type_selected = 'selected';
            } else {
                $type_selected = '';
            }
            $types_options .= '<option value="' . $data['type'] . '" ' . $type_selected . '> ' . ucfirst($data['type']) . '</option>';
        }
    }
    $types_options .= '</select>';

    if (isset($vars['searchbar']) && $vars['searchbar'] == "hide") {
        $searchbar = '';
    } else {
        $searchbar = '
            <div class="panel panel-default panel-condensed">
                <form method="post" action="" class="form-inline devices-search-header" role="form">
                        <div class="form-group">
                            <input type="text" name="hostname" id="hostname" value="' . $vars['hostname'] . '" class="form-control input-sm" placeholder="主機名稱">
                        </div>
                        <div class="form-group">' . $os_options . '</div>
                        <div class="form-group">' . $ver_options . '</div>
                        <div class="form-group">' . $platform_options . '</div>
                        <div class="form-group">' . $features_options . '</div>
                        <div class="form-group">' . $locations_options . '</div>
                        <div class="form-group">' . $types_options . '</div>
                        <input type="submit" class="btn btn-default input-sm devices-input-small" value="搜尋">
                        <a href="' . generate_url($vars) . '" title="更新瀏覽器頁面以顯示最新的搜尋結果。" class="btn btn-default input-sm devices-input-small">更新顯示</a>
                        <a href="' . generate_url(array('page' => 'devices', 'section' => $vars['section'], 'bare' => $vars['bare'])) . '" title="將條件重新設定為預設值。" class="btn btn-default input-sm devices-input-small">重置條件</a>               
                </form>
            </div>
        ';
    }

    echo $searchbar;

    echo '
    <div class="table-responsive">
        <table id="devices" class="table table-condensed table-hover">    
            <thead>
                <tr>
                    <th data-column-id="status" data-width="100px" data-searchable="false" data-formatter="status">狀態</th>
    ';

    if ($subformat == "detail") {
        echo '<th data-column-id="icon" data-width="80px" data-sortable="false" data-searchable="false" data-formatter="icon">製造商</th>';
    }

    echo '<th data-column-id="hostname" data-order="asc">裝置</th>';

    if ($subformat == "detail") {
        echo '<th data-column-id="ports" data-width="100px" data-sortable="false" data-searchable="false">偵測</th>';
    }

    echo '
                    <th data-column-id="hardware">平台</th>
                    <th data-column-id="os">作業系統</th>
                    <th data-column-id="uptime">運作時間</th>
    ';

    if ($subformat == "detail") {
        echo '<th data-column-id="location">位置</th>';
    }

    echo '
                    <th data-column-id="actions" data-width="90px" data-sortable="false" data-searchable="false" data-header-css-class="device-table-header-actions">操作</th>
                </tr>
            </thead>
        </table>
    </div>
    ';

    ?>

    <script>
        var grid = $("#devices").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            columnSelection: true,
            formatters: {
                "status": function (column, row) {
                    return "<span class=\"label label-" + row.extra + " devices-status-box-" + row.list_type + "\">" + row.msg + "</span>";
                },
                "icon": function (column, row) {
                    return "<span class=\"device-table-icon\">" + row.icon + "</span>";
                }
            },
            templates: {
                header: "<div class=\"devices-headers-table-menu\"><p class=\"{{css.actions}}\"></p></div><div class=\"row\"></div>"

            },
            post: function () {
                return {
                    id: "devices",
                    format: ' <?php echo mres($vars['format']); ?>',
                    hostname: '<?php echo htmlspecialchars($vars['hostname']); ?>',
                    os: '<?php echo mres($vars['os']); ?>',
                    version: '<?php echo mres($vars['version']); ?>',
                    hardware: '<?php echo mres($vars['hardware']); ?>',
                    features: '<?php echo mres($vars['features']); ?>',
                    location: '<?php echo mres($vars['location']); ?>',
                    type: '<?php echo mres($vars['type']); ?>',
                    state: '<?php echo mres($vars['state']); ?>',
                    disabled: '<?php echo mres($vars['disabled']); ?>',
                    ignore: '<?php echo mres($vars['ignore']); ?>',
                    group: '<?php echo mres($vars['group']); ?>',
                };
            },
            url: "ajax_table.php"
        });

    </script>

    <?php
}
