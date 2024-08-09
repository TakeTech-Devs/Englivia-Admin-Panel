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
        <title>Import Questions | <?= ucwords($_SESSION['company_name']) ?> Admin Panel  </title>
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
                                    <h2>Import Quiz Zone Questions</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <br />
                                    <form id="register_form"  method="POST" action ="db_operations.php"data-parsley-validate class="form-horizontal form-label-left">
                                        <input type="hidden" id="import_questions" name="import_questions" required value='1'/>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="questions_file"> Select CSV file</label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="file" name="questions_file" id="questions_file" required class="form-control col-md-7 col-xs-12" accept=".csv" />
                                            </div>
                                        </div>
                                        <div class="ln_solid"></div>
                                        <div class="form-group">
                                            <div class="col-md-3 col-sm-6 col-xs-12 col-md-offset-3">
                                                <button type="submit" id="submit_btn" class="btn btn-success">Upload CSV file</button>
                                            </div>
                                           
                                        </div>
                                    </form>                                 
                                </div>
                                <p>Download Empty CSV file</p>
                                  
                                   <a href="https://cl.englivia.com/%20cvfile/1.csv" class="btn btn-large btn-primary ">DownLoad File</a>
                                <div class="row">
                                    <div  class="col-md-offset-3 col-md-4" style ="display:none;" id="result">
                                    </div>
                                </div>
                            </div>
                           
                        </div>
                    </div>
                </div>
            </div>
            <!-- /page content -->
            <!-- footer content -->
            <?php include 'footer.php'; ?>
            <!-- /footer content -->
        </div>

        <!-- jQuery -->
        <script>
            $('#register_form').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
//                if ($("#register_form").validate().form()) {
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    beforeSend: function () {
                        $('#submit_btn').html('Uploading questions..');
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (result) {
                        $('#result').html(result);
                        $('#result').show().delay(6000).fadeOut();
                        $('#submit_btn').html('Upload CSV file');
                        $('#questions_file').val('');
                    }
                });
//                }
            });
        </script>
    </body>
</html>