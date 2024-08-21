<?php
session_start();
if (!isset($_SESSION['id']) && !isset($_SESSION['username'])) {
    header("location:index.php");
    return false;
    exit();
}
$type = 4;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paragraph Category Management | <?= ucwords($_SESSION['company_name']) ?> - Admin Panel </title>
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
                                <h2>Create Categories</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class='row'>
                                    <div class='col-md-12'>
                                        <form id="category_form" class="form-horizontal form-label-left">
                                            <input type="hidden" id="category_type" name="category_type" value="4">


                                            <div class="form-group row">
                                                <div class="col-md-6 col-sm-12">
                                                    <label for="category_name">Category Name</label>
                                                    <input type="text" id="category_name" name="category_name" required
                                                        class="form-control">
                                                </div>
                                                <div style="display: none">
                                                    <label for="image">Image</label>
                                                    <input type='file' name="image" id="image" class="form-control">
                                                </div>
                                            </div>

                                            <div class="ln_solid"></div>
                                            <div id="result"></div>
                                            <div class="form-group">
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <button type="submit" id="submit_btn" class="btn btn-warning">Add
                                                        New</button>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="col-md-12">
                                            <hr>
                                        </div>
                                    </div>


                                    <div class='col-md-12'>
                                        <!-- <div id="toolbar">
                                            <div class="col-md-3">
                                                <button class="btn btn-danger btn-sm" id="delete_multiple_subcategories"
                                                    title="Delete Selected Subcategories"><em
                                                        class='fa fa-trash'></em></button>
                                            </div>
                                        </div> -->
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
                                                            id="paragraph_translation_category__table__length">
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
                                                        <input type="search" id="paragraph_translation_category__data__search"
                                                            class="table__search" aria-controls="datatables">
                                                    </label>
                                                </div>
                                                <div class="table__buttons" style="display:none">
                                                    <button class="add_new" id="openAddNewModalBtn"><a
                                                            href="{{ route('admin.paragraph_translation_category.add.view') }}"><i
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
                                                        <th>Type</th>
                                                        <th>Instructions</th>
                                                        <th>Total Questions</th>
                                                        <th>Total Duration</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="paragraph_translation_category_management_table"></tbody>
                                            </table>
                                            <div class="table__clearfix">
                                                <div class="hint-text" id="paragraph_translation_category__hint__text"></div>
                                                <ul class="pagination" id="paragraph_translation_category__table__pagination"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Question Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editQuestionModalLabel">
            <div class="modal-dialog" role="document" style="width: 850px;">
                <div class="modal-content" style="border-radius: 10px;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="editQuestionModalLabel">Edit Category Details</h4>
                    </div>
                    <div class="modal-body">
                        <form id="update_form" class="form-horizontal form-label-left">
                            <input type="hidden" id="edit_id" name="edit_id">
                            <input type="hidden" id="edit_type" name="edit_category_type" value="1">

                            <div class="form-group">
                                <label>Category Name</label>
                                <input type="text" name="name" id="edit_category_name" placeholder="Category Name"
                                    class='form-control' required>
                            </div>
                            <div class="form-group">
                                <label>Category Instructions</label>
                                <textarea rows=6 type="text" name="edit_instructions" id="edit_instructions"
                                    placeholder="Category Name" class='form-control'></textarea>
                            </div>
                            <div style="display: none">
                                <label class="" for="image">Image <small>( Leave it blank for no change
                                        )</small></label>
                                <input type="file" name="image" id="edit_image" class="form-control"
                                    aria-required="true">
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div id="status" class="btn-group">
                                        <label class="btn btn-default" data-toggle-class="btn-primary"
                                            data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" id="status_deactive" value="0"> Deactive
                                        </label>
                                        <label class="btn btn-primary" data-toggle-class="btn-primary"
                                            data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" id="status_active" value="1"> Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" id="update_btn" class="btn btn-success">Update</button>
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