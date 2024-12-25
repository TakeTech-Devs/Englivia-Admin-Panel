<?php
if (file_exists('install/index.php')) {
    header("location:install/");
    die();
}
session_start();
if (isset($_SESSION['id']) && isset($_SESSION['username'])) {
    header("location:home.php");
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
    <title>Forgot Password</title>
    <?php include 'include-css.php'; ?>
</head>

<body class="login">
    <div>
        <a class="hiddenanchor" id="signup"></a>
        <a class="hiddenanchor" id="signin"></a>
        <div class="login_wrapper">
            <div class="animate form login_form">
                <section class="login_content">
                    <form id="forgot_password_form">
                        <h1 class="text-center">Forgot Password</h1>
                        <div id="email_input_section">
                            <div>
                                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your registered email" />
                            </div>
                            <div class="row mt-20 w-100">
                                <div class="col">
                                    <button type="button" id="send_otp_button" class="btn btn-default text-center" style="margin-left: 10px;">Send OTP</button>
                                </div>
                            </div>
                        </div>
                        <div style="display:none;" id="otp_section">
                            <div>
                                <input type="text" id="otp" name="otp" class="form-control mt-20" placeholder="Enter OTP" />
                            </div>
                            <div>
                                <button type="button" id="verify_otp_button" class="btn btn-default text-center mt-20" style="margin-left: 10px;">Verify OTP</button>
                            </div>
                        </div>
                        <div style="display:none;" id="reset_password_section">
                            <div>
                                <input type="password" id="new_password" name="new_password" class="form-control mt-20" placeholder="Enter New Password" />
                            </div>
                            <div>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control mt-20" placeholder="Confirm New Password" />
                            </div>
                            <div>
                                <button type="submit" id="reset_password_button" class="btn btn-default text-center mt-20" style="margin-left: 10px;">Reset Password</button>
                            </div>
                        </div>
                        <div class="alert alert-info" style="display:none;" id="login_message"></div>

                        <div class="clearfix"></div>
                        <div style="display:none;" id="result"></div>
                        <div class="row mt-20 w-100">
                            <div class="col text-right">
                                <a href="index.php">Back to Login</a>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
    <!-- Including Jquery so All js Can run -->
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <!-- Validation js -->
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script>
        let emailVerified = false;
        // let formData = new FormData();

        $("#forgot_password_form").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                otp: {
                    required: () => $("#otp_section").is(":visible")
                },
                new_password: {
                    required: () => $("#reset_password_section").is(":visible"),
                    minlength: 6
                },
                confirm_password: {
                    required: () => $("#reset_password_section").is(":visible"),
                    equalTo: "#new_password"
                }
            },
            messages: {
                email: {
                    required: "Please enter your email",
                    email: "Please enter a valid email address"
                },
                otp: {
                    required: "Please enter the OTP sent to your email"
                },
                new_password: {
                    required: "Please enter a new password",
                    minlength: "Password must be at least 6 characters long"
                },
                confirm_password: {
                    required: "Please confirm your new password",
                    equalTo: "Passwords do not match"
                }
            }
        });

        $("#send_otp_button").on('click', () => {
            const email = $("#email").val();
            if (!email) {
                alert("Please enter your email first.");
                return;
            }
            $.ajax({
                url: "send_otp.php",
                type: "POST",
                data: {
                    email
                },
                beforeSend: () => $('#send_otp_button').html('Sending...'),
                success: (result) => {
                    const resultElement = $('#result');
                    if (result === '1') {
                        resultElement.html('Otp sent successfully!').addClass('alert alert-success').show();
                        $("#email_input_section").hide();
                        $("#otp_section").show();

                        setTimeout(() => {
                            resultElement.fadeOut('slow', function() {
                                $(this).html('').removeClass('alert alert-success').hide();
                            });
                        }, 3000);

                        // formData.append('email', email);

                    } else {
                        resultElement.html(result).addClass('alert alert-danger').show();

                        setTimeout(() => {
                            resultElement.fadeOut('slow', function() {
                                $(this).html('').removeClass('alert alert-danger').hide();
                            });
                        }, 3000);
                    }
                },
                error: () => {
                    $('#result').html('An error occurred. Please try again.').addClass('alert alert-danger').show();

                    setTimeout(() => {
                        $('#result').fadeOut('slow', function() {
                            $(this).html('').removeClass('alert alert-danger').hide();
                        });
                    }, 3000);
                }
            });
        });

        $("#verify_otp_button").on('click', () => {
            const otp = $("#otp").val();
            const user_id = $("#email").val();

            if (!otp) {
                alert("Please enter the OTP.");
                return;
            }

            $.ajax({
                url: "verify_otp.php",
                type: "POST",
                data: {
                    otp,
                    user_id
                },
                beforeSend: () => $('#verify_otp_button').html('Verifying...'),
                success: (result) => {
                    const resultElement = $('#result');
                    if (!result.error) {
                        resultElement.html("OTP verified. You can now reset your password.").addClass('alert alert-success').show();
                        emailVerified = true;
                        $("#otp_section").hide();
                        $("#reset_password_section").show();

                        setTimeout(() => {
                            resultElement.fadeOut('slow', function() {
                                $(this).html('').removeClass('alert alert-success').hide();
                            });
                        }, 3000);

                    } else {
                        resultElement.html(result).addClass('alert alert-danger').show();

                        setTimeout(() => {
                            resultElement.fadeOut('slow', function() {
                                $(this).html('').removeClass('alert alert-danger').hide();
                            });
                        }, 3000);
                    }
                },
                error: () => {
                    $('#result').html('An error occurred. Please try again.').addClass('alert alert-danger').show();

                    setTimeout(() => {
                        $('#result').fadeOut('slow', function() {
                            $(this).html('').removeClass('alert alert-danger').hide();
                        });
                    }, 3000);
                }
            });
        });

        $(document).on('submit', '#forgot_password_form', function(e) {
            e.preventDefault();

            if (!emailVerified) {
                alert("Please verify your OTP first.");
                return;
            }

            const formData = new FormData(this);
            console.log(...formData.entries());

            $.ajax({
                url: "forgot_pass_process.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => $('#reset_password_button').html('Please Wait...'),
                success: (response) => {
                    console.log(response);

                    const resultElement = $('#result');
                    resultElement.html(response.message).addClass('alert alert-success').show();
                    $('#reset_password_button').html('Reset Password');

                    setTimeout(() => {
                        resultElement.fadeOut('slow', function() {
                            $(this).html('').removeClass('alert alert-success').hide();
                        });
                    }, 3000);

                    setTimeout(() => {
                        ('#reset_password_section').hide();
                        ('#login_message').html('You can now login with the new password!').show();
                    }, 3000);

                },
                error: (err) => {
                    console.error(err);

                    const resultElement = $('#result');
                    resultElement.html('An error occurred. Please try again.').addClass('alert alert-danger').show();

                    setTimeout(() => {
                        resultElement.fadeOut('slow', function() {
                            $(this).html('').removeClass('alert alert-danger').hide();
                        });
                    }, 3000);
                }
            });
        });
    </script>
</body>

</html>