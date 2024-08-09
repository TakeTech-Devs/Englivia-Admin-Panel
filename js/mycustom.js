$(document).ready(function () {
  // SSC Question Management
  let question_management_table = document.getElementById(
    "question_management_table"
  );

  if (document.body.contains(question_management_table)) {
    const apiUrl = "http://localhost/cl.englivia.com/api/question.php";

    function fetchQuestions(page, limit, search, category = null) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
      };

      if (category) {
        data.category = category;
      }

      $.ajax({
        url: `${apiUrl}?table=true`,
        method: "GET",
        data: data,
        success: function (data) {
          if (data.response.total !== "0") {
            $("#question_management_table").empty();
            data.response.data.forEach((question, index) => {
              $("#question_management_table").append(`
              <tr>
                  <td>${index + 1}</td>
                  <td>${question.id}</td>
                  <td>${question.ssc_category}</td>
                  <td>${question.question}</td>
                  <td>${question.optiona}</td>
                  <td>${question.optionb}</td>
                  <td>${question.optionc}</td>
                  <td>${question.optiond}</td>
                  <td>${question.answer}</td>
                  <td>${question.duration}</td>
                  <td>
                      <a class='btn btn-xs btn-primary edit-admin' data-id='${
                        question.id
                      }' id='edit_btn' data-toggle='modal' data-target='#editAdminModal' title='Edit'><i class='fas fa-edit'></i></a>
                      <a class='btn btn-xs btn-danger delete-admin' id='delete_btn' data-id='${
                        question.id
                      }' title='Delete'><i class='fas fa-trash'></i></a>
                  </td>
              </tr>
            `);
            });

            // Update pagination
            $("#question__hint__text").text(
              `Showing ${data.response.data.length} out of ${data.response.total} entries`
            );
            renderPagination(
              data.response.page,
              Math.ceil(data.response.total / data.response.limit)
            );
          } else {
            $("#question__hint__text").empty();
            $("#question__table__pagination").empty();
            $("#question_management_table").empty();
            $("#question_management_table").append(`
              <tr>
                <td colspan="10" class="text-center">No questions found</td>
              </tr>
          `);
            console.log("No questions found");
          }
        },
        error: function (error) {
          console.log("Error fetching data", error);
        },
      });
    }

    function renderPagination(currentPage, totalPages) {
      const pagination = $("#question__table__pagination");
      pagination.empty();

      // Previous button
      if (currentPage > 1) {
        pagination.append(`
        <li class="page-item">
          <span class="page-link" data-page="${currentPage - 1}">&laquo;</span>
        </li>
      `);
      }

      if (totalPages <= 5) {
        for (let i = 1; i <= totalPages; i++) {
          pagination.append(`
          <li class="page-item ${i === currentPage ? "active" : ""}">
            <span class="page-link" data-page="${i}">${i}</span>
          </li>
        `);
        }
      } else {
        if (currentPage <= 3) {
          for (let i = 1; i <= 4; i++) {
            pagination.append(`
            <li class="page-item ${i === currentPage ? "active" : ""}">
              <span class="page-link" data-page="${i}">${i}</span>
            </li>
          `);
          }
          pagination.append(
            `<li class="page-item"><span class="page-link">...</span></li>`
          );
          pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="${totalPages}">${totalPages}</span>
          </li>
        `);
        } else if (currentPage > totalPages - 3) {
          pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="1">1</span>
          </li>
        `);
          pagination.append(
            `<li class="page-item"><span class="page-link">...</span></li>`
          );
          for (let i = totalPages - 3; i <= totalPages; i++) {
            pagination.append(`
            <li class="page-item ${i === currentPage ? "active" : ""}">
              <span class="page-link" data-page="${i}">${i}</span>
            </li>
          `);
          }
        } else {
          pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="1">1</span>
          </li>
        `);
          pagination.append(
            `<li class="page-item"><span class="page-link">...</span></li>`
          );
          for (let i = currentPage - 1; i <= currentPage + 1; i++) {
            pagination.append(`
            <li class="page-item ${i === currentPage ? "active" : ""}">
              <span class="page-link" data-page="${i}">${i}</span>
            </li>
          `);
          }
          pagination.append(
            `<li class="page-item"><span class="page-link">...</span></li>`
          );
          pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="${totalPages}">${totalPages}</span>
          </li>
        `);
        }
      }

      // Next button
      if (currentPage < totalPages) {
        pagination.append(`
        <li class="page-item">
          <span class="page-link" data-page="${currentPage + 1}">&raquo;</span>
        </li>
      `);
      }
    }

    // Initial fetch
    fetchQuestions(1, 10, "");

    // Handle pagination click
    $(document).on(
      "click",
      "#question__table__pagination .page-link",
      function () {
        const page = $(this).data("page");
        const limit = $("#question__table__length").val();
        const search = $("#question__data__search").val();
        const category = $("#filter_category").val();
        fetchQuestions(page, limit, search, category);
      }
    );

    // Handle limit change
    $("#question__table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#question__data__search").val();
      const category = $("#filter_category").val();
      fetchQuestions(page, limit, search, category);
    });

    // Handle search
    $("#question__data__search").keyup(function () {
      const page = 1;
      const limit = $("#question__table__length").val();
      const search = $(this).val();
      const category = $("#filter_category").val();
      fetchQuestions(page, limit, search, category);
    });

    // Handle Category Filter
    $("#filter_btn").on("click", function (e) {
      const page = 1;
      const limit = $("#question__table__length").val();
      const search = $("#question__data__search").val();
      const category = $("#filter_category").val();
      console.log(page, limit, search, category);
      fetchQuestions(page, limit, search, category);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const questionId = $(this).data("id");
      if (confirm("Are you sure you want to delete this question?")) {
        $.ajax({
          url: apiUrl,
          method: "DELETE",
          contentType: "application/json",
          data: JSON.stringify({ id: questionId }),
          success: function (response) {
            if (response.status === 200) {
              alert("Question deleted successfully.");
              const page = 1;
              const limit = $("#question__table__length").val();
              const search = $("#question__data__search").val();
              const category = $("#filter_category").val();
              fetchQuestions(page, limit, search, category);
            } else {
              alert("Failed to delete question.");
            }
          },
        });
      }
    });

    // Function to open the modal with preset values
    $(document).on("click", "#edit_btn", function () {
      const questionId = $(this).data("id");
      $.ajax({
        url: `${apiUrl}?id=${questionId}`,
        method: "GET",
        success: function (data) {
          if (data.status === 200) {
            const question = data.response[0];
            $("#question_id").val(question.id);

            // Preselect category
            $("#update_category_id option").each(function () {
              if ($(this).val() == question.ssc_category) {
                $(this).attr("selected", "selected");
              } else {
                $(this).removeAttr("selected");
              }
            });

            // Preselect answer
            $("#edit_answer option").each(function () {
              if ($(this).val() == question.answer) {
                $(this).attr("selected", "selected");
              } else {
                $(this).removeAttr("selected");
              }
            });

            $("#edit_question").val(question.question);
            $("#edit_a").val(question.optiona);
            $("#edit_b").val(question.optionb);
            $("#edit_c").val(question.optionc);
            $("#edit_d").val(question.optiond);
            $("#edit_note").val(question.note);
            if (question.optione) {
              $("#edit_e").val(question.optione);
            }
            $("#edit_duration").val(parseInt(question.duration) / 60000);
            $("#editQuestionModal").modal({
              show: true,
              backdrop: "static",
              keyboard: false,
            });
          }
        },
        error: function (error) {
          console.log("Error fetching question data", error);
        },
      });
    });

    // jQuery AJAX for updating the data
    $("#update_btn").on("click", function () {
      const questionId = $("#question_id").val();
      const duration = parseInt($("#edit_duration").val());
      console.log(duration);
      const formData = {
        ssc_category: $("#update_category_id").val(),
        question: $("#edit_question").val(),
        optiona: $("#edit_a").val(),
        optionb: $("#edit_b").val(),
        optionc: $("#edit_c").val(),
        optiond: $("#edit_d").val(),
        answer: $("#edit_answer").val(),
        duration: $("#edit_duration").val(),
        note: $("#edit_note").val(),
      };
      if ($("#edit_e").val()) {
        formData.optione = $("#edit_e").val();
      }
      console.log(formData);

      $.ajax({
        url: `${apiUrl}?id=${questionId}`,
        method: "PUT",
        contentType: "application/json",
        data: JSON.stringify(formData),
        success: function (data) {
          console.log(data);
          if (data.status === 200) {
            $("#update_result")
              .html(
                '<div class="alert alert-success">' +
                  data.response.message +
                  "</div>"
              )
              .show();
            setTimeout(function () {
              $("#update_result").hide();
              $("#editQuestionModal").modal("hide");

              const page = $("#question__table__pagination .active span").data(
                "page"
              );
              const limit = $("#question__table__length").val();
              const search = $("#question__data__search").val();
              const category = $("#filter_category").val();
              fetchQuestions(page, limit, search, category);
            }, 2000);
          } else {
            $("#update_result")
              .html(
                '<div class="alert alert-danger">' +
                  data.response.error +
                  "</div>"
              )
              .show();
          }
        },
        error: function (error) {
          console.log("Error updating question data", error);
        },
      });
    });

    // Add New Question
    $("#questionForm").validate({
      rules: {
        ssc_category_id: "required",
        question: {
          required: true,
          minlength: 10,
        },
        a: "required",
        b: "required",
        c: "required",
        d: "required",
        answer: "required",
      },
      messages: {
        ssc_category_id: "Please select a category",
        question: {
          required: "Please enter a question",
          minlength: "Your question must be at least 10 characters long",
        },
        a: "Please enter option A",
        b: "Please enter option B",
        c: "Please enter option C",
        d: "Please enter option D",
        answer: "Please select the correct answer",
      },
      submitHandler: function (form) {
        var data = {
          ssc_category: $("#ssc_category_id").val(),
          image: $("#image").val() || null,
          question: $("#question").val(),
          optiona: $("#a").val(),
          optionb: $("#b").val(),
          optionc: $("#c").val(),
          optiond: $("#d").val(),
          optione: $("#e").val() || null,
          answer: $("#answer").val(),
          duration: $("#duration").val(),
          note: $("#note").val() || null,
        };

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            const page = 1;
            const limit = $("#question__table__length").val();
            const search = $("#question__data__search").val();
            const category = $("#filter_category").val();
            fetchQuestions(page, limit, search, category);

            alert(response.response.message);
          },
          error: function (xhr, status, error) {
            console.error("Submission failed:", error);
            console.error("Response:", xhr.responseText);
          },
        });
      },
    });
  }
  // End of SSC Question Management

  // SSC Category Management
  let ssc_category_management_table = document.getElementById(
    "ssc_category_management_table"
  );

  if (document.body.contains(ssc_category_management_table)) {
    const apiUrl = "http://localhost/cl.englivia.com/api/category.php";

    function fetchSscCategories(page, limit, search, category = null) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
      };

      if (category) {
        data.category = category;
      }

      $.ajax({
        url: `${apiUrl}?table=true`,
        method: "GET",
        data: data,
        success: function (data) {
          if (data.response.total !== "0") {
            $("#ssc_category_management_table").empty();
            data.response.data.forEach((category, index) => {
              $("#ssc_category_management_table").append(`
              <tr>
                  <td>${index + 1}</td>
                  <td>${category.id}</td>
                  <td style="min-width:100px">${category.category_name}</td>
                  <td style="width:700px">${Object.values(
                    category.instructions
                  ).map((instruction) => {
                    return `${instruction}<br>`;
                  })}</td>
                  <td>${category.questions}</td>
                  <td>${category.total_duration}</td>
                  <td>${category.status == 1 ? "Active" : "Deactive"}</td>
                  <td style="width:80px">
                      <a class='btn btn-xs btn-primary edit-admin' data-id='${
                        category.id
                      }' id='edit_btn' data-toggle='modal' data-target='#editAdminModal' title='Edit'><i class='fas fa-edit'></i></a>
                      <a class='btn btn-xs btn-danger delete-admin' id='delete_btn' data-id='${
                        category.id
                      }' title='Delete'><i class='fas fa-trash'></i></a>
                  </td>
              </tr>
            `);
            });

            // Update pagination
            $("#ssc_category__hint__text").text(
              `Showing ${data.response.data.length} out of ${data.response.total} entries`
            );
            renderPagination(
              data.response.page,
              Math.ceil(data.response.total / data.response.limit)
            );
          } else {
            $("#ssc_category__hint__text").empty();
            $("#ssc_category__table__pagination").empty();
            $("#ssc_category_management_table").empty();
            $("#ssc_category_management_table").append(`
              <tr>
                <td colspan="10" class="text-center">No category found</td>
              </tr>
          `);
            console.log("No category found");
          }
        },
        error: function (error) {
          console.log("Error fetching data", error);
        },
      });
    }

    function renderPagination(currentPage, totalPages) {
      const pagination = $("#ssc_category__table__pagination");
      pagination.empty();

      // Previous button
      if (currentPage > 1) {
        pagination.append(`
        <li class="page-item">
          <span class="page-link" data-page="${currentPage - 1}">&laquo;</span>
        </li>
      `);
      }

      if (totalPages <= 5) {
        for (let i = 1; i <= totalPages; i++) {
          pagination.append(`
          <li class="page-item ${i === currentPage ? "active" : ""}">
            <span class="page-link" data-page="${i}">${i}</span>
          </li>
        `);
        }
      } else {
        if (currentPage <= 3) {
          for (let i = 1; i <= 4; i++) {
            pagination.append(`
            <li class="page-item ${i === currentPage ? "active" : ""}">
              <span class="page-link" data-page="${i}">${i}</span>
            </li>
          `);
          }
          pagination.append(
            `<li class="page-item"><span class="page-link">...</span></li>`
          );
          pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="${totalPages}">${totalPages}</span>
          </li>
        `);
        } else if (currentPage > totalPages - 3) {
          pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="1">1</span>
          </li>
        `);
          pagination.append(
            `<li class="page-item"><span class="page-link">...</span></li>`
          );
          for (let i = totalPages - 3; i <= totalPages; i++) {
            pagination.append(`
            <li class="page-item ${i === currentPage ? "active" : ""}">
              <span class="page-link" data-page="${i}">${i}</span>
            </li>
          `);
          }
        } else {
          pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="1">1</span>
          </li>
        `);
          pagination.append(
            `<li class="page-item"><span class="page-link">...</span></li>`
          );
          for (let i = currentPage - 1; i <= currentPage + 1; i++) {
            pagination.append(`
            <li class="page-item ${i === currentPage ? "active" : ""}">
              <span class="page-link" data-page="${i}">${i}</span>
            </li>
          `);
          }
          pagination.append(
            `<li class="page-item"><span class="page-link">...</span></li>`
          );
          pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="${totalPages}">${totalPages}</span>
          </li>
        `);
        }
      }

      // Next button
      if (currentPage < totalPages) {
        pagination.append(`
        <li class="page-item">
          <span class="page-link" data-page="${currentPage + 1}">&raquo;</span>
        </li>
      `);
      }
    }

    // Initial fetch
    fetchSscCategories(1, 5, "");

    // Handle pagination click
    $(document).on(
      "click",
      "#ssc_category__table__pagination .page-link",
      function () {
        const page = $(this).data("page");
        const limit = $("#ssc_category__table__length").val();
        const search = $("#ssc_category__data__search").val();
        fetchSscCategories(page, limit, search);
      }
    );

    // Handle limit change
    $("#ssc_category__table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#ssc_category__data__search").val();
      fetchSscCategories(page, limit, search);
    });

    // Handle search
    $("#ssc_category__data__search").keyup(function () {
      const page = 1;
      const limit = $("#ssc_category__table__length").val();
      const search = $(this).val();
      fetchSscCategories(page, limit, search);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const categoryId = $(this).data("id");
      if (
        confirm(
          "By deleting this category all questions under this category will be deleted. Are you sure you want to delete?"
        )
      ) {
        $.ajax({
          url: apiUrl,
          method: "DELETE",
          contentType: "application/json",
          data: JSON.stringify({ id: categoryId }),
          success: function (response) {
            if (response.status === 200) {
              alert("Category deleted successfully.");
              const page = 1;
              const limit = $("#ssc_category__table__length").val();
              const search = $("#ssc_category__data__search").val();
              fetchSscCategories(page, limit, search);
            } else {
              alert("Failed to delete category!");
            }
          },
          error: (error) => {
            console.error(error);
          },
        });
      }
    });

    // Function to open the modal with preset values
    $(document).on("click", "#edit_btn", function () {
      const categoryId = $(this).data("id");
      $.ajax({
        url: `${apiUrl}?id=${categoryId}`,
        method: "GET",
        success: function (data) {
          if (data.status === 200) {
            const category = data.data[0];
            $("#edit_id").val(category.id);

            $("#edit_category_name").val(category.category_name);
            // Set the status radio button
            if (category.status == 1) {
              $("#status_active").prop("checked", true);
              $("#status_active")
                .parent()
                .addClass("btn-primary")
                .removeClass("btn-default");
              $("#status_deactive")
                .parent()
                .removeClass("btn-primary")
                .addClass("btn-default");
            } else {
              $("#status_deactive").prop("checked", true);
              $("#status_deactive")
                .parent()
                .addClass("btn-primary")
                .removeClass("btn-default");
              $("#status_active")
                .parent()
                .removeClass("btn-primary")
                .addClass("btn-default");
            }

            $("#editModal").modal({
              show: true,
              backdrop: "static",
              keyboard: false,
            });
          }
        },
        error: function (error) {
          console.log("Error fetching category data", error);
        },
      });
    });

    // jQuery AJAX for updating the data
    $("#update_btn").on("click", function () {
      const categoryId = $("#edit_id").val();
      const formData = {
        category_name: $("#edit_category_name").val(),
        status: parseInt($("input[name='status']:checked").val()),
      };
      if ($("#edit_instructions").val()) {
        formData[instructions] = $("#edit_instructions").val();
      }

      $.ajax({
        url: `${apiUrl}?id=${categoryId}`,
        method: "PUT",
        contentType: "application/json",
        data: JSON.stringify(formData),
        success: function (data) {
          console.log(data);
          if (data.status === 200) {
            $("#update_result")
              .html(
                '<div class="alert alert-success">' + data.message + "</div>"
              )
              .show();
            setTimeout(function () {
              $("#update_result").hide();
              $("#editModal").modal("hide");

              const page = $(
                "#ssc_category__table__pagination .active span"
              ).data("page");
              const limit = $("#ssc_category__table__length").val();
              const search = $("#ssc_category__data__search").val();
              fetchSscCategories(page, limit, search);
            }, 2000);
          } else {
            $("#update_result")
              .html(
                '<div class="alert alert-danger">' + data.message + "</div>"
              )
              .show();
          }
        },
        error: function (error) {
          console.log("Error updating category data", error);
        },
      });
    });

    // Add New Question
    $("#category_form").validate({
      rules: {
        category_name: "required",
        category_instructions: "required",
      },
      messages: {
        category_name: "Please enter category name",
        category_instructions: "Please enter instructions",
      },
      submitHandler: function (form) {
        var data = {
          category_name: $("#category_name").val(),
          instructions: $("#category_instructions").val() || null,
        };

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            const page = 1;
            const limit = $("#ssc_category__table__length").val();
            const search = $("#ssc_category__data__search").val();
            fetchSscCategories(page, limit, search);

            $("#category_name").val("");
            $("#category_instructions").val("");

            alert(response.message);
          },
          error: function (xhr, status, error) {
            console.error("Submission failed:", error);
            console.error("Response:", xhr.responseText);
          },
        });
      },
    });
  }
});
