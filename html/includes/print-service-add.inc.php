<?php

echo "
<h3><span class='label label-success threeqtr-width'>新增服務</span></h3>
<form id='addsrv' name='addsrv' method='post' action='' class='form-horizontal' role='form'>
  <div class='well well-lg'>
    <div class='form-group'>
      <input type='hidden' name='addsrv' value='yes'>
      <label for='device' class='col-sm-2 control-label'>裝置</label>
      <div class='col-sm-5'>
        <select name='device' class='form-control input-sm'>
          $devicesform
        </select>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='type' class='col-sm-2 control-label'>類型</label>
      <div class='col-sm-5'>
        <select name='type' id='type' class='form-control input-sm'>
          $servicesform
        </select>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='descr' class='col-sm-2 control-label'>說明</label>
      <div class='col-sm-5'>
        <textarea name='descr' id='descr' class='form-control input-sm' rows='5'></textarea>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='ip' class='col-sm-2 control-label'>IP 位址</label>
      <div class='col-sm-5'>
        <input name='ip' id='ip' class='form-control input-sm' placeholder='IP 位址'>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='params' class='col-sm-2 control-label'>參數</label>
      <div class='col-sm-5'>
        <input name='params' id='params' class='form-control input-sm'>
      </div>
      <div class='col-sm-5'>
          某些服務檢測時可能需要提供參數。
      </div>
    </div>
    <button type='submit' name='Submit' class='btn btn-success input-sm'>新增服務</button>
  </div>
</form>";
