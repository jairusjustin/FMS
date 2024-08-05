<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse('error', 'Invalid email format');
    }

    // Validate password format 
    if (strlen($password) < 6) {
        sendResponse('error', 'Password must be at least 6 characters long');
    }

    // Check if the user with the provided email exists
    $select_user = "SELECT user_id, pass, role FROM `user` WHERE email = ?";
    $stmt = mysqli_prepare($mysqli, $select_user);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $sql_run = mysqli_stmt_get_result($stmt);

    if ($sql_run === FALSE) {
        error_log("SQL Error: " . mysqli_error($mysqli));
        sendResponse('error', 'An error occurred');
    }

    $num_rows = mysqli_num_rows($sql_run);
    error_log("Number of Rows: " . $num_rows);

    error_log("SQL Query: " . $select_user);

    if ($num_rows > 0) {
        // Correct email and password
        $data = $sql_run->fetch_assoc();
        if (md5($password) === $data['pass']) {
            if ($data['role'] !== 'pending' && $data['role'] !== 'declined') {
                $_SESSION['user_id'] = $data['user_id'];
                $_SESSION['role'] = $data['role'];

                if ($data['role'] === 'user') {
                    sendResponse('user', 'User login successful!');
                } elseif ($data['role'] === 'admin') {
                    sendResponse('admin', 'Admin login successful!');
                } else {
                    sendResponse('error', 'Invalid role');
                }
            } elseif ($data['role'] === 'pending') {
                sendResponse('pending', 'Your account is pending approval.');
            } elseif ($data['role'] === 'declined') {
                sendResponse('error', 'Your account has been declined.');
            }
        } else {
            // Incorrect password
            sendResponse('error', 'Invalid username or password');
        }
    } else {
        // Email not registered
        sendResponse('error', 'Email is not registered');
    }
}

function sendResponse($status, $message) {
    $response = array(
        'status' => $status,
        'message' => $message,
        'stat_title' => $status === 'success' || $status === 'admin' ? 'Logged In Successfully!' : 'Error',
        'stat_icon' => $status === 'success' || $status === 'admin' ? 'success' : 'error',
        'stat_message' => $message
    );

    header('Content-Type: application/json');

    echo json_encode($response);
    exit();
}
?>
