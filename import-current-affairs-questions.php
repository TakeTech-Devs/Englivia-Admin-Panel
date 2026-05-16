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
        <title>Import Current Affairs Questions | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
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
                                    <h2>Import Current Affairs Questions</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <div class='row'>
                                        <div class='col-md-12'>
                                            <div class="alert alert-info" style="background-color: #f0f4f7; border: 1px solid #d1dbe5; color: #333;">
                                                <h4 style="color: #2A3F54;"><i class="fa fa-info-circle"></i> Test ID Finder</h4>
                                                <p>Filter by Language and Category to find the Test ID for your CSV file:</p>
                                                <div class="row">
                                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                                        <label>Language</label>
                                                        <?php
                                                        $sql = "SELECT * FROM `languages` ORDER BY id DESC";
                                                        $db->sql($sql);
                                                        $languages = $db->getResult();
                                                        ?>
                                                        <select id='language_selector' class='form-control'>
                                                            <option value=''>Select language</option>
                                                            <?php foreach ($languages as $row) { ?>
                                                                <option value='<?= $row['id'] ?>'><?= $row['language'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                                        <label>Category</label>
                                                        <select id='category_selector' class='form-control'>
                                                            <option value=''>Select Test Category</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                                        <label>Test (Sub Category)</label>
                                                        <select id='test_selector' class='form-control'>
                                                            <option value=''>Select Test</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top:15px;">
                                                        <label>Test ID</label>
                                                        <div class="input-group">
                                                            <input type="text" id="selected_test_id" class="form-control" readonly placeholder="Test ID">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-primary" type="button" onclick="copyToClipboard()">Copy ID</button>
                                                            </span>
                                                        </div>
                                                        <span id="copy_msg" style="display:none; color: #fff; background-color: #169F85; padding: 3px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; margin-top: 8px;"><i class="fa fa-check"></i> ID Copied Successfully!</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br />
                                    <form id="register_form" method="POST" action="db_operations.php" data-parsley-validate class="form-horizontal form-label-left">
                                        <input type="hidden" id="import_current_affairs_questions" name="import_current_affairs_questions" required value='1'/>
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
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-4" style="display:none;" id="result"></div>
                                </div>
                                <div class="ln_solid"></div>
                                <p>Download Sample CSV file</p>
                                <a href="cvfile/current-affairs-sample.csv" class="btn btn-large btn-primary">Download Sample CSV</a>
                                <div class="ln_solid"></div>
                                <div class="col-md-12">
                                    <h4>Instructions for CSV file:</h4>
                                    <ul>
                                        <li>File must be in <b>.csv</b> format.</li>
                                        <li>First line should be the header (Test ID, Question, Option A, Option B, Option C, Option D, Option E, Answer, Duration (Min), Note).</li>
                                        <li><b>Test ID</b> can be found in "Manage Tests" section.</li>
                                        <li><b>Answer</b> should be one of 'a', 'b', 'c', 'd', or 'e'.</li>
                                        <li><b>Duration</b> should be in minutes (e.g. 1).</li>
                                        <li><b>Note for Excel Users:</b> Long Test IDs may appear as "2.00E+13" in Excel. Before saving, please format the Test ID column as a <b>Number with 0 decimal places</b> to ensure the ID is not corrupted.</li>
                                    </ul>
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

        <script>
            $('#language_selector').on('change', function() {
                var language_id = $(this).val();
                if (language_id != '') {
                    $.ajax({
                        type: 'GET',
                        url: 'api/category.php',
                        data: 'type=2&language=' + language_id,
                        beforeSend: function() {
                            $('#category_selector').html('<option value="">Loading Categories...</option>');
                        },
                        success: function(result) {
                            var options = '<option value="">Select Test Category</option>';
                            if (result.data && result.data.length > 0) {
                                $.each(result.data, function(key, val) {
                                    options += '<option value="' + val.id + '">' + val.category_name + '</option>';
                                });
                            } else {
                                options = '<option value="">No Categories Found</option>';
                            }
                            $('#category_selector').html(options);
                            $('#test_selector').html('<option value="">Select Test</option>');
                            $('#selected_test_id').val('');
                        }
                    });
                } else {
                    $('#category_selector').html('<option value="">Select Test Category</option>');
                    $('#test_selector').html('<option value="">Select Test</option>');
                    $('#selected_test_id').val('');
                }
            });

            $('#category_selector').on('change', function() {
                var category_id = $(this).val();
                if (category_id != '') {
                    $.ajax({
                        type: 'GET',
                        url: 'api/subcategory.php',
                        data: 'type=2&category=' + category_id,
                        beforeSend: function() {
                            $('#test_selector').html('<option value="">Loading Tests...</option>');
                        },
                        success: function(result) {
                            var options = '<option value="">Select Test</option>';
                            if (result.data && result.data.length > 0) {
                                $.each(result.data, function(key, val) {
                                    options += '<option value="' + val.id + '">' + val.category_name + '</option>';
                                });
                            } else {
                                options = '<option value="">No Tests Found</option>';
                            }
                            $('#test_selector').html(options);
                            $('#selected_test_id').val('');
                        }
                    });
                } else {
                    $('#test_selector').html('<option value="">Select Test</option>');
                    $('#selected_test_id').val('');
                }
            });

            $('#test_selector').on('change', function() {
                $('#selected_test_id').val($(this).val());
            });

            function copyToClipboard() {
                var copyText = document.getElementById("selected_test_id");
                if (copyText.value == "") {
                    return;
                }
                copyText.select();
                copyText.setSelectionRange(0, 99999); /* For mobile devices */
                navigator.clipboard.writeText(copyText.value);
                
                // Show message
                $('#copy_msg').fadeIn().delay(2000).fadeOut();
            }

            $('#register_form').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
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
            });
        </script>
    </body>
</html>
