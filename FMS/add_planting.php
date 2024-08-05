<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropsfieldId = $_POST['cropsfield_id'];
    $activityDate = $_POST['activity_date'];
    $expenseAmount = $_POST['expenseAmount'];
    $seededArea = $_POST['seededArea']; 

    if (empty($activityDate)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a date for the activity!']);
        exit;
    }

    
    if ($seededArea <= 0 || $seededArea === "") {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid seeded area!!']);
        exit;
    }

    if (empty($expenseAmount)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter the expense amount!']);
        exit;
    }

    // Insert planting query
    $sql = "INSERT INTO activities (cropsfield_id, activity_type, activity_date) VALUES (?, 'Planting', ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('is', $cropsfieldId, $activityDate);

    if ($stmt->execute()) {
        $plantingId = $stmt->insert_id;

        // Insert seeded area into the seeded_area table
        $sqlInsertSeededArea = "INSERT INTO seeded_area (cropsfield_id, area) VALUES (?, ?)";
        $stmtInsertSeededArea = $mysqli->prepare($sqlInsertSeededArea);
        $stmtInsertSeededArea->bind_param('id', $cropsfieldId, $seededArea);
        $stmtInsertSeededArea->execute();

        // Insert expense query
        $sqlExpense = "INSERT INTO expenses (activity_id, expense_amount) VALUES (?, ?)";
        $stmtExpense = $mysqli->prepare($sqlExpense);
        $stmtExpense->bind_param('id', $plantingId, $expenseAmount);

        if ($stmtExpense->execute()) {
            $response = [
                'status' => 'success',
                'message' => 'Planting details and expense saved successfully!'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Error occurred while saving the expense.',
                'debug' => $stmtExpense->error
            ];
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error occurred while saving the planting details.',
            'debug' => $stmt->error
        ];
    }

    echo json_encode($response);
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request'
    ];
    echo json_encode($response);
}
?>
