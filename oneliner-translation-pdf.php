<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:index.php");
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
    <title>PDF Management | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
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
                                <h2>PDF Management <small>Add PDF</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">

                                <div id="toolbar" style="display: none">
                                    <div class="col-md-3">
                                        <button class="btn btn-danger btn-sm" id="delete_multiple_questions"
                                            title="Delete Selected Questions"><em class='fa fa-trash'></em></button>
                                    </div>
                                    <div>
                                        <select id='export_select' class="form-control">
                                            <option value="basic">Export This Page</option>
                                            <option value="all">Export All</option>
                                            <option value="selected">Export Selected</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="table__container">
                                    <!-- <div class="table__header">
                                        <div class="table__title">
                                            <h2>PDF</h2>
                                        </div>

                                    </div> -->
                                    <div class="table__sub__header">
                                        <div class="table__length">
                                            <label>
                                                Show
                                                <select name="table__length" class="table__length__selector"
                                                    id="table__length">
                                                    <option value="5">5</option>
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
                                                <input type="search" id="data__search" class="table__search"
                                                    aria-controls="datatables">
                                            </label>
                                        </div>
                                    </div>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Id</th>
                                                <th>Category</th>
                                                <th>Language</th>
                                                <th>Type</th>
                                                <th>Pdf</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="oneliner_translation_pdf_management_table"></tbody>
                                    </table>
                                    <div class="table__clearfix">
                                        <div class="hint-text" id="table__hint__text"></div>
                                        <ul class="pagination" id="table__pagination"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit PDF Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
            <div class="modal-dialog" role="document" style="width: 850px;">
                <div class="modal-content" style="border-radius: 10px;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="editModalLabel">Edit Question Details</h4>
                    </div>
                    <div class="modal-body">
                        <form id="update_form" method="POST" data-parsley-validate
                            class="form-horizontal form-label-left">
                            <input type='hidden' name="edit_id" id="edit_id" value='' />
                            <input type='hidden' name="update_question" id="update_question" value='4' />

                            <!-- Category Name -->
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12"
                                    for="edit_category_name">Category Name</label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <input type='text' name='edit_category_name' id='edit_category_name'
                                        class='form-control' required>
                                </div>
                            </div>

                            <!-- Pdf File -->
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="edit_category_pdf">Pdf
                                    File</label>
                                <div class="col-md-9 col-sm-9 col-xs-12">
                                    <input type='file' name="edit_category_pdf" id="edit_category_pdf"
                                        class="form-control">
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