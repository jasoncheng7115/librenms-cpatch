<?php
$param = array();

$pagetitle[] = 'Alert Log';

            echo '<div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-2">
                            <strong>警報記錄</strong>
                        </div>
                        <div class="col-md-2 col-md-offset-8">
                            <div class="pull-right pdf-export"></div>
                        </div>
                    </div>
                </div>
            ';
?>

<div class="table-responsive">
<table id="alertlog" class="table table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th data-column-id="time_logged" data-order="desc">記錄時間</th>
            <th data-column-id="details" data-sortable="false">&nbsp;</th>
            <th data-column-id="hostname">裝置</th>
            <th data-column-id="alert">警報</th>
            <th data-column-id="status" data-sortable="false">狀態</th>
        </tr>
    </thead>
</table>
</div>
</div>

<script>

var grid = $("#alertlog").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    templates: {
        header: '<div id="{{ctx.id}}" class="{{css.header}}"><div class="row"> \
                <div class="col-sm-8 actionBar"><span class="pull-left"> \
                <form method="post" action="" class="form-inline" role="form" id="result_form"> \
                <div class="form-group"> \
                <label> \
                <strong>裝置&nbsp;</strong> \
                </label> \
                <select name="device_id" id="device_id" class="form-control input-sm" style="min-width: 175px;"> \
                <option value="">All Devices</option> \
               </select> \
               </div> \
               <div class="form-group"> \
               <label> \
               <strong>&nbsp;狀態&nbsp;</strong> \
               </label> \
               <select name="state" id="state" class="form-control input-sm"> \
               <option value="-1"></option> \
               <option value="0">Ok</option> \
               <option value="1">Alert</option> \
               </select> \
               </div> \
               <button type="submit" class="btn btn-default input-sm">篩選</button> \
               </form></span></div> \
               <div class="col-sm-4 actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>'
    },
    post: function ()
    {
        return {
            id: "alertlog",
            device_id: '<?php echo htmlspecialchars($_POST['device_id']); ?>',
            state: '<?php echo htmlspecialchars($_POST['state']); ?>'
        };
    },
    url: "ajax_table.php"
}).on("loaded.rs.jquery.bootgrid", function() {

    var results = $("div.infos").text().split(" ");
    low = results[1] -1 ;
    high = results[3];
    max = high - low;
    search = $('.search-field').val();

    $(".pdf-export").html("<a href='pdf.php?report=alert-log&device_id=<?php echo $_POST['device_id']; ?>&string="+search+"&results="+max+"&start="+low+"'><i class='fa fa-heartbeat fa-lg icon-theme' aria-hidden='true'></i> Export to pdf</a>");

    grid.find(".incident-toggle").each( function() {
      $(this).parent().addClass('incident-toggle-td');
    }).on("click", function(e) {
      var target = $(this).data("target");
      $(target).collapse('toggle');
      $(this).toggleClass('fa-plus fa-minus');
    });
    grid.find(".incident").each( function() {
      $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');
      $(this).parent().parent().on("mouseenter", function() {
        $(this).find(".incident-toggle").fadeIn(200);
      }).on("mouseleave", function() {
        $(this).find(".incident-toggle").fadeOut(200);
      }).on("click", "td:not(.incident-toggle-td)", function() {
        var target = $(this).parent().find(".incident-toggle").data("target");
        if( $(this).parent().find(".incident-toggle").hasClass('fa-plus') ) {
          $(this).parent().find(".incident-toggle").toggleClass('fa-plus fa-minus');
          $(target).collapse('toggle');
        }
      });
    });
});

<?php echo generate_fill_select_js('hostnames', '#device_id', $_POST['device_id']); ?>
</script>
