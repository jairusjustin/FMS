<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sale_id'])) {
    $saleId = $_POST['sale_id'];

    $sql = "SELECT 
    s.sale_id, 
    s.sale_date, 
    s.sales_quantity, 
    s.sale_price, 
    s.sales_quantity * s.sale_price AS total_price, 
    h.harvest_id, 
    CONCAT(f.field_name, ' - ', c.crop_name) AS crop_field, 
    h.harvest_quantity - COALESCE((SELECT SUM(sales_quantity) 
                                   FROM sales 
                                   WHERE harvest_id = h.harvest_id 
                                   AND sale_id != s.sale_id), 0) AS remaining_quantity 
FROM 
    sales s 
JOIN 
    harvest h ON s.harvest_id = h.harvest_id 
JOIN 
    cropsfield cf ON h.cropsfield_id = cf.cropsfield_id 
JOIN 
    crops c ON cf.crops_id = c.crops_id 
JOIN 
    fields f ON cf.field_id = f.field_id 
WHERE 
    s.sale_id = ?;
";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $saleId);
    $stmt->execute();
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $response = [
            'status' => 'success',
            'data' => [
                'sale_id' => $row['sale_id'],
                'sale_date' => $row['sale_date'],
                'sales_quantity' => $row['sales_quantity'],
                'sale_price' => $row['sale_price'],
                'total_price' => $row['total_price'],
                'harvest_id' => $row['harvest_id'],
                'crop_field' => $row['crop_field'],
                'remaining_quantity' => $row['remaining_quantity']
            ]
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Sale not found'
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
