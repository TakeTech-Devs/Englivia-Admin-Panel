<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Users Details  - Admin Panel </title>
        <?php include 'include-css.php'; ?>
    </head>
    <body class="nav-md">
        <div class="container body">
            <div class="main_container">
                <?php include 'sidebar.php'; ?>
                <!-- page content -->
                <div class="right_col" role="main">
                    <!-- top tiles -->
                    <br />
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Users Details </h2>
                                    <div class="clearfix"></div>
                                </div>
                               <div class="x_content">
                                  <!--   <div id="toolbar">
                                        <select id='export_select' class="form-control" >
                                            <option value="basic">Export This Page</option> -->
                                            <!--<option value="all">Export All</option>-->
                                           <!--   <option value="selected">Export Selected</option>
                                        </select>
                                    </div> -->
                                   
                                    <div style="visibility: hidden">
                                        <select id='export_select' class="form-control" >
                                            <option value="basic">Export This Page</option>
                                            <!--<option value="all">Export All</option>-->
                                            <option value="selected">Export Selected</option>
                                        </select>
                                    </div>
                                    <table
                                          aria-describedby="mydesc" class='table-striped' id='users_list' 
                                             data-toggle="table"
                                            data-url="get-list.php?table=users"
                                            data-click-to-select="true"
                                            data-side-pagination="server"
                                            data-pagination="true"
                                            data-page-list="[5, 10, 20, 50, 100, 200]"
                                            data-search="true" data-show-columns="false"
                                            data-show-refresh="true" data-trim-on-search="false"
                                            data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true"
                                            data-toolbar="#toolbar" data-show-export="false"
                                            data-maintain-selected="true"
                                            data-export-types='["txt","excel"]'
                                            data-export-options='{
                                            "fileName": "users-list-<?= date('d-m-y') ?>",
                                            	"ignoreColumn": ["state"]
                                            }'
                                            data-query-params="queryParams_1"
                                            > 
                                        <thead>
                                            <tr>
                                                <th scope="col" data-field="state" data-checkbox="true" data-visible="false"></th>
                                                <th scope="col" data-field="id" data-sortable="true" data-visible="true" >ID</th>
                                                <th scope="col" data-field="profile" data-sortable="false" data-visible="false" >Profile</th>
                                                <th scope="col" data-field="name" data-sortable="true"  data-visible="true" >Name</th>
                                                <th scope="col" data-field="email" data-sortable="true" data-visible="true" >Email</th>
                                                <th scope="col" data-field="mobile" data-sortable="true" data-visible="true" >Mobile</th>
                                              <!--  <th scope="col" data-field="type" data-sortable="false" data-visible="false" >Type</th> -->
                                              <!--  <th scope="col" data-field="coins" data-sortable="false" data-visible="false" >Coins</th> -->
                                             <!--   <th scope="col" data-field="refer_code" data-sortable="true" data-visible="false">Refer Code</th> -->
                                             <!--   <th scope="col" data-field="friends_code" data-sortable="true" data-visible="false">Friends Code</th> -->
                                             <!--   <th scope="col" data-field="fcm_id" data-sortable="true"  data-visible="false" >FCM ID</th> -->
                                             <!--   <th scope="col" data-field="ip_address" data-sortable="true"  data-visible="false" >IP Address</th> -->
                                                <th scope="col" data-field="status" data-sortable="true"  data-visible="true" >Status</th>
                                                <th scope="col" data-field="date_registered" data-sortable="true"  data-visible="true" >Register Date</th>
                                                <th scope="col" data-field="operate" data-events="actionEvents" data-visible="false" >Operate</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /page content -->
            <div class="modal fade" id='editUserModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update User Status</h4>
                        </div>
                        <div class="modal-body">
                            <form id="update_form"  method="POST" action ="db_operations.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="user_id" id="user_id" value=''/>
                                <input type='hidden' name="update_user" id="update_user" value='1'/>
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div id="status" class="btn-group" >
                                            <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                <input type="radio" name="status" value="0">  Deactive 
                                            </label>
                                            <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                <input type="radio" name="status" value="1"> Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="submit_btn" class="btn btn-success">Submit</button>
                                    </div>
                                </div>
                            </form>
                            <div class="row">
                                <div  class="col-md-offset-3 col-md-8" style ="display:none;" id="result"></div>                                    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- footer content -->
            <?php include 'footer.php'; ?>
            <!-- /footer content -->
        </div>

        <!-- jQuery -->
        <script>
            var $table = $('#users_list');
            $('#toolbar').find('select').change(function () {
                $table.bootstrapTable('refreshOptions', {
                    exportDataType: $(this).val()
                });
            });
        </script>
        <script>
            function queryParams_1(p) {
                return {
                    "status": $('#filter_status').val(),
                    limit: p.limit,
                    sort: p.sort,
                    order: p.order,
                    offset: p.offset,
                    search: p.search
                };
            }
        </script>
        <script>
            $('#report_form').on('submit', function (e) {
                e.preventDefault();
                $('#users_list').bootstrapTable('refresh');
            });
        </script>
                                                         
           <script>
            window.actionEvents = {
                'click .edit-users': function (e, value, row, index) {
                    // alert('You click remove icon, row: ' + JSON.stringify(row));
                    $("input[name=status][value=1]").prop('checked', true);
                    if ($(row.status).text() == 'Deactive')
                        $("input[name=status][value=0]").prop('checked', true);
                    $('#user_id').val(row.id);
                }
            };
        </script>
       
                                        
                                                         
        <script>
            $('#update_form').validate({
                rules: {
                    status: "required",
                }
            });
        </script>
        <script>
            $('#update_form').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                if ($("#update_form").validate().form()) {
                    $.ajax({
                        type: 'POST',
                        url: $(this).attr('action'),
                        data: formData,
                        beforeSend: function () {
                            $('#submit_btn').html('Please wait..');
                        },
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (result) {
                            $('#result').html(result);
                            $('#result').show().delay(3000).fadeOut();
                            $('#submit_btn').html('Submit');
                            $('#users_list').bootstrapTable('refresh');
                            setTimeout(function () {
                                $('#editUserModal').modal('hide');
                            }, 4000);
                        }
                    });
                }
            });
        </script>
    </body>
</html>