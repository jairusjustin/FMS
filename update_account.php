<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; 
    $current_password = trim($_POST["currentPassword"]);
    $new_password = trim($_POST["newPassword"]);
    $first_name = trim($_POST["firstName"]);
    $last_name = trim($_POST["lastName"]);
    $email = trim($_POST["email"]);

    // Validate current password format 
    if (strlen($current_password) < 6) {
        sendResponse('error', 'Current password must be at least 6 characters long');
    }

    // Validate new password format 
    if ($new_password && strlen($new_password) < 6) {
        sendResponse('error', 'New password must be at least 6 characters long');
    }

    // Check if the user with the provided user_id exists
    $select_user = "SELECT pass FROM `user` WHERE user_id = ?";
    $stmt = mysqli_prepare($mysqli, $select_user);

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    mysqli_stmt_execute($stmt);

    $sql_run = mysqli_stmt_get_result($stmt);

    if ($sql_run === FALSE) {
        error_log("SQL Error: " . mysqli_error($mysqli));
        sendResponse('error', 'An error occurred');
    }

    $data = $sql_run->fetch_assoc();

    // Check if the current password matches
    if (md5($current_password) === $data['pass']) {
        // Update the user details
        $update_query = "UPDATE `user` SET firstname = ?, lastname = ?, email = ?";
        $params = "sss";
        $values = [$first_name, $last_name, $email];

        if ($new_password) {
            $update_query .= ", pass = ?";
            $params .= "s";
            $values[] = md5($new_password);
        }

        $update_query .= " WHERE user_id = ?";
        $params .= "i";
        $values[] = $user_id;

        $stmt = mysqli_prepare($mysqli, $update_query);

        mysqli_stmt_bind_param($stmt, $params, ...$values);

        if (mysqli_stmt_execute($stmt)) {
            sendResponse('success', 'Account details updated successfully!');
        } else {
            error_log("SQL Error: " . mysqli_error($mysqli));
            sendResponse('error', 'An error occurred while updating the account details');
        }
    } else {
        sendResponse('error', 'Current password is incorrect');
    }
}

function sendResponse($status, $message) {
    $response = array(
        'status' => $status,
        'message' => $message,
        'stat_title' => $status === 'success' ? 'Success!' : 'Error',
        'stat_icon' => $status === 'success' ? 'success' : 'error',
        'stat_message' => $message
    );

    header('Content-Type: application/json');

    echo json_encode($response);
    exit();
}
?>
