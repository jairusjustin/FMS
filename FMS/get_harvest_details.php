<?php
include 'config.php';

if(isset($_POST['harvest_id'])) {
    $harvestId = $_POST['harvest_id'];

    $sql = "SELECT 
                h.harvest_id, 
                CONCAT(f.field_name, ' - ', c.crop_name) AS crop_field, 
                a.activity_date, 
                h.harvest_quantity, 
                c.default_sale_price,
                h.harvest_quantity - COALESCE(SUM(s.sales_quantity), 0) AS remaining_quantity,
                cf.cropsfield_id
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
                h.harvest_id = ?
            GROUP BY 
                h.harvest_id, crop_field, a.activity_date, h.harvest_quantity, c.default_sale_price, cf.cropsfield_id";

    if($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $harvestId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $harvestDetails = $result->fetch_assoc();

        echo json_encode(['status' => 'success', 'data' => [$harvestDetails]]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error occurred while fetching harvest details.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Harvest ID is not set.']);
}
?>
