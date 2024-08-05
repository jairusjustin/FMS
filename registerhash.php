<?php
include 'config.php'; 
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
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
    $select_user = "SELECT user_id, role FROM `user` WHERE email = ?";
    $stmt = mysqli_prepare($mysqli, $select_user);

    // Bind the parameters
    mysqli_stmt_bind_param($stmt, "s", $email);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Get the result
    $sql_run = mysqli_stmt_get_result($stmt);

    if ($sql_run === FALSE) {
        error_log("SQL Error: " . mysqli_error($mysqli));
        $response = array(
            'status' => 'error',
            'message' => 'Database error occurred'
        );
        sendResponse($response);
    }

    // Fetch user data
    $user_data = mysqli_fetch_assoc($sql_run);

    if ($user_data) {
        // Email already registered
        $response = array(
            'status' => 'error',
            'message' => 'Email is already registered'
        );
        sendResponse($response);
    } else {
        // Hash the password securely
        $hashedPassword = md5($password);

        // Insert the new user into the database with role 'pending'
        $insert_user = "INSERT INTO `user` (firstname, lastname, email, pass, role) VALUES (?, ?, ?, ?, 'pending')";
        $stmt_insert = mysqli_prepare($mysqli, $insert_user);

        // Bind the parameters
        mysqli_stmt_bind_param($stmt_insert, "ssss", $firstname, $lastname, $email, $hashedPassword);

        // Execute the statement
        $result = mysqli_stmt_execute($stmt_insert);

        if ($result) {
            // Registration success
            $response = array(
                'status' => 'success',
                'message' => 'Registration successful. Awaiting approval.'
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
    // Set Content-Type header for JSON
    header('Content-Type: application/json');

    // Send JSON response for AJAX
    echo json_encode($response);
    exit();
}
?>
