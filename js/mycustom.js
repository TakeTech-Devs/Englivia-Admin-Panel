$(document).ready(function () {
  // Determine the environment and set the API URL
  const protocol = window.location.protocol; // 'http:' or 'https:'
  const host = window.location.host; // 'localhost' or 'cl.englivia.com'

  let apiUrl;
  // Function to fetch languages asynchronously
  async function fetchLanguages() {
    if (host.includes("localhost")) {
      languageApiUrl = `${protocol}//${host}/cl.englivia.com/api/language.php`;
    } else {
      languageApiUrl = `${protocol}//${host}/api/language.php`;
    }

    try {
      const response = await $.ajax({
        url: languageApiUrl,
        method: "GET",
      });
      return response.data;
    } catch (error) {
      console.error("Error fetching languages:", error);
      return [];
    }
  }

  // Function to get language name by ID
  function getLanguage(language) {
    if (language == 0 || language > 3) {
      return "Language not available";
    }
    return languages[language - 1].language;
  }

  // Async IIFE (Immediately Invoked Function Expression) to handle async code
  (async () => {
    languages = await fetchLanguages();
    console.log("langs", languages);
  })();

  // SSC Question Management
  let question_management_table = document.getElementById(
    "question_management_table"
  );

  if (document.body.contains(question_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/question.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/question.php`;
    }

    function fetchQuestions(page, limit, search, category = null) {
      console.log(page, limit, search, category);
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
          console.log(data);
          // List categories
          let categoryList = {};
          $("#ssc_category_id option").each(function () {
            categoryList[$(this).val()] = $(this)
              .text()
              .replace(/\s+/g, " ") // Replace multiple spaces and newlines with a single space
              .trim(); // Trim any leading or trailing spaces
          });

          if (data.response.total !== "0") {
            $("#question_management_table").empty();
            data.response.data.forEach((question, index) => {
              $("#question_management_table").append(`
              <tr>
                  <td>${index + 1}</td>
                  <td>${question.id}</td>
                  <td>${categoryList[question.category_id]}</td>
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
              if ($(this).val() == question.category_id) {
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
      const formData = {
        category_id: $("#update_category_id").val(),
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
          category_id: $("#ssc_category_id").val(),
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
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/category.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/category.php`;
    }

    function fetchSscCategories(page, limit, search, category = null) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 1,
      };

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
                  <td style="min-width:100px">${category.type}</td>
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
        type: $("#edit_category_type").val(),
        status: parseInt($("input[name='status']:checked").val()),
      };
      if ($("#edit_instructions").val()) {
        formData["instructions"] = $("#edit_instructions").val();
      }
      $.ajax({
        url: `${apiUrl}?id=${categoryId}`,
        method: "PUT",
        contentType: "application/json",
        data: JSON.stringify(formData),
        success: function (data) {
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
          type: $("#category_type").val(),
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
  } // End of SSC Category Management

  // Current Affairs Question Management
  let current_affairs_question_management_table = document.getElementById(
    "current_affairs_question_management_table"
  );

  if (document.body.contains(current_affairs_question_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/question.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/question.php`;
    }

    function fetchcurrent_affairsQuestions(
      page,
      limit,
      search,
      category = null
    ) {
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
        url: `${apiUrl}?table&type=2`,
        method: "GET",
        data: data,
        success: function (data) {
          // List Tests
          let testList = {};
          $("#current_affairs_category_id option").each(function () {
            testList[$(this).val()] = $(this)
              .text()
              .replace(/^(ca\s*-\s*)/i, "") // Remove any form of "CA -" at the start (case-insensitive)
              .replace(/\s+/g, " ") // Replace multiple spaces and newlines with a single space
              .trim(); // Trim any leading or trailing spaces
          });
          console.log(testList);

          if (data.response.total !== "0") {
            $("#current_affairs_question_management_table").empty();
            data.response.data.forEach((question, index) => {
              $("#current_affairs_question_management_table").append(`
                
                <tr>
                    <td>${index + 1}</td>
                    <td>${question.id}</td>
                    <td style="min-width: 100px;">${
                      testList[question.category_id]
                    }</td>
                    <td style="min-width: 100px;">${question.category_name}</td>
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
            $("#current_affairs_question_management_table").empty();
            $("#current_affairs_question_management_table").append(`
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
      const pagination = $("#table__pagination");
      pagination.empty();

      // Previous button
      if (currentPage > 1) {
        pagination.append(`
          <li class="page-item">
            <span class="page-link" data-page="${
              currentPage - 1
            }">&laquo;</span>
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
            <span class="page-link" data-page="${
              currentPage + 1
            }">&raquo;</span>
          </li>
        `);
      }
    }

    // Initial fetch
    fetchcurrent_affairsQuestions(1, 10, "");

    // Handle pagination click
    $(document).on("click", "#table__pagination .page-link", function () {
      const page = $(this).data("page");
      const limit = $("#table__length").val();
      const search = $("#data__search").val();
      const category = $("#edit_current_affairs_category_id").val();
      fetchcurrent_affairsQuestions(page, limit, search, category);
    });

    // Handle limit change
    $("#table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#data__search").val();
      const category = $("#edit_current_affairs_category_id").val();
      fetchcurrent_affairsQuestions(page, limit, search, category);
    });

    // Handle search
    $("#data__search").keyup(function () {
      const page = 1;
      const limit = $("#table__length").val();
      const search = $(this).val();
      const category = $("#edit_current_affairs_category_id").val();
      fetchcurrent_affairsQuestions(page, limit, search, category);
    });

    // Handle Category Filter
    $("#filter_btn").on("click", function (e) {
      const page = 1;
      const limit = $("#table__length").val();
      const search = $("#data__search").val();
      const category = $("#edit_current_affairs_category_id").val();
      console.log(page, limit, search, category);
      fetchcurrent_affairsQuestions(page, limit, search, category);
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
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              const category = $("#edit_current_affairs_category_id").val();
              fetchcurrent_affairsQuestions(page, limit, search, category);
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
      console.log(questionId);

      $.ajax({
        url: `${apiUrl}?type=2&id=${questionId}`,
        method: "GET",
        success: function (data) {
          if (data.status === 200) {
            const question = data.response[0];
            $("#current_affairs_question_id").val(question.id);

            // Preselect category
            $("#edit_current_affairs_category_id option").each(function () {
              if ($(this).val() == question.category_id) {
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

            $("#editModal").modal({
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
      const questionId = $("#current_affairs_question_id").val();
      const duration = parseInt($("#edit_duration").val());
      console.log(duration);
      const formData = {
        category_id: $("#update_category_id").val(),
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
              console.log(data);

              $("#update_result").hide();
              $("#editModal").modal("hide");

              const page = $("#table__pagination .active span").data("page");
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              const category = $("#edit_current_affairs_category_id").val();
              fetchcurrent_affairsQuestions(page, limit, search, category);
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
        current_affairs_category_id: "required",
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
        current_affairs_category_id: "Please select a category",
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
          category_id: $("#current_affairs_category_id").val(),
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

        console.log(data);

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            console.log(response);
            const page = 1;
            const limit = $("#table__length").val();
            const search = $("#data__search").val();
            const category = $("#edit_current_affairs_category_id").val();
            fetchcurrent_affairsQuestions(page, limit, search, category);

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
  // End of Current Affairs Question Management

  // Current Affairs Category Management
  let current_affairs_category_management_table = document.getElementById(
    "current_affairs_category_management_table"
  );

  if (document.body.contains(current_affairs_category_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/category.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/category.php`;
    }

    function fetchcurrent_affairsCategories(
      page,
      limit,
      search,
      category = null
    ) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 2,
      };

      $.ajax({
        url: `${apiUrl}?table=true`,
        method: "GET",
        data: data,
        success: function (data) {
          if (data.response.total !== "0") {
            $("#current_affairs_category_management_table").empty();
            data.response.data.forEach((category, index) => {
              $("#current_affairs_category_management_table").append(`
                  <tr>
                      <td>${index + 1}</td>
                      <td>${category.id}</td>
                      <td style="min-width:100px">${category.category_name}</td>
                      <td style="min-width:100px">${
                        category.language == null
                          ? "N/A"
                          : getLanguage(category.language)
                      }</td>
                      <td style="min-width:100px">${category.type}</td>
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
            $("#current_affairs_category_management_table").empty();
            $("#current_affairs_category_management_table").append(`
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
      const pagination = $("#table__pagination");
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
    fetchcurrent_affairsCategories(1, 5, "");

    // Handle pagination click
    $(document).on("click", "#table__pagination .page-link", function () {
      const page = $(this).data("page");
      const limit = $("#table__length").val();
      const search = $("#data__search").val();
      fetchcurrent_affairsCategories(page, limit, search);
    });

    // Handle limit change
    $("#table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#data__search").val();
      fetchcurrent_affairsCategories(page, limit, search);
    });

    // Handle search
    $("#data__search").keyup(function () {
      const page = 1;
      const limit = $("#table__length").val();
      const search = $(this).val();
      fetchcurrent_affairsCategories(page, limit, search);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const categoryId = $(this).data("id");
      if (
        confirm(
          "By deleting this category all questions and PDF under this category will be deleted. Are you sure you want to delete?"
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
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchcurrent_affairsCategories(page, limit, search);
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
        url: `${apiUrl}?type=2&id=${categoryId}`,
        method: "GET",
        success: function (data) {
          console.log(data);

          if (data.status === 200) {
            const category = data.data[0];
            $("#edit_id").val(category.id);

            // Preselect language
            $("#edit_category_language option").each(function () {
              if ($(this).val() == category.language) {
                $(this).attr("selected", "selected");
              } else {
                $(this).removeAttr("selected");
              }
            });

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
        type: $("#edit_category_type").val(),
        language: $("#edit_category_language").val(),
        status: parseInt($("input[name='status']:checked").val()),
      };

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

              const page = $("#table__pagination .active span").data("page");
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchcurrent_affairsCategories(page, limit, search);
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
        category_language: "required",
      },
      messages: {
        category_name: "Please enter category name",
        category_language: "Please select category language",
      },
      submitHandler: function (form) {
        var data = {
          category_name: $("#category_name").val(),
          language: $("#category_language").val(),
          type: $("#category_type").val(),
          tag: $("#category_tag").val(),
        };

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            const page = 1;
            const limit = $("#table__length").val();
            const search = $("#data__search").val();
            fetchcurrent_affairsCategories(page, limit, search);

            $("#category_name").val("");

            alert(response.message);
          },
          error: function (xhr, status, error) {
            console.error("Submission failed:", error);
            console.error("Response:", xhr.responseText);
          },
        });
      },
    });
  } // End of Current Affairs Category Management

  // Current Affairs Subcategory Management
  let current_affairs_subcategory_management_table = document.getElementById(
    "current_affairs_subcategory_management_table"
  );

  if (document.body.contains(current_affairs_subcategory_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/subcategory.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/subcategory.php`;
    }

    function fetchcurrent_affairsCategories(
      page,
      limit,
      search,
      category = null
    ) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 2,
      };

      $.ajax({
        url: `${apiUrl}?table`,
        method: "GET",
        data: data,
        success: function (data) {
          console.log(data.response.data);
          // List Parent Category
          let parentList = {};
          $("#current_affairs_category_id option").each(function () {
            parentList[$(this).val()] = $(this).text().trim();
          });

          if (data.response.total !== "0") {
            $("#current_affairs_subcategory_management_table").empty();
            data.response.data.forEach((category, index) => {
              $("#current_affairs_subcategory_management_table").append(`
              <tr>
                  <td>${index + 1}</td>
                  <td>${category.id}</td>
                  <td style="min-width:100px">${
                    parentList[category.category]
                  }</td>
                  <td style="min-width:100px">${category.category_name}</td>
                  <td style="min-width:100px">${category.type}</td>
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
            $("#current_affairs_category__hint__text").text(
              `Showing ${data.response.data.length} out of ${data.response.total} entries`
            );
            renderPagination(
              data.response.page,
              Math.ceil(data.response.total / data.response.limit)
            );
          } else {
            $("#current_affairs_category__hint__text").empty();
            $("#current_affairs_category__table__pagination").empty();
            $("#current_affairs_subcategory_management_table").empty();
            $("#current_affairs_subcategory_management_table").append(`
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
      const pagination = $("#current_affairs_category__table__pagination");
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
    fetchcurrent_affairsCategories(1, 5, "");

    // Handle pagination click
    $(document).on(
      "click",
      "#current_affairs_category__table__pagination .page-link",
      function () {
        const page = $(this).data("page");
        const limit = $("#current_affairs_category__table__length").val();
        const search = $("#current_affairs_category__data__search").val();
        fetchcurrent_affairsCategories(page, limit, search);
      }
    );

    // Handle limit change
    $("#current_affairs_category__table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#current_affairs_category__data__search").val();
      fetchcurrent_affairsCategories(page, limit, search);
    });

    // Handle search
    $("#current_affairs_category__data__search").keyup(function () {
      const page = 1;
      const limit = $("#current_affairs_category__table__length").val();
      const search = $(this).val();
      fetchcurrent_affairsCategories(page, limit, search);
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
              const limit = $("#current_affairs_category__table__length").val();
              const search = $("#current_affairs_category__data__search").val();
              fetchcurrent_affairsCategories(page, limit, search);
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

            // Preselect category
            $("#update_current_affairs_category_id option").each(function () {
              if ($(this).val() == category.category) {
                $(this).attr("selected", "selected");
              } else {
                $(this).removeAttr("selected");
              }
            });

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
        type: $("#edit_category_type").val(),
        category: $("#update_current_affairs_category_id").val(),
        status: parseInt($("input[name='status']:checked").val()),
      };
      if ($("#edit_instructions").val()) {
        formData["instructions"] = $("#edit_instructions").val();
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
                "#current_affairs_category__table__pagination .active span"
              ).data("page");
              const limit = $("#current_affairs_category__table__length").val();
              const search = $("#current_affairs_category__data__search").val();
              fetchcurrent_affairsCategories(page, limit, search);
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
        current_affairs_category_id: "required",
      },
      messages: {
        category_name: "Please enter category name",
        current_affairs_category_id: "Please select parent category",
      },
      submitHandler: function (form) {
        var data = {
          category_name: $("#category_name").val(),
          type: $("#category_type").val(),
          category: $("#current_affairs_category_id").val(),
        };

        console.log(data);

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            const page = 1;
            const limit = $("#current_affairs_category__table__length").val();
            const search = $("#current_affairs_category__data__search").val();
            fetchcurrent_affairsCategories(page, limit, search);

            $("#category_name").val("");

            alert(response.message);
          },
          error: function (xhr, status, error) {
            console.error("Submission failed:", error);
            console.error("Response:", xhr.responseText);
          },
        });
      },
    });
  } // End of Current Affairs Subcategory Management

  // Current Affairs PDF Management
  let pdf_management_table = document.getElementById("pdf_management_table");

  if (document.body.contains(pdf_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/pdf.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/pdf.php`;
    }

    function fetchTableData(page, limit, search, category = null) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 2,
      };

      $.ajax({
        url: `${apiUrl}?table`,
        method: "GET",
        data: data,
        success: function (data) {
          console.log(data.data);

          if (data.total !== "0") {
            $("#pdf_management_table").empty();
            data.data.forEach((category, index) => {
              let language = "";
              if (host.includes("localhost")) {
                languageApiUrl = `${protocol}//${host}/cl.englivia.com/api/language.php`;
              } else {
                languageApiUrl = `${protocol}//${host}/api/language.php`;
              }

              $.ajax({
                url: `${languageApiUrl}?language=${category.language}`,
                method: "GET",
                success: function (data) {
                  $("#pdf_management_table").append(`
                    <tr>
                      <td>${index + 1}</td>
                      <td>${category.id}</td>
                      <td style="min-width:100px">${category.category_name}</td>
                      <td style="min-width:100px">${
                        category.language == null
                          ? "N/A"
                          : getLanguage(category.language)
                      }</td>
                      <td style="min-width:100px">${category.type}</td>
                      <td>${category.pdf}</td>
                      <td style="min-width:80px">
                          <a class='btn btn-xs btn-primary edit-admin' data-id='${
                            category.id
                          }' id='edit_btn' data-toggle='modal' data-target='#editAdminModal' title='Edit'><i class='fas fa-edit'></i></a>
                          <a class='btn btn-xs btn-danger delete-admin' id='delete_btn' data-id='${
                            category.id
                          }' title='Delete'><i class='fas fa-trash'></i></a>
                      </td>
                    </tr>
                  `);
                },
              });
            });

            // Table hint text
            $("#table__hint__text").text(
              `Showing ${data.data.length} out of ${data.total} entries`
            );

            // Update pagination
            renderPagination(data.page, Math.ceil(data.total / data.limit));
          } else {
            $("#table__hint__text").empty();
            $("#table__pagination").empty();
            $("#pdf_management_table").empty();
            $("#pdf_management_table").append(`
              <tr>
                <td colspan="10" class="text-center">No data found</td>
              </tr>
          `);
            console.log("No data found");
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
    fetchTableData(1, 5, "");

    // Handle pagination click
    $(document).on("click", "#table__pagination .page-link", function () {
      const page = $(this).data("page");
      const limit = $("#table__length").val();
      const search = $("#data__search").val();
      fetchTableData(page, limit, search);
    });

    // Handle limit change
    $("#table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#data__search").val();
      fetchTableData(page, limit, search);
    });

    // Handle search
    $("#data__search").keyup(function () {
      const page = 1;
      const limit = $("#table__length").val();
      const search = $(this).val();
      fetchTableData(page, limit, search);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const categoryId = $(this).data("id");
      if (confirm("Are you sure you want to delete?")) {
        $.ajax({
          url: apiUrl,
          method: "DELETE",
          contentType: "application/json",
          data: JSON.stringify({ id: categoryId }),
          success: function (response) {
            if (response.status === 200) {
              alert("Category deleted successfully.");
              const page = 1;
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchTableData(page, limit, search);
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

            $("#editModal").modal({
              show: true,
              backdrop: "static",
              keyboard: true,
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
      const formData = new FormData();

      // Append text fields to formData
      formData.append("id", $("#edit_id").val());
      formData.append("category_name", $("#edit_category_name").val());

      // Append the file to formData
      const pdfFile = $("#edit_category_pdf")[0].files[0];
      if (pdfFile) {
        formData.append("pdf", pdfFile);
      }

      $.ajax({
        url: `${apiUrl}`,
        method: "POST", // Change to POST for file upload (you'll handle PUT on the server)
        contentType: false, // Important: `false` to let jQuery set the content type for multipart/form-data
        processData: false, // Important: `false` to prevent jQuery from processing the data
        data: formData,
        success: function (data) {
          console.log(data.message);
          if (data.status === 200) {
            $("#update_result")
              .html(
                '<div class="alert alert-success">' + data.message + "</div>"
              )
              .show();
            setTimeout(function () {
              $("#update_result").hide();
              $("#editModal").modal("hide");

              const page = $("#table__pagination .active span").data("page");
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchTableData(page, limit, search);
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
          type: $("#category_type").val(),
          instructions: $("#category_instructions").val() || null,
        };

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            const page = 1;
            const limit = $("#table__length").val();
            const search = $("#data__search").val();
            fetchTableData(page, limit, search);

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
  // End of Current Affairs PDF Management

  // Sentence Structure Category Management
  let sentence_structure_category_management_table = document.getElementById(
    "sentence_structure_category_management_table"
  );

  if (document.body.contains(sentence_structure_category_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/category.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/category.php`;
    }

    function fetchSentenceStructureCategories(
      page,
      limit,
      search,
      category = null
    ) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 3,
      };

      $.ajax({
        url: `${apiUrl}?table=true`,
        method: "GET",
        data: data,
        success: function (data) {
          console.log(data.response.data[0]);

          if (data.response.total !== "0") {
            $("#sentence_structure_category_management_table").empty();
            data.response.data.forEach((category, index) => {
              $("#sentence_structure_category_management_table").append(`
            <tr>
                <td>${index + 1}</td>
                <td>${category.id}</td>
                <td style="min-width:100px">${category.category_name}</td>
                <td style="min-width:100px">${
                  category.language == null
                    ? "N/A"
                    : getLanguage(category.language)
                }</td>
                <td style="min-width:100px">${category.type}</td>
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
            $("#sentence_structure_category__hint__text").text(
              `Showing ${data.response.data.length} out of ${data.response.total} entries`
            );
            renderPagination(
              data.response.page,
              Math.ceil(data.response.total / data.response.limit)
            );
          } else {
            $("#sentence_structure_category__hint__text").empty();
            $("#sentence_structure_category__table__pagination").empty();
            $("#sentence_structure_category_management_table").empty();
            $("#sentence_structure_category_management_table").append(`
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
      const pagination = $("#sentence_structure_category__table__pagination");
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
    fetchSentenceStructureCategories(1, 5, "");

    // Handle pagination click
    $(document).on(
      "click",
      "#sentence_structure_category__table__pagination .page-link",
      function () {
        const page = $(this).data("page");
        const limit = $("#sentence_structure_category__table__length").val();
        const search = $("#sentence_structure_category__data__search").val();
        fetchSentenceStructureCategories(page, limit, search);
      }
    );

    // Handle limit change
    $("#sentence_structure_category__table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#sentence_structure_category__data__search").val();
      fetchSentenceStructureCategories(page, limit, search);
    });

    // Handle search
    $("#sentence_structure_category__data__search").keyup(function () {
      const page = 1;
      const limit = $("#sentence_structure_category__table__length").val();
      const search = $(this).val();
      fetchSentenceStructureCategories(page, limit, search);
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
              const limit = $(
                "#sentence_structure_category__table__length"
              ).val();
              const search = $(
                "#sentence_structure_category__data__search"
              ).val();
              fetchSentenceStructureCategories(page, limit, search);
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
      console.log(categoryId);

      $.ajax({
        url: `${apiUrl}?type=3&id=${categoryId}`,
        method: "GET",
        success: function (data) {
          console.log(data);

          if (data.status === 200) {
            const category = data.data[0];
            $("#edit_id").val(category.id);

            $("#edit_category_name").val(category.category_name);

            // Preselect language
            $("#edit_category_language option").each(function () {
              if ($(this).val() == category.language) {
                $(this).attr("selected", "selected");
              } else {
                $(this).removeAttr("selected");
              }
            });

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
        type: $("#edit_category_type").val(),
        language: $("#edit_category_language").val(),
        status: parseInt($("input[name='status']:checked").val()),
      };

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
                "#sentence_structure_category__table__pagination .active span"
              ).data("page");
              const limit = $(
                "#sentence_structure_category__table__length"
              ).val();
              const search = $(
                "#sentence_structure_category__data__search"
              ).val();
              fetchSentenceStructureCategories(page, limit, search);
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
        category_language: "required",
      },
      messages: {
        category_name: "Please enter category name",
        category_language: "Please select category language",
      },
      submitHandler: function (form) {
        var data = {
          category_name: $("#category_name").val(),
          type: $("#category_type").val(),
          instructions: $("#category_instructions").val() || null,
          language: $("#category_language").val(),
          tag: $("#edit_tag").val(),
        };

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            const page = 1;
            const limit = $(
              "#sentence_structure_category__table__length"
            ).val();
            const search = $(
              "#sentence_structure_category__data__search"
            ).val();
            fetchSentenceStructureCategories(page, limit, search);

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
  } // End of Sentence Structure Category Management

  // Sentence Structure PDF Management
  let sentence_structure_pdf_management_table = document.getElementById(
    "sentence_structure_pdf_management_table"
  );

  if (document.body.contains(sentence_structure_pdf_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/pdf.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/pdf.php`;
    }

    function fetchTableData(page, limit, search, category = null) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 3,
      };

      $.ajax({
        url: `${apiUrl}?table`,
        method: "GET",
        data: data,
        success: function (data) {
          console.log(data.data.length);

          if (data.total !== "0") {
            $("#sentence_structure_pdf_management_table").empty();
            data.data.forEach((category, index) => {
              $("#sentence_structure_pdf_management_table").append(`
              <tr>
                  <td>${index + 1}</td>
                  <td>${category.id}</td>
                  <td style="min-width:100px">${category.category_name}</td>
                  <td style="min-width:100px">${
                    category.language == null
                      ? "N/A"
                      : getLanguage(category.language)
                  }</td>
                  <td style="min-width:100px">${category.type}</td>
                  <td>${category.pdf}</td>
                  <td style="min-width:80px">
                      <a class="btn btn-xs btn-primary edit-btn" data-id="${
                        category.id
                      }" id="edit_btn" data-toggle="modal" data-target="#editModal" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a class='btn btn-xs btn-danger delete-admin' id='delete_btn' data-id='${
                        category.id
                      }' title='Delete'><i class='fas fa-trash'></i></a>
                  </td>
              </tr>
            `);
            });

            // Table hint text
            $("#table__hint__text").text(
              `Showing ${data.data.length} out of ${data.total} entries`
            );

            // Update pagination
            renderPagination(data.page, Math.ceil(data.total / data.limit));
          } else {
            $("#table__hint__text").empty();
            $("#table__pagination").empty();
            $("#sentence_structure_pdf_management_table").empty();
            $("#sentence_structure_pdf_management_table").append(`
              <tr>
                <td colspan="10" class="text-center">No data found</td>
              </tr>
          `);
            console.log("No data found");
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
    fetchTableData(1, 5, "");

    // Handle pagination click
    $(document).on("click", "#table__pagination .page-link", function () {
      const page = $(this).data("page");
      const limit = $("#table__length").val();
      const search = $("#data__search").val();
      fetchTableData(page, limit, search);
    });

    // Handle limit change
    $("#table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#data__search").val();
      fetchTableData(page, limit, search);
    });

    // Handle search
    $("#data__search").keyup(function () {
      const page = 1;
      const limit = $("#table__length").val();
      const search = $(this).val();
      fetchTableData(page, limit, search);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const categoryId = $(this).data("id");
      if (confirm("Are you sure you want to delete?")) {
        $.ajax({
          url: apiUrl,
          method: "DELETE",
          contentType: "application/json",
          data: JSON.stringify({ id: categoryId }),
          success: function (response) {
            if (response.status === 200) {
              alert("Category deleted successfully.");
              const page = 1;
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchTableData(page, limit, search);
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

    // Open Update modal with preset values
    $(document).on("click", ".edit-btn", function () {
      const categoryId = $(this).data("id");
      console.log("Edit button clicked, Category ID:", categoryId);

      $.ajax({
        url: `${apiUrl}?type=3&id=${categoryId}`,
        method: "GET",
        success: function (data) {
          console.log(data);

          if (data.status === 200) {
            const category = data.data[0];
            $("#edit_id").val(category.id);
            $("#edit_category_name").val(category.category_name);

            $("#editModal").modal({
              show: true,
              backdrop: "static",
              keyboard: true,
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
      const formData = new FormData();

      // Append text fields to formData
      formData.append("id", $("#edit_id").val());
      formData.append("category_name", $("#edit_category_name").val());

      // Append the file to formData
      const pdfFile = $("#edit_category_pdf")[0].files[0];
      if (pdfFile) {
        formData.append("pdf", pdfFile);
      }

      $.ajax({
        url: `${apiUrl}`,
        method: "POST", // Change to POST for file upload (you'll handle PUT on the server)
        contentType: false, // Important: `false` to let jQuery set the content type for multipart/form-data
        processData: false, // Important: `false` to prevent jQuery from processing the data
        data: formData,
        success: function (data) {
          console.log(data.message);
          if (data.status === 200) {
            $("#update_result")
              .html(
                '<div class="alert alert-success">' + data.message + "</div>"
              )
              .show();
            setTimeout(function () {
              $("#update_result").hide();
              $("#editModal").modal("hide");

              const page = $("#table__pagination .active span").data("page");
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchTableData(page, limit, search);
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
  }
  // End of Sentence Structure PDF Management

  // One Liner Translation Category Management
  let one_linear_translation_category_management_table =
    document.getElementById("one_linear_translation_category_management_table");

  if (
    document.body.contains(one_linear_translation_category_management_table)
  ) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/translation/one-liner.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/translation/one-liner.php`;
    }

    function fetchOneLinearTranslationCategories(
      page,
      limit,
      search,
      category = null
    ) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 4,
        tag: "oneliner", // Add this line to filter by tag
      };

      $.ajax({
        url: `${apiUrl}?table=true`,
        method: "GET",
        data: data,
        success: function (data) {
          console.log(data);

          if (data.response.total !== "0") {
            $("#one_linear_translation_category_management_table").empty();
            data.response.data.forEach((category, index) => {
              // Add this condition to filter the results
              $("#one_linear_translation_category_management_table").append(`
            <tr>
                <td>${index + 1}</td>
                <td>${category.id}</td>
                <td style="min-width:100px">${category.category_name}</td>
                <td style="min-width:100px">${
                  category.language == null
                    ? "N/A"
                    : getLanguage(category.language)
                }</td>
                <td style="min-width:100px">${category.type}</td>
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
            $("#one_linear_translation_category__hint__text").text(
              `Showing ${data.response.data.length} out of ${data.response.total} entries`
            );
            renderPagination(
              data.response.page,
              Math.ceil(data.response.total / data.response.limit)
            );
          } else {
            $("#one_linear_translation_category__hint__text").empty();
            $("#one_linear_translation_category__table__pagination").empty();
            $("#one_linear_translation_category_management_table").empty();
            $("#one_linear_translation_category_management_table").append(`
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
      const pagination = $(
        "#one_linear_translation_category__table__pagination"
      );
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
    fetchOneLinearTranslationCategories(1, 5, "");

    // Handle pagination click
    $(document).on(
      "click",
      "#one_linear_translation_category__table__pagination .page-link",
      function () {
        const page = $(this).data("page");
        const limit = $(
          "#one_linear_translation_category__table__length"
        ).val();
        const search = $(
          "#one_linear_translation_category__data__search"
        ).val();
        fetchOneLinearTranslationCategories(page, limit, search);
      }
    );

    // Handle limit change
    $("#one_linear_translation_category__table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#one_linear_translation_category__data__search").val();
      fetchOneLinearTranslationCategories(page, limit, search);
    });

    // Handle search
    $("#one_linear_translation_category__data__search").keyup(function () {
      const page = 1;
      const limit = $("#one_linear_translation_category__table__length").val();
      const search = $(this).val();
      fetchOneLinearTranslationCategories(page, limit, search);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const categoryId = $(this).data("id");
      if (
        confirm(
          "By deleting this category PDF under this category will be deleted. Are you sure you want to delete?"
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
              const limit = $(
                "#one_linear_translation_category__table__length"
              ).val();
              const search = $(
                "#one_linear_translation_category__data__search"
              ).val();
              fetchOneLinearTranslationCategories(page, limit, search);
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
        url: `${apiUrl}?type=4&id=${categoryId}`,
        method: "GET",
        success: function (data) {
          if (data.status === 200) {
            const category = data.data[0];
            $("#edit_id").val(category.id);

            // Preselect language
            $("#edit_category_language option").each(function () {
              if ($(this).val() == category.language) {
                $(this).attr("selected", "selected");
              } else {
                $(this).removeAttr("selected");
              }
            });

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
        type: $("#edit_category_type").val(),
        language: $("#edit_category_language").val(),
        status: parseInt($("input[name='status']:checked").val()),
      };
      if ($("#edit_instructions").val()) {
        formData["instructions"] = $("#edit_instructions").val();
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
                "#one_linear_translation_category__table__pagination .active span"
              ).data("page");
              const limit = $(
                "#one_linear_translation_category__table__length"
              ).val();
              const search = $(
                "#one_linear_translation_category__data__search"
              ).val();
              fetchOneLinearTranslationCategories(page, limit, search);
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
        category_language: "required",
      },
      messages: {
        category_name: "Please enter category name",
        category_language: "Please select category language",
      },
      submitHandler: function (form) {
        var data = {
          category_name: $("#category_name").val(),
          language: $("#category_language").val(),
          type: $("#category_type").val(),
          tag: $("#category_tag").val(),
        };

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            const page = 1;
            const limit = $(
              "#one_linear_translation_category__table__length"
            ).val();
            const search = $(
              "#one_linear_translation_category__data__search"
            ).val();
            fetchOneLinearTranslationCategories(page, limit, search);

            $("#category_name").val("");

            alert(response.message);
          },
          error: function (xhr, status, error) {
            console.error("Submission failed:", error);
            console.error("Response:", xhr.responseText);
          },
        });
      },
    });
  } // End of One Liner Translation Category Management

  // One Liner Translation PDF Management
  let oneliner_translation_pdf_management_table = document.getElementById(
    "oneliner_translation_pdf_management_table"
  );

  if (document.body.contains(oneliner_translation_pdf_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/translation/oneliner-pdf.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/translation/oneliner-pdf.php`;
    }

    function fetchTableData(page, limit, search, category = null) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 4,
      };

      $.ajax({
        url: `${apiUrl}?table`,
        method: "GET",
        data: data,
        success: function (data) {
          console.log(data.data.length);

          if (data.total !== "0") {
            $("#oneliner_translation_pdf_management_table").empty();
            data.data.forEach((category, index) => {
              $("#oneliner_translation_pdf_management_table").append(`
              <tr>
                  <td>${index + 1}</td>
                  <td>${category.id}</td>
                  <td style="min-width:100px">${category.category_name}</td>
                  <td style="min-width:100px">${
                    category.language == null
                      ? "N/A"
                      : getLanguage(category.language)
                  }</td>
                  <td style="min-width:100px">${category.type}</td>
                  <td>${category.pdf}</td>
                  <td style="min-width:80px">
                      <a class="btn btn-xs btn-primary edit-btn" data-id="${
                        category.id
                      }" id="edit_btn" data-toggle="modal" data-target="#editModal" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a class='btn btn-xs btn-danger delete-admin' id='delete_btn' data-id='${
                        category.id
                      }' title='Delete'><i class='fas fa-trash'></i></a>
                  </td>
              </tr>
            `);
            });

            // Table hint text
            $("#table__hint__text").text(
              `Showing ${data.data.length} out of ${data.total} entries`
            );

            // Update pagination
            renderPagination(data.page, Math.ceil(data.total / data.limit));
          } else {
            $("#table__hint__text").empty();
            $("#table__pagination").empty();
            $("#oneliner_translation_pdf_management_table").empty();
            $("#oneliner_translation_pdf_management_table").append(`
              <tr>
                <td colspan="10" class="text-center">No data found</td>
              </tr>
          `);
            console.log("No data found");
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
    fetchTableData(1, 5, "");

    // Handle pagination click
    $(document).on("click", "#table__pagination .page-link", function () {
      const page = $(this).data("page");
      const limit = $("#table__length").val();
      const search = $("#data__search").val();
      fetchTableData(page, limit, search);
    });

    // Handle limit change
    $("#table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#data__search").val();
      fetchTableData(page, limit, search);
    });

    // Handle search
    $("#data__search").keyup(function () {
      const page = 1;
      const limit = $("#table__length").val();
      const search = $(this).val();
      fetchTableData(page, limit, search);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const categoryId = $(this).data("id");
      if (confirm("Are you sure you want to delete?")) {
        $.ajax({
          url: apiUrl,
          method: "DELETE",
          contentType: "application/json",
          data: JSON.stringify({ id: categoryId }),
          success: function (response) {
            if (response.status === 200) {
              alert("Category deleted successfully.");
              const page = 1;
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchTableData(page, limit, search);
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

    // Open Update modal with preset values
    $(document).on("click", ".edit-btn", function () {
      const categoryId = $(this).data("id");
      console.log("Edit button clicked, Category ID:", categoryId);

      $.ajax({
        url: `${apiUrl}?type=4&id=${categoryId}`,
        method: "GET",
        success: function (data) {
          console.log(data);

          if (data.status === 200) {
            const category = data.data[0];
            $("#edit_id").val(category.id);
            $("#edit_category_name").val(category.category_name);

            $("#editModal").modal({
              show: true,
              backdrop: "static",
              keyboard: true,
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
      const formData = new FormData();

      // Append text fields to formData
      formData.append("id", $("#edit_id").val());
      formData.append("category_name", $("#edit_category_name").val());

      // Append the file to formData
      const pdfFile = $("#edit_category_pdf")[0].files[0];
      if (pdfFile) {
        formData.append("pdf", pdfFile);
      }

      $.ajax({
        url: `${apiUrl}`,
        method: "POST", // Change to POST for file upload (you'll handle PUT on the server)
        contentType: false, // Important: `false` to let jQuery set the content type for multipart/form-data
        processData: false, // Important: `false` to prevent jQuery from processing the data
        data: formData,
        success: function (data) {
          console.log(data.message);
          if (data.status === 200) {
            $("#update_result")
              .html(
                '<div class="alert alert-success">' + data.message + "</div>"
              )
              .show();
            setTimeout(function () {
              $("#update_result").hide();
              $("#editModal").modal("hide");

              const page = $("#table__pagination .active span").data("page");
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchTableData(page, limit, search);
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
  }
  // End of One Liner Translation Structure PDF Management

  // Paragraph Translation Category Management
  let paragraph_translation_category_management_table = document.getElementById(
    "paragraph_translation_category_management_table"
  );

  if (document.body.contains(paragraph_translation_category_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/translation/paragraph.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/translation/paragraph.php`;
    }

    function fetchParagraphTranslationCategories(
      page,
      limit,
      search,
      category = null
    ) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 4,
        tag: "paragraph",
      };

      $.ajax({
        url: `${apiUrl}?table=true`,
        method: "GET",
        data: data,
        success: function (data) {
          console.log(data.response.data[0]);

          if (data.response.total !== "0") {
            $("#paragraph_translationtegory_management_table").empty();
            data.response.data.forEach((category, index) => {
              // Add this condition to filter the results
              $("#paragraph_translation_category_management_table").append(`
            <tr>
                <td>${index + 1}</td>
                <td>${category.id}</td>
                <td style="min-width:100px">${category.category_name}</td>
                <td style="min-width:100px">${
                  category.language == null
                    ? "N/A"
                    : getLanguage(category.language)
                }</td>
                <td style="min-width:100px">${category.type}</td>
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
            $("#paragraph_translation_category__hint__text").text(
              `Showing ${data.response.data.length} out of ${data.response.total} entries`
            );
            renderPagination(
              data.response.page,
              Math.ceil(data.response.total / data.response.limit)
            );
          } else {
            $("#paragraph_translation_category__hint__text").empty();
            $("#paragraph_translation_category__table__pagination").empty();
            $("#paragraph_translation_category_management_table").empty();
            $("#paragraph_translation_category_management_table").append(`
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
      const pagination = $(
        "#paragraph_translation_category__table__pagination"
      );
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
    fetchParagraphTranslationCategories(1, 5, "");

    // Handle pagination click
    $(document).on(
      "click",
      "#paragraph_translation_category__table__pagination .page-link",
      function () {
        const page = $(this).data("page");
        const limit = $("#paragraph_translation_category__table__length").val();
        const search = $("#paragraph_translation_category__data__search").val();
        fetchParagraphTranslationCategories(page, limit, search);
      }
    );

    // Handle limit change
    $("#paragraph_translation_category__table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#paragraph_translation_category__data__search").val();
      fetchParagraphtranslationCategories(page, limit, search);
    });

    // Handle search
    $("#paragraph_translation_category__data__search").keyup(function () {
      const page = 1;
      const limit = $("#paragraph_translation_category__table__length").val();
      const search = $(this).val();
      fetchParagraphTranslationCategories(page, limit, search);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const categoryId = $(this).data("id");
      if (
        confirm(
          "By deleting this category PDF under this category will be deleted. Are you sure you want to delete?"
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
              const limit = $(
                "#paragraph_translation_category__table__length"
              ).val();
              const search = $(
                "#paragraph_translation_category__data__search"
              ).val();
              $("#paragraph_translation_category_management_table").empty();
              fetchParagraphTranslationCategories(page, limit, search);
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
        url: `${apiUrl}?type=4&id=${categoryId}`,
        method: "GET",
        success: function (data) {
          if (data.status === 200) {
            const category = data.data[0];
            $("#edit_id").val(category.id);

            $("#edit_category_name").val(category.category_name);

            // Preselect language
            $("#edit_category_language option").each(function () {
              if ($(this).val() == category.language) {
                $(this).attr("selected", "selected");
              } else {
                $(this).removeAttr("selected");
              }
            });

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
              keyboard: true,
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
        type: $("#edit_category_type").val(),
        language: $("#edit_category_language").val(),
        status: parseInt($("input[name='status']:checked").val()),
      };

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
                "#paragraph_translation_category__table__pagination .active span"
              ).data("page");
              const limit = $(
                "#paragraph_translation_category__table__length"
              ).val();
              const search = $(
                "#paragraph_translation_category__data__search"
              ).val();
              fetchParagraphTranslationCategories(page, limit, search);
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
        category_language: "required",
      },
      messages: {
        category_name: "Please enter category name",
        category_language: "Please select category language",
      },
      submitHandler: function (form) {
        var data = {
          category_name: $("#category_name").val(),
          language: $("#category_language").val(),
          type: $("#category_type").val(),
          tag: $("#category_tag").val(),
        };

        $.ajax({
          url: apiUrl,
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify(data),
          success: function (response) {
            const page = 1;
            const limit = $(
              "#paragraph_translation_category__table__length"
            ).val();
            const search = $(
              "#paragraph_translation_category__data__search"
            ).val();
            $("#paragraph_translation_category_management_table").empty();
            fetchParagraphTranslationCategories(page, limit, search);

            $("#category_name").val("");

            alert(response.message);
          },
          error: function (xhr, status, error) {
            console.error("Submission failed:", error);
            console.error("Response:", xhr.responseText);
          },
        });
      },
    });
  } // End of Paragraph Translation Category Management

  // Paragraph Translation PDF Management
  let paragraph_translation_pdf_management_table = document.getElementById(
    "paragraph_translation_pdf_management_table"
  );

  if (document.body.contains(paragraph_translation_pdf_management_table)) {
    if (host.includes("localhost")) {
      apiUrl = `${protocol}//${host}/cl.englivia.com/api/translation/paragraph-pdf.php`;
    } else {
      apiUrl = `${protocol}//${host}/api/translation/paragraph-pdf.php`;
    }

    function fetchTableData(page, limit, search, category = null) {
      console.log(page, limit, search);
      let data = {
        page: page,
        limit: limit,
        search: search,
        type: 4,
      };

      $.ajax({
        url: `${apiUrl}?table`,
        method: "GET",
        data: data,
        success: function (data) {
          console.log(data.data.length);

          if (data.total !== "0") {
            $("#paragraph_translation_pdf_management_table").empty();
            data.data.forEach((category, index) => {
              $("#paragraph_translation_pdf_management_table").append(`
              <tr>
                  <td>${index + 1}</td>
                  <td>${category.id}</td>
                  <td style="min-width:100px">${category.category_name}</td>
                  <td style="min-width:100px">${
                    category.language == null
                      ? "N/A"
                      : getLanguage(category.language)
                  }</td>
                  <td style="min-width:100px">${category.type}</td>
                  <td>${category.pdf}</td>
                  <td style="min-width:80px">
                      <a class="btn btn-xs btn-primary edit-btn" data-id="${
                        category.id
                      }" id="edit_btn" data-toggle="modal" data-target="#editModal" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a class='btn btn-xs btn-danger delete-admin' id='delete_btn' data-id='${
                        category.id
                      }' title='Delete'><i class='fas fa-trash'></i></a>
                  </td>
              </tr>
            `);
            });

            // Table hint text
            $("#table__hint__text").text(
              `Showing ${data.data.length} out of ${data.total} entries`
            );

            // Update pagination
            renderPagination(data.page, Math.ceil(data.total / data.limit));
          } else {
            $("#table__hint__text").empty();
            $("#table__pagination").empty();
            $("#paragraph_translation_pdf_management_table").empty();
            $("#paragraph_translation_pdf_management_table").append(`
              <tr>
                <td colspan="10" class="text-center">No data found</td>
              </tr>
          `);
            console.log("No data found");
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
    fetchTableData(1, 5, "");

    // Handle pagination click
    $(document).on("click", "#table__pagination .page-link", function () {
      const page = $(this).data("page");
      const limit = $("#table__length").val();
      const search = $("#data__search").val();
      fetchTableData(page, limit, search);
    });

    // Handle limit change
    $("#table__length").change(function () {
      const page = 1;
      const limit = $(this).val();
      const search = $("#data__search").val();
      fetchTableData(page, limit, search);
    });

    // Handle search
    $("#data__search").keyup(function () {
      const page = 1;
      const limit = $("#table__length").val();
      const search = $(this).val();
      fetchTableData(page, limit, search);
    });

    // Handle Delete
    $(document).on("click", "#delete_btn", function () {
      const categoryId = $(this).data("id");
      if (confirm("Are you sure you want to delete?")) {
        $.ajax({
          url: apiUrl,
          method: "DELETE",
          contentType: "application/json",
          data: JSON.stringify({ id: categoryId }),
          success: function (response) {
            if (response.status === 200) {
              alert("Category deleted successfully.");
              const page = 1;
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchTableData(page, limit, search);
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

    // Open Update modal with preset values
    $(document).on("click", ".edit-btn", function () {
      const categoryId = $(this).data("id");
      console.log("Edit button clicked, Category ID:", categoryId);

      $.ajax({
        url: `${apiUrl}?type=4&id=${categoryId}`,
        method: "GET",
        success: function (data) {
          console.log(data);

          if (data.status === 200) {
            const category = data.data[0];
            $("#edit_id").val(category.id);
            $("#edit_category_name").val(category.category_name);

            $("#editModal").modal({
              show: true,
              backdrop: "static",
              keyboard: true,
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
      const formData = new FormData();

      // Append text fields to formData
      formData.append("id", $("#edit_id").val());
      formData.append("category_name", $("#edit_category_name").val());

      // Append the file to formData
      const pdfFile = $("#edit_category_pdf")[0].files[0];
      if (pdfFile) {
        formData.append("pdf", pdfFile);
      }

      $.ajax({
        url: `${apiUrl}`,
        method: "POST", // Change to POST for file upload (you'll handle PUT on the server)
        contentType: false, // Important: `false` to let jQuery set the content type for multipart/form-data
        processData: false, // Important: `false` to prevent jQuery from processing the data
        data: formData,
        success: function (data) {
          console.log(data.message);
          if (data.status === 200) {
            $("#update_result")
              .html(
                '<div class="alert alert-success">' + data.message + "</div>"
              )
              .show();
            setTimeout(function () {
              $("#update_result").hide();
              $("#editModal").modal("hide");

              const page = $("#table__pagination .active span").data("page");
              const limit = $("#table__length").val();
              const search = $("#data__search").val();
              fetchTableData(page, limit, search);
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
  }
  // End of paragraph Translation Structure PDF Management
});
