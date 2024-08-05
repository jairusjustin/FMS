<?php
include 'config.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $expense_id = $_POST['expense_id'];

    $sql_fetch_expense = "SELECT e.expense_id, 
                                a.activity_date, 
                                a.activity_type, 
                                e.expense_amount, 
                                CONCAT(c.crop_name, ' - ', f.field_name) AS crop_field
                        FROM expenses e
                        JOIN activities a ON e.activity_id = a.activity_id
                        JOIN cropsfield cf ON a.cropsfield_id = cf.cropsfield_id
                        JOIN crops c ON cf.crops_id = c.crops_id
                        JOIN fields f ON cf.field_id = f.field_id
                        WHERE e.expense_id = ?";
                        
    $stmt_fetch_expense = mysqli_prepare($mysqli, $sql_fetch_expense);

    if ($stmt_fetch_expense) {
        mysqli_stmt_bind_param($stmt_fetch_expense, "i", $expense_id);
        mysqli_stmt_execute($stmt_fetch_expense);
        $result = mysqli_stmt_get_result($stmt_fetch_expense);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $response['status'] = 'success';
            $response['data'] = $row;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Expense not found.';
        }

        mysqli_stmt_close($stmt_fetch_expense);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error in prepared statement for fetching expense: ' . mysqli_error($mysqli);
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
