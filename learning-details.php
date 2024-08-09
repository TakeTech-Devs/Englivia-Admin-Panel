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
        <title>Questions for Learning | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
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
                                    <h2>Add Image For Learnings</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <div class="row">
                                        <form id="register_form" method="POST" action="db_operations.php" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="novalidate">
                                            <h4 class="col-md-offset-1"><strong>Create Learnings</strong></h4>
                                            <input type="hidden" id="add_learning_question" name="add_learning_detail" required value="1">
                                            <input type="hidden" id="learning_id" name="learning_id" value="<?= $_GET['id'] ?>" required>

                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="headline">Learnings Headline</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <textarea id="question" name="headline" class="form-control" required></textarea>
                                                </div>
                                            </div>
                                          <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="headline">Headline Meanings</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <textarea id="qlavel" name="headline_meaning" class="form-control" required></textarea>
                                                </div>
                                            </div>
                                          
                                          <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="detail">Learnings Detail</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <textarea id="question" name="detail" class="form-control" required></textarea>
                                                </div>
                                            </div>
                                          
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="image">Learnings Image</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
 <input type='file' name="image" id="image" class="form-control">                                                </div>
                                            </div>
                                          
                                        
                                           
                                            <div class="ln_solid"></div>
                                            <div class="form-group">
                                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-1">
                                                    <button type="submit" id="submit_btn" class="btn btn-success">Create Now</button>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-offset-3 col-md-4" style ="display:none;" id="result"></div>
                                            </div>
                                        </form>
                                        <div class="col-md-12"><hr></div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-md-12'>
                                            <h2>Learnings List</h2>
                                        </div>
                                        <div class='col-md-12'><hr></div>
                                    </div>
                                  
                                    <table aria-describedby="mydesc" class='table-striped' id='questions'
                                           data-toggle="table" data-url="get-list.php?table=learnning_detail"
                                           data-sort-name="id" data-sort-order="desc"
                                           data-click-to-select="true" data-side-pagination="server"                                           
                                            data-show-columns="true"
                                           data-show-refresh="true" data-trim-on-search="false"                                                    
                                           data-toolbar="#toolbar" data-mobile-responsive="true" data-maintain-selected="true"  
                                           data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"  
                                           data-show-export="false" data-export-types='["txt","excel"]'
                                           data-export-options='{
                                           "fileName": "questions-list-<?= date('d-m-y') ?>",
                                           "ignoreColumn": ["state"]	
                                           }'
                                           data-query-params="queryParams_1"
                                           >
                                        <thead>
                                            <tr>
                                                <th scope="col" data-field="state" data-checkbox="true"></th>
                                                <th scope="col" data-field="id" data-sortable="true">ID</th>
                                                <th scope="col" data-field="learning_id" data-sortable="true">Learning ID</th>
                                                <th scope="col" data-field="headline" data-sortable="true">Headline</th>
                                                <th scope="col" data-field="headline_meaning" data-sortable="true">Headline Meaning</th>
                                                <th scope="col" data-field="detail" data-sortable="true">Detail</th>
                                                <th scope="col" data-field="image" data-sortable="true">Image</th>

                                                <th scope="col" data-field="operate" data-events="actionEvents">Operate</th>

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
            <div class="modal fade" id='editLearningModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Edit Question</h4>
                        </div>
                        <div class="modal-body">
                                                          
                                  <form id="update_form"  method="POST" action ="db_operations.php" data-parsley-validate class="form-horizontal form-label-left">
                                            <h4 class="col-md-offset-1"><strong>Create Learnings</strong></h4>
                                            <input type="hidden" id="update_learning_detail" name="update_learning_detail" required value="1">
                                            <input type="hidden" id="learning_id" name="learning_id" value="<?= $_GET['id'] ?>" required>
											<input type="hidden" id="ld_id" name="ld_id" value="" required>
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="headline">Learnings Headline</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <textarea id="edit_headline" name="edit_headline" class="form-control" required></textarea>
                                                </div>
                                            </div>
                                          <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="headline">Headline Meanings</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <textarea id="edit_headline_meaning" name="edit_headline_meaning" class="form-control" required></textarea>
                                                </div>
                                            </div>
                                          
                                          <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="detail">Learnings Detail</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <textarea id="edit_detail" name="edit_detail" class="form-control" required></textarea>
                                                </div>
                                            </div>
                                          
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="image">Learnings Image</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
 													<input type='file' name="image" id="image" class="form-control">                                               
                                                </div>
                                            </div>
                                          
                                        
                                           
                                            <div class="ln_solid"></div>
                                            <div class="form-group">
                                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-1">
                                                    <button type="submit" id="update_btn" class="btn btn-success">Update Learnings</button>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-offset-3 col-md-4" style ="display:none;" id="result"></div>
                                            </div>
                                        </form>
                          
                            <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="update_result"></div></div>
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
            window.actionEvents = {
                'click .edit-learnings': function (e, value, row, index) {
                    $('#ld_id').val(row.id);
                    $('#edit_headline').val(row.headline);
                    $('#edit_headline_meaning').val(row.headline_meaning);
                    $('#edit_detail').val(row.detail);
                                         		                                                                        
  
                }
            };
        </script>
        <script>
            $(document).on('click', '.delete-question', function () {
                if (confirm('Are you sure? Want to delete detail')) {
                    id = $(this).data("id");
                    $.ajax({
                        url: 'db_operations.php',
                        type: "get",
                        data: 'id=' + id + '&delete_learning_detail=1',
                        success: function (result) {
                            if (result == 1) {
                                $('#questions').bootstrapTable('refresh');
                            } else
                                alert('Error! Question could not be deleted');
                        }
                    });
                }
            });
        </script>    
        <script>
            function queryParams_1(p) {
                return {
                    learning_id:<?= $_GET['id'] ?>,
                    limit: p.limit,
                    sort: p.sort,
                    order: p.order,
                    offset: p.offset,
                    search: p.search
                };
            }
        </script>
        <script>
            var $table = $('#questions');
            $('#toolbar').find('select').change(function () {
                $table.bootstrapTable('refreshOptions', {
                    exportDataType: $(this).val()
                });
            });
        </script>        

        <script>
            $('#register_form').validate({
                rules: {
                    question: "required",
                    a: "required",
                    b: "required",
                    c: "required",
                    d: "required",
                    answer: "required"
                }
            });
        </script>
        <script>
            $('#register_form').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                if ($("#register_form").validate().form()) {
                    $.ajax({
                        type: 'POST',
                        url: $(this).attr('action'),
                        data: formData,
                        beforeSend: function () {
                            $('#submit_btn').html('Please wait..');
                            $('#submit_btn').prop('disabled', true);
                        },
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (result) {
                            $('#submit_btn').html('Create Now');
                            $('#result').html(result);
                            $('#result').show().delay(4000).fadeOut();
                            $('#register_form')[0].reset();
                            $('#tf').show('fast');
                            $('.ntf').show('fast');
                            $('#submit_btn').prop('disabled', false);
                            $('#questions').bootstrapTable('refresh');
                        }
                    });
                }
            });
        </script>

        <script>
            $('#update_form').validate({
                rules: {
                    edit_question: "required",
                    update_quiz_id: "required",
                    update_a: "required",
                    update_b: "required",
                    update_c: "required",
                    update_d: "required",
                    edit_answer: "required"
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
                            $('#update_btn').html('Please wait..');
                        },
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (result) {
                            $('#update_result').html(result);
                            $('#update_result').show().delay(3000).fadeOut();
                            $('#update_btn').html('Update Question');
                            $('#questions').bootstrapTable('refresh');
                            setTimeout(function () {
                                $('#editQuestionModal').modal('hide');
                            }, 4000);
                        }
                    });
                }
            });
        </script>
        <script>
            $('#filter_btn').on('click', function (e) {
                $('#questions').bootstrapTable('refresh');
            });
            $('#delete_multiple_questions').on('click', function (e) {
                sec = 'tbl_learning_question';
                is_image = 0;
                table = $('#questions');
                delete_button = $('#delete_multiple_questions');
                selected = table.bootstrapTable('getAllSelections');
                ids = "";
                $.each(selected, function (i, e) {
                    ids += e.id + ",";
                });
                ids = ids.slice(0, -1); // removes last comma character
                if (ids == "") {
                    alert("Please select some questions to delete!");
                } else {
                    if (confirm("Are you sure you want to delete all selected questions?")) {
                        $.ajax({
                            type: 'GET',
                            url: "db_operations.php",
                            data: 'delete_multiple=1&ids=' + ids + '&sec=' + sec + '&is_image=' + is_image,
                            beforeSend: function () {
                                delete_button.html('<i class="fa fa-spinner fa-pulse"></i>');
                            },
                            success: function (result) {
                                if (result == 1) {
                                    alert("Questions deleted successfully");
                                } else {
                                    ("Could not delete questions. Try again!");
                                }
                                delete_button.html('<i class="fa fa-trash"></i>');
                                table.bootstrapTable('refresh');
                            }
                        });
                    }
                }
            });
        </script>
        <script>
            $('input[name="question_type"]').on("click", function (e) {
                var question_type = $(this).val();
                if (question_type == "2") {
                    $('#tf').hide('fast');
                    $('#a').val("<?php echo $config['true_value'] ?>");
                    $('#b').val("<?php echo $config['false_value'] ?>");
                    $('.ntf').hide('fast');
                } else {
                    $('#a').val('');
                    $('#b').val('');
                    $('#tf').show('fast');
                    $('.ntf').show('fast');
                }
            });
            $('input[name="edit_question_type"]').on("click", function (e) {
                var edit_question_type = $(this).val();
                if (edit_question_type == "2") {
                    $('#edit_tf').hide('fast');
                    $('#edit_a').val("<?php echo $config['true_value'] ?>");
                    $('#edit_b').val("<?php echo $config['false_value'] ?>");
                    $('.edit_ntf').hide('fast');
                    $('#edit_answer').val('');
                } else {
                    $('#edit_tf').show('fast');
                    $('.edit_ntf').show('fast');
                }
            });
        </script>

    </body>
</html>