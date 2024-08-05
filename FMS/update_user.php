<?php
include 'config.php';
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['userId'];
    $userFirstName = $_POST['userFirstName'];
    $userLastName = $_POST['userLastName'];
    $userEmail = $_POST['userEmail'];
    $userPassword = $_POST['userPassword'];

    $update_query = "UPDATE user SET firstname = ?, lastname = ?, email = ?, pass = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($mysqli, $update_query);

    mysqli_stmt_bind_param($stmt, "ssssi", $userFirstName, $userLastName, $userEmail, $userPassword, $user_id);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // Update successful
        $status = "success";
    } else {
        // Update failed
        $status = "error";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($mysqli);

header("Location: admin_settings.php?status=" . urlencode($status));
exit;
?>
