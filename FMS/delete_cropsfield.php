<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cropsfield_id'])) {
    $cropsfieldId = $_POST['cropsfield_id'];

    // Delete expenses related to the activity
    $deleteExpenses = "DELETE FROM expenses WHERE activity_id IN (SELECT activity_id FROM activities WHERE cropsfield_id = ?)";
    $stmtDeleteExpenses = mysqli_prepare($mysqli, $deleteExpenses);
    mysqli_stmt_bind_param($stmtDeleteExpenses, "i", $cropsfieldId);
    mysqli_stmt_execute($stmtDeleteExpenses);
    mysqli_stmt_close($stmtDeleteExpenses);

    // Check if there are related harvests for the activity
    $sqlHarvest = "SELECT harvest_id FROM harvest WHERE activity_id IN (SELECT activity_id FROM activities WHERE cropsfield_id = ?)";
    $stmtHarvest = mysqli_prepare($mysqli, $sqlHarvest);
    mysqli_stmt_bind_param($stmtHarvest, "i", $cropsfieldId);
    mysqli_stmt_execute($stmtHarvest);
    $resultHarvest = mysqli_stmt_get_result($stmtHarvest);

    if ($resultHarvest->num_rows > 0) {
        // There are related harvests, check for associated sales and delete them
        while ($rowHarvest = mysqli_fetch_assoc($resultHarvest)) {
            $harvestId = $rowHarvest['harvest_id'];

            // Check if there are related sales for the harvest
            $sqlSales = "SELECT sale_id FROM sales WHERE harvest_id = ?";
            $stmtSales = mysqli_prepare($mysqli, $sqlSales);
            mysqli_stmt_bind_param($stmtSales, "i", $harvestId);
            mysqli_stmt_execute($stmtSales);
            $resultSales = mysqli_stmt_get_result($stmtSales);

            if ($resultSales->num_rows > 0) {
                // Delete sales records
                $deleteSales = "DELETE FROM sales WHERE harvest_id = ?";
                $stmtDeleteSales = mysqli_prepare($mysqli, $deleteSales);
                mysqli_stmt_bind_param($stmtDeleteSales, "i", $harvestId);
                mysqli_stmt_execute($stmtDeleteSales);
                mysqli_stmt_close($stmtDeleteSales);
            }

            // Delete harvest records
            $deleteHarvest = "DELETE FROM harvest WHERE harvest_id = ?";
            $stmtDeleteHarvest = mysqli_prepare($mysqli, $deleteHarvest);
            mysqli_stmt_bind_param($stmtDeleteHarvest, "i", $harvestId);
            mysqli_stmt_execute($stmtDeleteHarvest);
            mysqli_stmt_close($stmtDeleteHarvest);
        }
    }

    // Check if the cropsfield ID is in the seeded area table and delete it
    $sqlCheckSeededArea = "SELECT * FROM seeded_area WHERE cropsfield_id = ?";
    $stmtCheckSeededArea = mysqli_prepare($mysqli, $sqlCheckSeededArea);
    mysqli_stmt_bind_param($stmtCheckSeededArea, "i", $cropsfieldId);
    mysqli_stmt_execute($stmtCheckSeededArea);
    $resultSeededArea = mysqli_stmt_get_result($stmtCheckSeededArea);

    if ($resultSeededArea->num_rows > 0) {
        // Delete the cropsfield from the seeded area table
        $deleteSeededArea = "DELETE FROM seeded_area WHERE cropsfield_id = ?";
        $stmtDeleteSeededArea = mysqli_prepare($mysqli, $deleteSeededArea);
        mysqli_stmt_bind_param($stmtDeleteSeededArea, "i", $cropsfieldId);
        mysqli_stmt_execute($stmtDeleteSeededArea);
        mysqli_stmt_close($stmtDeleteSeededArea);
    }

    // Delete the cropsfield record
    $deleteCropsfield = "DELETE FROM cropsfield WHERE cropsfield_id = ?";
    $stmtDeleteCropsfield = mysqli_prepare($mysqli, $deleteCropsfield);
    mysqli_stmt_bind_param($stmtDeleteCropsfield, "i", $cropsfieldId);
    mysqli_stmt_execute($stmtDeleteCropsfield);
    mysqli_stmt_close($stmtDeleteCropsfield);

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>