<?php

$common_output[] = '
<div class="table-responsive">
    <table id="eventlog" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="eventicon"></th>
                <th data-column-id="datetime" data-order="desc">日期時間</th>
                <th data-column-id="hostname">主機名稱</th>
                <th data-column-id="type">類型</th>
                <th data-column-id="message">訊息</th>
                <th data-column-id="username">使用者</th>
            </tr>
        </thead>
    </table>
</div>
<script>

var eventlog_grid = $("#eventlog").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    post: function ()
    {
        return {
            id: "eventlog",
            device: "' .mres($vars['device']) .'",
            eventtype: "' .mres($vars['eventtype']) .'",
        };
    },
    url: "ajax_table.php"
});

</script>
';
