<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
}
$type = 1;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Questions for Quiz | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
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
                                <h2>Questions for Quiz <small>Create New Question</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="row">
                                    <form id="register_form" method="POST" action="db_operations.php"
                                        data-parsley-validate="" class="form-horizontal form-label-left"
                                        novalidate="novalidate">
                                        <h4 class="col-md-offset-1"><strong>Create a Question</strong></h4>
                                        <input type="hidden" id="add_question" name="add_question" required="" value="1"
                                            aria-required="true">
                                        <?php
                                        $db->sql("SET NAMES 'utf8'");
                                        if ($fn->is_language_mode_enabled()) {
                                            ?>
                                            <div class="form-group">
                                                <label class="control-label col-md-1 col-sm-3 col-xs-12"
                                                    for="category">Language</label>
                                                <div class="col-md-10 col-sm-6 col-xs-12">
                                                    <?php
                                                    $sql = "SELECT * FROM `languages` ORDER BY id DESC";
                                                    $db->sql($sql);
                                                    $languages = $db->getResult();
                                                    ?>
                                                    <select id="language_id" name="language_id" required
                                                        class="form-control">
                                                        <option value="">Select language</option>
                                                        <?php foreach ($languages as $language) { ?>
                                                            <option value='<?= $language['id'] ?>'><?= $language['language'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="form-group">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12"
                                                for="category">Subjects</label>
                                            <div class="col-md-4 col-sm-6 col-xs-12">
                                                <?php
                                                $sql = "SELECT id, category_name FROM category WHERE type=" . $type . " ORDER BY id DESC";
                                                $db->sql($sql);
                                                $categories = $db->getResult();
                                                ?>
                                                <select name='category' id='category' class='form-control' required>
                                                    <option value=''>Select Subjects</option>
                                                    <?php foreach ($categories as $row) { ?>
                                                        <option value='<?= $row['id'] ?>'><?= $row['category_name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <label class="control-label col-md-2 col-sm-3 col-xs-12"
                                                for="subcategory">Sections</label>
                                            <div class="col-md-4 col-sm-6 col-xs-12">
                                                <select name='subcategory' id='subcategory' class='form-control'>
                                                    <option value=''>Select Sections</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12"
                                                for="question">Question</label>
                                            <div class="col-md-10 col-sm-6 col-xs-12">
                                                <textarea id="question" name="question" class="form-control"
                                                    required></textarea>
                                            </div>
                                        </div>
                                        <div style="display: none">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12" for="image">Image
                                                for Question <small>( if any )</small></label>
                                            <div class="col-md-10 col-sm-6 col-xs-12">
                                                <input type="file" id="image" name="image" class="form-control"
                                                    aria-required="true">
                                            </div>
                                        </div>
                                        <div style="display: none">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12"
                                                for="answer type">Question Type</label>
                                            <div class="col-md-8 col-sm-6 col-xs-12">
                                                <div id="status" class="btn-group">
                                                    <label class="btn btn-default" data-toggle-class="btn-primary"
                                                        data-toggle-passive-class="btn-default">
                                                        <input type="radio" name="question_type" value="1" checked="">
                                                        Options
                                                    </label>
                                                    <!-- <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                            <input type="radio" name="question_type" value="2"> True / False
                                                        </label> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12"
                                                for="a">Options</label>
                                            <div class="col-md-8 col-sm-6 col-xs-12"></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="a" class="control-label col-md-1 col-sm-3 col-xs-12">A</label>
                                            <div class="col-md-4 col-sm-6 col-xs-12">
                                                <input id="a" class="form-control" type="text" value="" name="a">
                                            </div>
                                            <label for="b" class="control-label col-md-1 col-sm-3 col-xs-12">B</label>
                                            <div class="col-md-5 col-sm-6 col-xs-12">
                                                <input id="b" class="form-control" type="text" name="b">
                                            </div>
                                        </div>
                                        <div id="tf">
                                            <div class="form-group">
                                                <label for="c"
                                                    class="control-label col-md-1 col-sm-3 col-xs-12">C</label>
                                                <div class="col-md-4 col-sm-6 col-xs-12">
                                                    <input id="c" class="form-control" type="text" name="c">
                                                </div>
                                                <label for="d"
                                                    class="control-label col-md-1 col-sm-3 col-xs-12">D</label>
                                                <div class="col-md-5 col-sm-6 col-xs-12">
                                                    <input id="d" class="form-control" type="text" name="d">
                                                </div>
                                            </div>
                                            <?php if ($fn->is_option_e_mode_enabled()) { ?>
                                                <div class="form-group">
                                                    <label for="e" class="control-label col-md-1 col-sm-3 col-xs-12">E
                                                    </label>
                                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                                        <input id="e" class="form-control" type="text" name="e">
                                                    </div>
                                                    <label for="d"
                                                        class="control-label col-md-1 col-sm-3 col-xs-12"></label>
                                                    <div class="col-md-5 col-sm-6 col-xs-12">
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12" for="level">Level
                                                (Number Only)</label>
                                            <div class="col-md-10 col-sm-6 col-xs-12">
                                                <input type='text' name='level' id='level' class='form-control'
                                                    required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12"
                                                for="answer">Answer</label>
                                            <div class="col-md-10 col-sm-6 col-xs-12">
                                                <select name='answer' id='answer' class='form-control'>
                                                    <option value=''>Select Right Answer</option>
                                                    <option value='a'>A</option>
                                                    <option value='b'>B</option>
                                                    <option class='ntf' value='c'>C</option>
                                                    <option class='ntf' value='d'>D</option>
                                                    <?php if ($fn->is_option_e_mode_enabled()) { ?>
                                                        <option class='ntf' value='e'>E</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12"
                                                for="note">Note</label>
                                            <div class="col-md-10 col-sm-6 col-xs-12">
                                                <textarea name='note' id='note' class='form-control'></textarea>
                                            </div>
                                        </div>

                                        <div class="ln_solid"></div>
                                        <div class="form-group">
                                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-1">
                                                <button type="submit" id="submit_btn" class="btn btn-success">Create
                                                    Now</button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-4" style="display:none;" id="result">
                                            </div>
                                        </div>
                                    </form>
                                    <div class="col-md-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-12'>
                                        <h2>Questions of Quiz <small>View / Update / Delete</small></h2>
                                    </div>
                                    <div class='col-md-12'>
                                        <?php if ($fn->is_language_mode_enabled()) { ?>
                                            <div class='col-md-3'>
                                                <select id='filter_language' class='form-control' required>
                                                    <option value="">Select language</option>
                                                    <?php foreach ($languages as $language) { ?>
                                                        <option value='<?= $language['id'] ?>'><?= $language['language'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class='col-md-3'>
                                                <select id='filter_category' class='form-control' required>
                                                    <option value=''>Select Subjects</option>
                                                </select>
                                            </div>
                                        <?php } else { ?>
                                            <div class='col-md-3'>
                                                <select id='filter_category' class='form-control' required>
                                                    <option value=''>Select Subjects</option>
                                                    <?php foreach ($categories as $row) { ?>
                                                        <option value='<?= $row['id'] ?>'><?= $row['category_name'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        <?php } ?>
                                        <div class='col-md-3'>
                                            <select id='filter_subcategory' class='form-control' required>
                                                <option value=''>Select Subjects</option>
                                            </select>
                                        </div>

                                        <div class='col-md-3'>
                                            <button class='btn btn-primary btn-block' id='filter_btn'>Filter
                                                Questions</button>
                                        </div>
                                    </div>
                                    <div class='col-md-12'>
                                        <hr>
                                    </div>
                                </div>
                                <div id="toolbar">
                                    <div class="col-md-3">
                                        <button class="btn btn-danger btn-sm" id="delete_multiple_questions"
                                            title="Delete Selected Questions"><em class='fa fa-trash'></em></button>
                                    </div>
                                    <div style="display: none">
                                        <select id='export_select' class="form-control">
                                            <option value="basic">Export This Page</option>
                                            <!--<option value="all">Export All</option>-->
                                            <option value="selected">Export Selected</option>
                                        </select>
                                    </div>
                                </div>
                                <table aria-describedby="mydesc" class='table-striped' id='questions'
                                    data-toggle="table" data-url="get-list.php?table=question" data-sort-name="id"
                                    data-sort-order="desc" data-click-to-select="true" data-side-pagination="server"
                                    data-search="true" data-show-columns="false" data-show-refresh="true"
                                    data-trim-on-search="false" data-toolbar="#toolbar" data-mobile-responsive="true"
                                    data-maintain-selected="true" data-pagination="true" data-page-list="[50, 100, 200]"
                                    data-show-export="false" data-export-types='["txt","excel"]' data-export-options='{
                                           "fileName": "questions-list-<?= date('d-m-y') ?>",
                                           "ignoreColumn": ["state"]	
                                           }' data-query-params="queryParams_1">
                                    <thead>
                                        <tr>
                                            <th scope="col" data-field="state" data-checkbox="true"></th>
                                            <th scope="col" data-field="id" data-sortable="true">ID</th>
                                            <th scope="col" data-field="category" data-sortable="true"
                                                data-visible='false'>Subjects</th>
                                            <th scope="col" data-field="subcategory" data-sortable="true"
                                                data-visible='false'>Sections</th>
                                            <?php if ($fn->is_language_mode_enabled()) { ?>
                                                <th scope="col" data-field="language_id" data-sortable="true"
                                                    data-visible='false'>Language ID</th>
                                                <th scope="col" data-field="language" data-sortable="true"
                                                    data-visible='true'>Language</th>
                                            <?php } ?>
                                            <th scope="col" data-field="image" data-sortable="false"
                                                data-visible='false'>Image</th>
                                            <th scope="col" data-field="question" data-sortable="true">Question</th>
                                            <th scope="col" data-field="question_type" data-sortable="true"
                                                data-visible='false'>Question Type</th>
                                            <th scope="col" data-field="optiona" data-sortable="true">Option A</th>
                                            <th scope="col" data-field="optionb" data-sortable="true">Option B</th>
                                            <th scope="col" data-field="optionc" data-sortable="true">Option C</th>
                                            <th scope="col" data-field="optiond" data-sortable="true">Option D</th>
                                            <?php if ($fn->is_option_e_mode_enabled()) { ?>
                                                <th scope="col" data-field="optione" data-sortable="true"
                                                    data-visible='false'>Option E</th>
                                            <?php } ?>
                                            <th scope="col" data-field="answer" data-sortable="true">Answer</th>
                                            <th scope="col" data-field="level" data-sortable="true">Level</th>
                                            <th scope="col" data-field="note" data-sortable="true" data-visible='true'>
                                                Note</th>
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
        <div class="modal fade" id='editQuestionModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Edit Question Details</h4>
                    </div>
                    <div class="modal-body">
                        <form id="update_form" method="POST" action="db_operations.php" data-parsley-validate
                            class="form-horizontal form-label-left">
                            <input type='hidden' name="question_id" id="question_id" value='' />
                            <input type='hidden' name="update_question" id="update_question" value='1' />
                            <input type='hidden' name="image_url" id="image_url" value='' />
                            <?php
                            $db->sql("SET NAMES 'utf8'");
                            if ($fn->is_language_mode_enabled()) {
                                ?>
                                <div class="form-group">
                                    <label class="control-label col-md-1 col-sm-3 col-xs-12" for="category">Language</label>
                                    <div class="col-md-10 col-sm-6 col-xs-12">
                                        <?php
                                        $sql = "SELECT * FROM `languages` ORDER BY id DESC";
                                        $db->sql($sql);
                                        $languages = $db->getResult();
                                        ?>
                                        <select id="update_language_id" name="update_language_id" required
                                            class="form-control col-md-7 col-xs-12">
                                            <option value="">Select language</option>
                                            <?php foreach ($languages as $language) { ?>
                                                <option value='<?= $language['id'] ?>'><?= $language['language'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="form-group">
                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="category">Subjects</label>
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <select name='edit_category' id='edit_category' class='form-control' required>
                                        <option value=''>Select Subjects</option>
                                        <?php foreach ($categories as $row) { ?>
                                            <option value='<?= $row['id'] ?>'><?= $row['category_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="control-label col-md-2 col-sm-3 col-xs-12"
                                    for="subcategory">Sections</label>
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <select name="edit_subcategory" id="edit_subcategory" class="form-control">
                                        <option value="">Select Sections</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-5 col-sm-3 col-xs-12" for="image">Image for Question <small>( Leave
                                        it blank for no change )</small></label>
                                <div class="col-md-10 col-md-offset-1 col-sm-6 col-xs-12">
                                    <input type="file" id="edit_image" name="image"
                                        class="form-control col-md-7 col-xs-12" aria-required="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="question">Question</label>
                                <div class="col-md-10 col-sm-6 col-xs-12">
                                    <textarea type="text" id="edit_question" name="edit_question" required="required"
                                        class="form-control col-md-7 col-xs-12" aria-required="true"></textarea>
                                </div>
                            </div>

                            <div style="display:none">
                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="answer type">Question
                                    Type</label>
                                <div class="col-md-8 col-sm-6 col-xs-12">
                                    <div id="status" class="btn-group">
                                        <label class="btn btn-default" data-toggle-class="btn-primary"
                                            data-toggle-passive-class="btn-default">
                                            <input type="radio" id="edit_question_type" name="edit_question_type"
                                                value="1" checked=""> Options
                                        </label>
                                        <label class="btn btn-default" data-toggle-class="btn-primary"
                                            data-toggle-passive-class="btn-default">
                                            <input type="radio" id="edit_question_type" name="edit_question_type"
                                                value="2"> True / False
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="a">Options</label>
                                <div class="col-md-8 col-sm-6 col-xs-12"></div>
                            </div>
                            <div class="form-group">
                                <label for="a" class="control-label col-md-1 col-sm-3 col-xs-12">A</label>
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <input id="edit_a" class="form-control col-md-7 col-xs-12" type="text"
                                        name="edit_a">
                                </div>
                                <label for="b" class="control-label col-md-1 col-sm-3 col-xs-12">B</label>
                                <div class="col-md-5 col-sm-6 col-xs-12">
                                    <input id="edit_b" class="form-control col-md-7 col-xs-12" type="text"
                                        name="edit_b">
                                </div>
                            </div>
                            <div id="edit_tf">
                                <div class="form-group">
                                    <label for="c" class="control-label col-md-1 col-sm-3 col-xs-12">C</label>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <input id="edit_c" class="form-control col-md-7 col-xs-12" type="text"
                                            name="edit_c">
                                    </div>
                                    <label for="d" class="control-label col-md-1 col-sm-3 col-xs-12">D</label>
                                    <div class="col-md-5 col-sm-6 col-xs-12">
                                        <input id="edit_d" class="form-control col-md-7 col-xs-12" type="text"
                                            name="edit_d">
                                    </div>
                                </div>
                                <?php if ($fn->is_option_e_mode_enabled()) { ?>
                                    <div class="form-group">
                                        <label for="e" class="control-label col-md-1 col-sm-3 col-xs-12">E</label>
                                        <div class="col-md-4 col-sm-6 col-xs-12">
                                            <input id="edit_e" class="form-control col-md-7 col-xs-12" type="text"
                                                name="edit_e">
                                        </div>
                                        <label class="control-label col-md-1 col-sm-3 col-xs-12"></label>
                                        <div class="col-md-5 col-sm-6 col-xs-12"></div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="level">Level</label>
                                <div class="col-md-10 col-sm-6 col-xs-12">
                                    <input type="text" id="edit_level" name="edit_level" required="required"
                                        class="form-control col-md-7 col-xs-12" aria-required="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="answer">Answer</label>
                                <div class="col-md-10 col-sm-6 col-xs-12">
                                    <select name="edit_answer" id="edit_answer" class="form-control" required>
                                        <option value="">Select Right Answer</option>
                                        <option value="a">A</option>
                                        <option value="b">B</option>
                                        <option class='edit_ntf' value="c">C</option>
                                        <option class='edit_ntf' value="d">D</option>
                                        <?php if ($fn->is_option_e_mode_enabled()) { ?>
                                            <option class='edit_ntf' value='e'>E</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-1 col-sm-3 col-xs-12" for="note">Note</label>
                                <div class="col-md-10 col-sm-6 col-xs-12">
                                    <textarea name="edit_note" id="edit_note" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" id="update_btn" class="btn btn-success">Update
                                        Question</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-offset-3 col-md-8" style="display:none;" id="update_result"></div>
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
        var type = <?= $type ?>;
        <?php if ($fn->is_language_mode_enabled()) { ?>
            $('#language_id').on('change', function (e) {
                var language_id = $('#language_id').val();
                $.ajax({
                    type: 'POST',
                    url: "db_operations.php",
                    data: 'get_categories_of_language=1&language_id=' + language_id + '&type=' + type,
                    beforeSend: function () {
                        $('#category').html('Please wait..');
                    },
                    success: function (result) {
                        $('#category').html(result);
                    }
                });
            });
            $('#update_language_id').on('change', function (e, row_language_id, row_category, row_subcategory) {
                var language_id = $('#update_language_id').val();
                $.ajax({
                    type: 'POST',
                    url: "db_operations.php",
                    data: 'get_categories_of_language=1&language_id=' + language_id + '&type=' + type,
                    beforeSend: function () {
                        $('#edit_category').html('Please wait..');
                    },
                    success: function (result) {
                        $('#edit_category').html(result).trigger("change");
                        if (language_id == row_language_id && row_category != 0)
                            $('#edit_category').val(row_category).trigger("change", [row_category, row_subcategory]);
                    }
                });
            });
            $('#filter_language').on('change', function (e) {
                var language_id = $('#filter_language').val();
                $.ajax({
                    type: 'POST',
                    url: "db_operations.php",
                    data: 'get_categories_of_language=1&language_id=' + language_id + '&type=' + type,
                    beforeSend: function () {
                        $('#filter_category').html('<option>Please wait..</option>');
                    },
                    success: function (result) {
                        $('#filter_category').html(result);
                        $('#filter_subcategory').html('<option>Select Sections</option>');
                    }
                });
            });
            category_options = '';
            <?php
            $category_options = "<option value=''>Select Options</option>";
            foreach ($categories as $category) {
                $category_options .= "<option value='" . $category['id'] . "'>" . $category['category_name'] . "</option>";
            }
            ?>
            category_options = "<?= $category_options; ?>";

        <?php } ?>
    </script>
    <script>
        $('#category').on('change', function (e) {
            var category_id = $('#category').val();
            $.ajax({
                type: 'POST',
                url: "db_operations.php",
                data: 'get_subcategories_of_category=1&category_id=' + category_id,
                beforeSend: function () {
                    $('#subcategory').html('Please wait..');
                },
                success: function (result) {
                    console.log(result);

                    $('#subcategory').html(result);
                }
            });
        });
        $('#edit_category').on('change', function (e, row_category, row_subcategroy) {
            var category_id = $('#edit_category').val();
            $.ajax({
                type: 'POST',
                url: "db_operations.php",
                data: 'get_subcategories_of_category=1&category_id=' + category_id,
                beforeSend: function () {
                    $('#edit_subcategory').html('Please wait..');
                },
                success: function (result) {
                    $('#edit_subcategory').html(result);
                    if (category_id == row_category && row_subcategroy != 0)
                        $('#edit_subcategory').val(row_subcategroy);
                }
            });
        });

        $('#filter_category').on('change', function (e) {
            var category_id = $('#filter_category').val();
            $.ajax({
                type: 'POST',
                url: "db_operations.php",
                data: 'get_subcategories_of_category=1&category_id=' + category_id,
                beforeSend: function () {
                    $('#filter_subcategory').html('<option>Please wait..</option>');
                },
                success: function (result) {
                    $('#filter_subcategory').html(result);
                }
            });
        });
    </script>

    <script>
        window.actionEvents = {
            'click .edit-question': function (e, value, row, index) {

                $('#question_id').val(row.id);
                if (row.image != 'No image') {
                    var regex = /<img.*?src="(.*?)"/;
                    var src = regex.exec(row.image)[1];
                    $('#image_url').val(src);
                } else {
                    $('#image_url').val('');
                }

                $('#edit_question').val(row.question);
                <?php if ($fn->is_language_mode_enabled()) { ?>
                    if (row.language_id == 0) {
                        $('#update_language_id').val(row.language_id);
                        $('#edit_category').html(category_options);
                        $('#edit_category').val(row.category).trigger("change", [row.category, row.subcategory]);
                    } else {
                        $('#update_language_id').val(row.language_id).trigger("change", [row.language_id, row.category, row.subcategory]);
                    }

                <?php } else { ?>
                    $('#edit_category').val(row.category).trigger("change", [row.category, row.subcategory]);
                <?php } ?>
                var question_type = row.question_type;
                if (question_type == "2") {
                    $("input[name=edit_question_type][value=2]").prop('checked', true);
                    $('#edit_tf').hide('fast');
                    $('#edit_a').val(row.optiona);
                    $('#edit_b').val(row.optionb);
                    $('.edit_ntf').hide('fast');
                } else {
                    $("input[name=edit_question_type][value=1]").prop('checked', true);
                    $('#edit_a').val(row.optiona);
                    $('#edit_b').val(row.optionb);
                    $('#edit_c').val(row.optionc);
                    $('#edit_d').val(row.optiond);
                    <?php if ($fn->is_option_e_mode_enabled()) { ?>
                        $('#edit_e').val(row.optione);
                    <?php } ?>
                    $('#edit_tf').show('fast');
                    $('.edit_ntf').show('fast');
                }

                $('#edit_a').val(row.optiona);
                $('#edit_b').val(row.optionb);
                $('#edit_c').val(row.optionc);
                $('#edit_d').val(row.optiond);
                <?php if ($fn->is_option_e_mode_enabled()) { ?>
                    $('#edit_e').val(row.optione);
                <?php } ?>
                $('#edit_answer').val(row.answer.toLowerCase());
                $('#edit_level').val(row.level);
                $('#edit_note').val(row.note);
                $('#edit_subcategory').val(row.subcategory);
            }
        };
    </script>
    <script>
        $(document).on('click', '.delete-question', function () {
            if (confirm('Are you sure? Want to delete question')) {
                id = $(this).data("id");
                image = $(this).data("image");
                $.ajax({
                    url: 'db_operations.php',
                    type: "get",
                    data: 'id=' + id + '&image=' + image + '&delete_question=1',
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
                "language": $('#filter_language').val(),
                "category": $('#filter_category').val(),
                "subcategory": $('#filter_subcategory').val(),
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
                category: "required",
                a: "required",
                b: "required",
                c: "required",
                d: "required",
                level: "required",
                answer: "required"
            }
        });
    </script>
    <script>
        $('#register_form').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            if ($("#register_form").validate().form()) {
                <?php if ($fn->is_language_mode_enabled()) { ?>
                    var language = $('#language_id').val();
                <?php } ?>
                var category = $('#category').val();
                var subcategory = $('#subcategory').val();
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
                        $('#category').val(category);
                        $('#subcategory').val(subcategory);
                        <?php if ($fn->is_language_mode_enabled()) { ?>
                            $('#language_id').val(language);
                        <?php } ?>
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
                        $('#edit_image').val('');
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
            sec = 'question';
            is_image = 1;
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