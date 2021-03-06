<?php

$no_refresh = true;

$param = array();

if ($vars['action'] == 'expunge' && $_SESSION['userlevel'] >= '10') {
    dbQuery('TRUNCATE TABLE `eventlog`');
    print_message('Event log truncated');
}

$pagetitle[] = '事件記錄';

print_optionbar_start();

?>

<form method="post" action="" class="form-inline" role="form" id="result_form">
    <div class="form-group">
      <label>
        <strong>裝置</strong>
      </label>
      <select name="device" id="device" class="form-control input-sm">
        <option value="">所有裝置</option>
        <?php
        foreach (get_all_devices() as $data) {
            if (device_permitted($data['device_id'])) {
                echo "<option value='".$data['device_id']."'";
                if ($data['device_id'] == $_POST['device']) {
                    echo 'selected';
                }

                echo '>'.format_hostname($data).'</option>';
            }
        }
        ?>
      </select>
    </div>
    <div class="form-group">
        <label>
            <strong>類型: </strong>
        </label>
        <select name="eventtype" id="eventtype" class="form-control input-sm">
            <option value="">所有類型</option>
<?php

foreach (dbFetchColumn("SELECT `type` FROM `eventlog` GROUP BY `type`") as $type) {
    echo "<option value='$type'";
    if ($type === $_POST['eventtype']) {
        echo ' selected';
    }
    echo ">$type</option>";
}

?>
        </select>
    </div>
    <button type="submit" class="btn btn-default input-sm">篩選</button>
</form>

<?php
print_optionbar_end();

require_once 'includes/common/eventlog.inc.php';
echo implode('', $common_output);

?>

