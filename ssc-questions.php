<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
    exit();
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
                                <h2>Questions for SSC mock test <small>Create New Question</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="row">
                                    <form id="questionForm" class="form-horizontal form-label-left">
                                        <h4 class="col-md-offset-1"><strong>Create a Question</strong></h4>
                                        <input type="hidden" id="add_question" name="add_question" required="" value="1"
                                            aria-required="true">

                                        <?php $db->sql("SET NAMES 'utf8'");?>

                                        <div class="form-group d-none">
                                            <label class="control-label col-md-1 col-sm-3 col-xs-12"
                                                for="category">Category</label>
                                            <div class="col-md-10 col-sm-6 col-xs-12">
                                                <?php
                                                    $sql = "SELECT * FROM `ssc_category` ORDER BY id DESC";
                                                    $db->sql($sql);
                                                    $categories = $db->getResult();
                                                ?>
                                                <select id="ssc_category_id" name="ssc_category_id" required
                                                    class="form-control">
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($categories as $category) { ?>
                                                    <option value='<?= $category['id'] ?>'>
                                                        <?= $category['category_name'] ?>
                                                    </option>
                                                    <?php } ?>
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
                                                for="answer type">Question
                                                Type</label>
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
                                                for="duration">Duration (In Minutes)</label>
                                            <div class="col-md-10 col-sm-6 col-xs-12">
                                                <input type='text' name='duration' id='duration' class='form-control'
                                                    required>
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
                                        <h2>Questions of SSC mock test <small>View / Update / Delete</small></h2>
                                    </div>
                                    <div class='col-md-12'>
                                        <div class='col-md-3'>
                                            <select id='filter_category' class='form-control' required>
                                                <option value=''>Select Category</option>
                                                <?php foreach ($categories as $row) { ?>
                                                <option value='<?= $row['id'] ?>'><?= $row['category_name'] ?></option>
                                                <?php } ?>
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
                                <div id="toolbar" style="display: none">
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
                                <!-- data-url="get-list.php?table=question" -->
                                <div class="table__container">
                                    <div class="table__header">
                                        <div class="table__title">
                                            <h2>Questions</h2>
                                        </div>

                                    </div>
                                    <div class="table__sub__header">
                                        <div class="table__length" id="table__length">
                                            <label>
                                                Show
                                                <select name="table__length" class="table__length__selector"
                                                    id="question__table__length">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                                Entries
                                            </label>
                                        </div>
                                        <div id="tables_filter" class="tables__filter">
                                            <label>
                                                Search:
                                                <input type="search" id="question__data__search" class="table__search"
                                                    aria-controls="datatables">
                                            </label>
                                        </div>
                                        <div class="table__buttons" style="display:none">
                                            <button class="add_new" id="openAddNewModalBtn"><a
                                                    href="{{ route('admin.question.add.view') }}"><i
                                                        class="fa-solid fa-circle-plus"></i> Add New
                                                    Question</a></button>
                                        </div>
                                    </div>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Id</th>
                                                <th>Category</th>
                                                <th>Question</th>
                                                <th>Optiona</th>
                                                <th>Optionb</th>
                                                <th>Optionc</th>
                                                <th>Optiond</th>
                                                <th>Answer</th>
                                                <th>Duration</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="question_management_table"></tbody>
                                    </table>
                                    <div class="table__clearfix">
                                        <div class="hint-text" id="question__hint__text"></div>
                                        <ul class="pagination" id="question__table__pagination"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Question Modal -->
        <div class="modal fade" id="editQuestionModal" tabindex="-1" role="dialog"
            aria-labelledby="editQuestionModalLabel">
            <div class="modal-dialog" role="document" style="width: 850px;">
                <div class="modal-content" style="border-radius: 10px;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="editQuestionModalLabel">Edit Question Details</h4>
                    </div>
                    <div class="modal-body">
                        <form id="update_form" method="POST" data-parsley-validate
                            class="form-horizontal form-label-left">
                            <input type='hidden' name="question_id" id="question_id" value='' />
                            <input type='hidden' name="update_question" id="update_question" value='1' />

                            <!-- Category Dropdown -->
                            <div class="form-group">
                                <label for="update_category_id"
                                    class="control-label col-md-3 col-sm-3 col-xs-12">Category</label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <?php
                                        $sql = "SELECT * FROM `ssc_category` ORDER BY id DESC";
                                        $db->sql($sql);
                                        $categories = $db->getResult();
                                    ?>
                                    <select id="update_category_id" name="update_category_id" required
                                        class="form-control">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category) { ?>
                                        <option value='<?= $category['id'] ?>'>
                                            <?= $category['category_name'] ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Question -->
                            <div class="form-group">
                                <label for="edit_question"
                                    class="control-label col-md-3 col-sm-3 col-xs-12">Question</label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <textarea id="edit_question" name="edit_question" required
                                        class="form-control"></textarea>
                                </div>
                            </div>

                            <!-- Options A & B -->
                            <div class="form-group">
                                <label for="edit_a" class="control-label col-md-3 col-sm-3 col-xs-12">Option A</label>
                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <input id="edit_a" class="form-control" type="text" name="edit_a">
                                </div>
                                <label for="edit_b" class="control-label col-md-3 col-sm-3 col-xs-12">Option B</label>
                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <input id="edit_b" class="form-control" type="text" name="edit_b">
                                </div>
                            </div>

                            <!-- Options C & D -->
                            <div class="form-group">
                                <label for="edit_c" class="control-label col-md-3 col-sm-3 col-xs-12">Option C</label>
                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <input id="edit_c" class="form-control" type="text" name="edit_c">
                                </div>
                                <label for="edit_d" class="control-label col-md-3 col-sm-3 col-xs-12">Option D</label>
                                <div class="col-md-3 col-sm-3 col-xs-12">
                                    <input id="edit_d" class="form-control" type="text" name="edit_d">
                                </div>
                            </div>

                            <!-- Option E (conditionally shown) -->
                            <?php if ($fn->is_option_e_mode_enabled()) { ?>
                            <div class="form-group">
                                <label for="edit_e" class="control-label col-md-3 col-sm-3 col-xs-12">Option E</label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <input id="edit_e" class="form-control" type="text" name="edit_e">
                                </div>
                            </div>
                            <?php } ?>

                            <!-- Answer -->
                            <div class="form-group">
                                <label for="edit_answer"
                                    class="control-label col-md-3 col-sm-3 col-xs-12">Answer</label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <select name="edit_answer" id="edit_answer" class="form-control" required>
                                        <option value="">Select Right Answer</option>
                                        <option value="a" id="answer__a">A</option>
                                        <option value="b" id="answer__b">B</option>
                                        <option value="c" id="answer__c">C</option>
                                        <option value="d" id="answer__d">D</option>
                                        <?php if ($fn->is_option_e_mode_enabled()) { ?>
                                        <option value="e">E</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <!-- duration -->
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="duration">Duration (In
                                    Minutes)</label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <input type='number' name='duration' id='edit_duration' class='form-control'
                                        required>
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="form-group">
                                <label for="edit_note" class="control-label col-md-3 col-sm-3 col-xs-12">Note</label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <textarea name="edit_note" id="edit_note" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3" style="margin:0 0 0 50%">
                                    <button type="button" id="update_btn" class="btn btn-success">Update
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
        <!-- <?php include 'footer.php'; ?> -->
        <!-- /footer content -->
    </div>

</body>

</html>