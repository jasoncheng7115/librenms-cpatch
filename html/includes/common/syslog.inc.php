<?php

$common_output[] = '
<div class="table-responsive">
    <table id="syslog" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="priority">&nbsp;</th>
                <th data-column-id="timestamp" data-order="desc">日期時間</th>
                <th data-column-id="device_id">主機名稱</th>
                <th data-column-id="program">程式</th>
                <th data-column-id="msg">訊息</th>
                <th data-column-id="status">狀態</th>
            </tr>
        </thead>
    </table>
</div>
<script>

var syslog_grid = $("#syslog").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    post: function ()
    {
        return {
            id: "syslog",
            device: "'.mres($vars['device']) .'",
            program: "'.mres($vars['program']).'",
            priority: "'.mres($vars['priority']).'",
            to: "'.mres($vars['to']).'",
            from: "'.mres($vars['from']).'",
        };
    },
    url: "ajax_table.php",
    statusMappings: {
        // Nagios style
        0: "text-muted",
        1: "warning",
        2: "danger",
        3: "info"
    }
});

</script>
';
