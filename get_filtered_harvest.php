<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crops_id'])) {
    $crops_id = $_POST['crops_id'];

    $sql = "SELECT 
                h.harvest_id, 
                CONCAT(f.field_name, ' - ', c.crop_name) AS crop_field, 
                a.activity_date, 
                h.harvest_quantity, 
                c.default_sale_price,
                h.harvest_quantity - COALESCE(SUM(s.sales_quantity), 0) AS remaining_quantity
            FROM 
                harvest h
            INNER JOIN 
                cropsfield cf ON h.cropsfield_id = cf.cropsfield_id
            INNER JOIN 
                crops c ON cf.crops_id = c.crops_id
            INNER JOIN 
                fields f ON cf.field_id = f.field_id
            INNER JOIN 
                activities a ON h.activity_id = a.activity_id
            LEFT JOIN 
                sales s ON h.harvest_id = s.harvest_id
            WHERE 
                c.crops_id = ?
            GROUP BY 
                h.harvest_id, crop_field, a.activity_date, h.harvest_quantity, c.default_sale_price
            ORDER BY 
                a.activity_date DESC";

    $stmt = mysqli_prepare($mysqli, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $crops_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error fetching harvest records']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
