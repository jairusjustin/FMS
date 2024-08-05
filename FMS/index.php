<?php
include 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <title>Saka-Insights Farm Management</title>
    <!-- Styles -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/icon.png"/>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.js"></script>
</head>
<body>

<div class="wrapper">
    <nav class="nav">
        <div class="nav-logo">
            <img src="images/logo.png" alt="Saka-Insights Logo">
            <p>Saka-Insights</p>
        </div>
    </nav>

    <!-- login form -->
    <div class="form-box">
        <div class="login-container" id="login">
            <div id="message-div"></div>
            <div class="top">
                <span>Don't have an account? <a onclick="register()" class="link">Sign Up</a></span>
                <header>Login</header>
            </div>
            <form id="loginForm" method="post" action="login.php">
                <div id="message-div"></div>
                <div class="input-box">
                    <input type="text" class="input-field" placeholder="Email" name="email" required maxlength="50">
                    <i class="bx bx-user"></i>
                </div>
                <div class="input-box">
                    <input type="password" class="input-field" placeholder="Password" name="password" required maxlength="20">
                    <i class="bx bx-lock-alt"></i>
                </div>
                <div class="input-box">
                    <input type="submit" class="submit" value="Sign In">
                </div>
            </form>
        </div>

        <!-- registration form -->
        <div class="register-container" id="register">
            <div class="top">
                <span>Have an account? <a onclick="login()" class="link">Login</a></span>
                <header>Sign Up</header>
            </div>
            <form id="registerForm" method="post" action="registerhash.php">
                <div class="two-forms">
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Firstname" name="firstname" required>
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Lastname" name="lastname" required>
                        <i class="bx bx-user"></i>
                    </div>
                </div>
                <div class="input-box">
                    <input type="text" class="input-field" placeholder="Email" name="email" required maxlength="50">
                    <i class="bx bx-envelope"></i>
                </div>
                <div class="input-box">
                    <input type="password" class="input-field" placeholder="Password" name="password" required maxlength="20">
                    <i class="bx bx-lock-alt"></i>
                </div>
                <div class="input-box">
                    <input type="submit" class="submit" value="Register">
                </div>
            </form>
        </div>
    </div>
        <!-- Footer -->
        <footer class="footer">
        <p>&copy; 2024 Saka-Insights Farm Management</p>
    </footer>
</div>


<script src="js/main.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        $('#loginForm').submit(function (event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: 'login.php',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        // Successful login
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            confirmButtonColor: '#436850',
                            text: response.message
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if (response.role === 'admin') {
                                    window.location.href = 'admin_dashboard.php';
                                } else {
                                    window.location.href = 'user_dashboard.php';
                                }
                            }
                        });
                    } else if (response.status === 'admin') {
                        // Admin login
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            confirmButtonColor: '#436850',
                            text: response.message
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'admin_dashboard.php';
                            }
                        });
                    } else if (response.status === 'user') {
                        // User login
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            confirmButtonColor: '#436850',
                            text: response.message
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'user_weather.php';
                            }
                        });
                    } else if (response.status === 'pending') {
                        // Account is pending
                        Swal.fire({
                            icon: 'info',
                            title: 'Pending Approval',
                            confirmButtonColor: '#436850',
                            text: response.message,
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Failed login
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            confirmButtonColor: '#436850',
                            text: response.message
                        });
                    }
                },
                error: function () {
                    // Failed login
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        confirmButtonColor: '#436850',
                        text: 'An error occurred during login'
                    });
                }
            });
        });
    });

$(document).ready(function () {
    $('#registerForm').submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        // Serialize form data
        var formData = $(this).serialize();

        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: 'registerhash.php',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    // Display success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    // Reset the form after successful submission
                    $('#registerForm')[0].reset();
                } else {
                    // Display error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            },
            error: function () {
                // Display error message if AJAX request fails
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during registration',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        });
    });
});

</script>
</body>
</html>
