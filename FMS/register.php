<?php
include 'config.php'; 
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = array(
            'status' => 'error',
            'message' => 'Invalid email format'
        );
        sendResponse($response);
    }

    // Validate password format 
    if (strlen($password) < 6) {
        $response = array(
            'status' => 'error',
            'message' => 'Password must be at least 6 characters long'
        );
        sendResponse($response);
    }

    // Check if the user with the provided email already exists
    $select_user = "SELECT user_id FROM `user` WHERE email = ?";
    $stmt = mysqli_prepare($mysqli, $select_user);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $sql_run = mysqli_stmt_get_result($stmt);

    if ($sql_run === FALSE) {
        error_log("SQL Error: " . mysqli_error($mysqli));
    }

    // Check if the email is already registered
    $num_rows = mysqli_num_rows($sql_run);

    if ($num_rows > 0) {
        // Email already registered
        $response = array(
            'status' => 'error',
            'message' => 'Email is already registered'
        );
        sendResponse($response);
    } else {
        // Insert the new user into the database without hashing the password
        $insert_user = "INSERT INTO `user` (firstname, lastname, email, pass) VALUES (?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($mysqli, $insert_user);


        mysqli_stmt_bind_param($stmt_insert, "ssss", $firstname, $lastname, $email, $password);


        $result = mysqli_stmt_execute($stmt_insert);

        if ($result) {
            // Registration success
            $response = array(
                'status' => 'success',
                'message' => 'Registration successful'
            );
            sendResponse($response);
        } else {
            // Registration failed
            $response = array(
                'status' => 'error',
                'message' => 'Registration failed'
            );
            sendResponse($response);
        }
    }
}

function sendResponse($response) {
    header('Content-Type: application/json');

    echo json_encode($response);
    exit();
}
?>
