let grammar_exercise_subcategory_management_table = document.getElementById(
    "grammar_exercise_subcategory_management_table"
);

if (document.body.contains(grammar_exercise_subcategory_management_table)) {
    if (host.includes("localhost")) {
        apiUrl = `${protocol}//${host}/cl.englivia.com/api/subcategory.php`;
    } else {
        apiUrl = `${protocol}//${host}/api/subcategory.php`;
    }

    function fetchcurrent_grammarExerciseSubCategories(page, limit, search) {
        let data = {
            page: page,
            limit: limit,
            search: search,
            type: 9,
        };

        $.ajax({
            url: `${apiUrl}?table`,
            method: "GET",
            data: data,
            success: function (data) {
                console.log("API Response", data); // for debugging

                let parentList = {};
                $("#category_id option").each(function () {
                    parentList[$(this).val()] = $(this).text().trim();
                });

                const hasData =
                    data &&
                    typeof data === "object" &&
                    data.status &&
                    data.response &&
                    Array.isArray(data.response.data) &&
                    data.response.data.length > 0;

                if ((data.status == 206 || data.status == 200) && hasData) {
                    $("#grammar_exercise_subcategory_management_table").empty();
                    data.response.data.forEach((category, index) => {
                        $("#grammar_exercise_subcategory_management_table").append(`
        <tr>
          <td>${index + 1}</td>
          <td>${category.id}</td>
          <td>${parentList[category.category] || category.category}</td>
          <td>${category.category_name}</td>
          <td>${category.type}</td>
          <td>${category.questions}</td>
          <td>${category.total_duration}</td>
          <td>${category.status == 1 ? "Active" : "Deactive"}</td>
          <td>
            <a class='btn btn-xs btn-primary edit-admin' data-id='${category.id}' id='edit_btn' data-toggle='modal' data-target='#editAdminModal' title='Edit'><i class='fas fa-edit'></i></a>
            <a class='btn btn-xs btn-danger delete-admin' id='delete_btn' data-id='${category.id}' title='Delete'><i class='fas fa-trash'></i></a>
          </td>
        </tr>
      `);
                    });

                    $("#table__hint__text").text(
                        `Showing ${data.response.data.length} out of ${data.response.total} entries`
                    );
                    renderPagination(
                        data.response.page,
                        Math.ceil(data.response.total / data.response.limit)
                    );
                } else {
                    $("#table__hint__text").empty();
                    $("#table__pagination").empty();
                    $("#grammar_exercise_subcategory_management_table").html(`
      <tr><td colspan="10" class="text-center">No Sub-category found</td></tr>
    `);
                }
            },


            error: function (error) {
                console.log("Error fetching data", error);
            },
        });
    }

    function renderPagination(currentPage, totalPages) {
        const pagination = $("#table__pagination");
        pagination.empty();

        if (currentPage > 1) {
            pagination.append(`
        <li class="page-item">
          <span class="page-link" data-page="${currentPage - 1}">&laquo;</span>
        </li>
      `);
        }

        const appendPage = (i) => {
            pagination.append(`
        <li class="page-item ${i === currentPage ? "active" : ""}">
          <span class="page-link" data-page="${i}">${i}</span>
        </li>
      `);
        };

        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) appendPage(i);
        } else {
            if (currentPage <= 3) {
                for (let i = 1; i <= 4; i++) appendPage(i);
                pagination.append(`<li class="page-item"><span class="page-link">...</span></li>`);
                appendPage(totalPages);
            } else if (currentPage > totalPages - 3) {
                appendPage(1);
                pagination.append(`<li class="page-item"><span class="page-link">...</span></li>`);
                for (let i = totalPages - 3; i <= totalPages; i++) appendPage(i);
            } else {
                appendPage(1);
                pagination.append(`<li class="page-item"><span class="page-link">...</span></li>`);
                for (let i = currentPage - 1; i <= currentPage + 1; i++) appendPage(i);
                pagination.append(`<li class="page-item"><span class="page-link">...</span></li>`);
                appendPage(totalPages);
            }
        }

        if (currentPage < totalPages) {
            pagination.append(`
        <li class="page-item">
          <span class="page-link" data-page="${currentPage + 1}">&raquo;</span>
        </li>
      `);
        }
    }

    // Initial Fetch
    fetchcurrent_grammarExerciseSubCategories(1, 5, "");

    // Pagination
    $(document).on("click", "#table__pagination .page-link", function () {
        const page = $(this).data("page");
        const limit = $("#table__length").val();
        const search = $("#data__search").val();
        fetchcurrent_grammarExerciseSubCategories(page, limit, search);
    });

    // Limit change
    $("#table__length").change(function () {
        const page = 1;
        const limit = $(this).val();
        const search = $("#data__search").val();
        fetchcurrent_grammarExerciseSubCategories(page, limit, search);
    });

    // Search
    $("#data__search").keyup(function () {
        const page = 1;
        const limit = $("#table__length").val();
        const search = $(this).val();
        fetchcurrent_grammarExerciseSubCategories(page, limit, search);
    });

    // Delete
    $(document).on("click", "#delete_btn", function () {
        const subcategoryId = $(this).data("id");
        if (confirm("Are you sure? This will delete all associated questions.")) {
            $.ajax({
                url: apiUrl,
                method: "DELETE",
                contentType: "application/json",
                data: JSON.stringify({ id: subcategoryId }),
                success: function (response) {
                    if (response.status === 200) {
                        // alert(response.message);
                        alert("Category deleted successfully.");
                        const page = 1;
                        const limit = $("#table__length").val();
                        const search = $("#data__search").val();
                        fetchcurrent_grammarExerciseSubCategories(page, limit, search);
                    } else {
                        alert("Failed to delete category!");
                    }
                },
                error: (err) => console.log(err),
            });
        }
    });

    // Edit
    $(document).on("click", "#edit_btn", function () {
        const id = $(this).data("id");
        $.ajax({
            url: `${apiUrl}?id=${id}`,
            method: "GET",
            success: function (data) {
                const category = data.data[0];
                $("#edit_id").val(category.id);
                $("#edit_category_name").val(category.category_name);
                $("#edit_category_type").val(category.type);
                $("#update_category_id").val(category.category);
                $(`#status_${category.status == 1 ? "active" : "deactive"}`).prop("checked", true);
                $("#editModal").modal({ show: true, backdrop: "static", keyboard: false });
            },
            error: (err) => console.log(err),
        });
    });

    // Update
    $("#update_btn").click(function () {
        const formData = {
            category_name: $("#edit_category_name").val(),
            type: $("#edit_category_type").val(),
            category: $("#update_category_id").val(),
            status: parseInt($("input[name='status']:checked").val()),
        };

        $.ajax({
            url: `${apiUrl}?id=${$("#edit_id").val()}`,
            method: "PUT",
            contentType: "application/json",
            data: JSON.stringify(formData),
            success: function (res) {
                if (res.status === 200) {
                    $("#update_result").html(`<div class="alert alert-success">${res.message}</div>`).show();
                    setTimeout(() => {
                        $("#update_result").hide();
                        $("#editModal").modal("hide");
                        fetchcurrent_grammarExerciseSubCategories(1, $("#table__length").val(), "");
                    }, 2000);
                } else {
                    $("#update_result").html(`<div class="alert alert-danger">${res.message}</div>`).show();
                }
            },
            error: (err) => console.log("Update error:", err),
        });
    });

    // Insert (Manual or CSV)
    $("#category_form").submit(function (e) {
        e.preventDefault();
        const mode = $("#entry_mode option:selected").val(); // hidden field or radio group

        if (mode === "csv") {
            const formData = new FormData(this);
            for (let pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`);
            }
            $.ajax({
                url: apiUrl + "?upload_subcat_csv=1",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (res) {
                    alert(res.message);
                    fetchcurrent_grammarExerciseSubCategories(1, $("#table__length").val(), "");
                },
                error: function (xhr) {
                    console.log("CSV Upload Error", xhr.responseText);
                },
            });
        } else {
            const data = {
                category_name: $("#category_name").val(),
                type: $("#category_type").val(),
                category: $("#category_id").val(),
            };

            $.ajax({
                url: apiUrl,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(data),
                success: function (response) {
                    alert(response.message);
                    fetchcurrent_grammarExerciseSubCategories(1, $("#table__length").val(), "");
                    $("#category_form")[0].reset();
                },
                error: function (xhr) {
                    console.log("Manual Insert Error:", xhr.responseText);
                },
            });
        }
    });
}
