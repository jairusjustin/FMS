<?php
include 'config.php';

session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Fetch total seeded area
$sqlSeededArea = "SELECT SUM(area) as totalSeededArea FROM seeded_area";
$resultSeededArea = $mysqli->query($sqlSeededArea);
$rowSeededArea = $resultSeededArea->fetch_assoc();
$totalSeededArea = $rowSeededArea['totalSeededArea'];
$totalSeededAreacard = number_format($totalSeededArea, 2) . " ha";

// Fetch total harvest quantity
$sqlHarvestQuantitycard = "SELECT SUM(harvest_quantity) as totalHarvestQuantity FROM harvest";
$resultHarvestQuantitycard = $mysqli->query($sqlHarvestQuantitycard);
$rowHarvestQuantitycard = $resultHarvestQuantitycard->fetch_assoc();
$totalHarvestQuantitycard = $rowHarvestQuantitycard['totalHarvestQuantity'];
$totalHarvestFormattedcard = number_format($totalHarvestQuantitycard) . " kg";

// Fetch total sales amount
$sqlTotalSalescard = "SELECT SUM(sales_quantity * sale_price) as totalSalesAmount FROM sales";
$resultTotalSalescard = $mysqli->query($sqlTotalSalescard);
$rowTotalSalescard = $resultTotalSalescard->fetch_assoc();
$totalSalescard = $rowTotalSalescard['totalSalesAmount'];
$totalSalesFormattedcard = '₱' . number_format($totalSalescard, 2);

// Fetch total expenses amount
$sqlTotalExpensescard = "SELECT SUM(expense_amount) as totalExpenses FROM expenses";
$resultTotalExpensescard = $mysqli->query($sqlTotalExpensescard);
$rowTotalExpensescard = $resultTotalExpensescard->fetch_assoc();
$totalExpensescard = $rowTotalExpensescard['totalExpenses'];

// Calculate total profit
$totalProfitcard = $totalSalescard - $totalExpensescard;
$totalProfitFormattedcard = '₱' . number_format($totalProfitcard, 2);

$queryDoughnutChartSeededArea = "SELECT 
                                c.crop_name, 
                                ROUND(SUM(sa.area) / (SELECT SUM(area) FROM seeded_area) * 100, 2) AS percentage_seeded_area
                            FROM 
                                crops c
                            JOIN 
                                cropsfield cf ON c.crops_id = cf.crops_id
                            JOIN 
                                seeded_area sa ON cf.cropsfield_id = sa.cropsfield_id
                            GROUP BY 
                                c.crop_name";
$dataPointsDoughnutSeededArea = array();
$resultDoughnutChartSeededArea = $mysqli->query($queryDoughnutChartSeededArea);

if ($resultDoughnutChartSeededArea) {
    while ($row = $resultDoughnutChartSeededArea->fetch_assoc()) {
        $cropName = $row['crop_name'];
        $percentageSeededArea = (float)$row['percentage_seeded_area'];

        $dataPointsDoughnutSeededArea[] = array("cropName" => $cropName, "percentageSeededArea" => $percentageSeededArea);
    }
} else {
    echo "0 results";
}

// Fetch Total Harvest data from your database
$queryBarChartTotalHarvest = "SELECT 
                            c.crop_name, 
                            SUM(h.harvest_quantity) AS total_harvest_quantity
                            FROM 
                            crops c
                            JOIN 
                            cropsfield cf ON c.crops_id = cf.crops_id
                            JOIN 
                            harvest h ON cf.cropsfield_id = h.cropsfield_id
                            GROUP BY 
                            c.crop_name
                            ORDER BY total_harvest_quantity DESC";
$dataPointsBarChartTotalHarvest = array();
$resultBarChartTotalHarvest = $mysqli->query($queryBarChartTotalHarvest);

if ($resultBarChartTotalHarvest) {
    while ($row = $resultBarChartTotalHarvest->fetch_assoc()) {
        $cropName = $row['crop_name'];
        $totalHarvestQuantity = (float)$row['total_harvest_quantity'];

        $dataPointsBarChartTotalHarvest[] = array("cropName" => $cropName, "totalHarvestQuantity" => $totalHarvestQuantity);
    }
} else {
    echo "0 results";
}

// Fetch Total Sales per Crop 
$queryTotalSalesPerCrop = "SELECT 
                        c.crop_name, 
                        COALESCE(SUM(s.sales_quantity * s.sale_price), 0) AS total_sales                        
                        FROM 
                        crops c
                        LEFT JOIN 
                        cropsfield cf ON c.crops_id = cf.crops_id
                        LEFT JOIN 
                        harvest h ON cf.cropsfield_id = h.cropsfield_id
                        LEFT JOIN 
                        sales s ON h.harvest_id = s.harvest_id
                        WHERE 
                        c.is_deleted = 0
                        GROUP BY 
                        c.crop_name
                        ORDER BY total_sales DESC;
                        ";
$resultTotalSalesPerCrop = mysqli_query($mysqli, $queryTotalSalesPerCrop);

$totalSalesPerCrop = array(); 

if ($resultTotalSalesPerCrop && mysqli_num_rows($resultTotalSalesPerCrop) > 0) {
    while ($rowTotalSalesPerCrop = mysqli_fetch_assoc($resultTotalSalesPerCrop)) {
        $totalSalesPerCrop[$rowTotalSalesPerCrop['crop_name']] = (float)$rowTotalSalesPerCrop['total_sales'];
    }
} else {
    echo "0 results";
}

// Fetch total expense amount per activity type
$queryTotalExpensesPerActivity = "SELECT a.activity_type, SUM(e.expense_amount) AS totalExpenseAmount
                            FROM expenses e
                            INNER JOIN activities a ON e.activity_id = a.activity_id
                            GROUP BY a.activity_type
                            ORDER BY totalExpenseAmount DESC";
$resultTotalExpensesPerActivity = $mysqli->query($queryTotalExpensesPerActivity);

$expensesData = array(); 

if ($resultTotalExpensesPerActivity && $resultTotalExpensesPerActivity->num_rows > 0) {
    while ($row = $resultTotalExpensesPerActivity->fetch_assoc()) {
        $totalExpenseAmount = $row['totalExpenseAmount'];
        $activityType = $row['activity_type'];

        $expensesData[] = array("totalExpenseAmount" => $totalExpenseAmount, "activityType" => $activityType);
    }
} else {
    echo "0 results";
}

// SQL query to fetch profit/loss data per crop
$queryProfitLossPerCrop = "SELECT s.crop_name, 
                        s.sales,
                        e.expenses,
                        (s.sales - e.expenses) AS profit_loss,
                        CASE
                            WHEN (s.sales - e.expenses) >= 0 THEN 'Profit'
                            ELSE 'Loss'
                        END AS profit_status
                        FROM
                        (
                        SELECT c.crop_name, 
                            SUM(s.sales_quantity * s.sale_price) AS sales
                        FROM crops c
                        JOIN cropsfield cf ON c.crops_id = cf.crops_id
                        JOIN harvest h ON cf.cropsfield_id = h.cropsfield_id
                        JOIN sales s ON h.harvest_id = s.harvest_id
                        GROUP BY c.crops_id, c.crop_name
                        ) s
                        JOIN
                        (
                        SELECT c.crop_name, 
                            SUM(e.expense_amount) AS expenses
                        FROM crops c
                        JOIN cropsfield cf ON c.crops_id = cf.crops_id
                        JOIN activities a ON cf.cropsfield_id = a.cropsfield_id
                        JOIN expenses e ON a.activity_id = e.activity_id
                        GROUP BY c.crops_id, c.crop_name
                        ) e ON s.crop_name = e.crop_name
                        ORDER BY profit_loss DESC;";

// Fetch profit/loss data per crop
$resultProfitLossPerCrop = $mysqli->query($queryProfitLossPerCrop);

$profitLossData = array(); 

if ($resultProfitLossPerCrop && $resultProfitLossPerCrop->num_rows > 0) {
    while ($row = $resultProfitLossPerCrop->fetch_assoc()) {
        $cropName = $row['crop_name'];
        $profitLoss = $row['profit_loss'];

        $profitLossData[] = array("cropName" => $cropName, "profitLoss" => $profitLoss);
    }
} else {
    echo "0 results";
}

$queryMonthlyProfit = "SELECT 
                month,
                sales,
                expenses,
                profit,
                SUM(profit) OVER (ORDER BY month) AS cumulative_profit
                FROM
                (SELECT 
                    COALESCE(s.month, e.month) AS month,
                    COALESCE(s.sales_amount, 0) AS sales,
                    COALESCE(e.expense_amount, 0) AS expenses,
                    COALESCE(s.sales_amount, 0) + COALESCE(e.expense_amount, 0) AS profit
                FROM 
                    (SELECT 
                        DATE_FORMAT(sale_date, '%Y-%m') AS month,
                        SUM(sales_quantity * sale_price) AS sales_amount
                    FROM 
                        sales
                    GROUP BY 
                        month) s
                LEFT JOIN
                    (SELECT 
                        DATE_FORMAT(a.activity_date, '%Y-%m') AS month,
                        -SUM(e.expense_amount) AS expense_amount
                    FROM 
                        activities a
                    JOIN 
                        expenses e ON a.activity_id = e.activity_id
                    GROUP BY 
                        month) e ON s.month = e.month

                UNION

                SELECT 
                    COALESCE(s.month, e.month) AS month,
                    COALESCE(s.sales_amount, 0) AS sales,
                    COALESCE(e.expense_amount, 0) AS expenses,
                    COALESCE(s.sales_amount, 0) + COALESCE(e.expense_amount, 0) AS profit
                FROM 
                    (SELECT 
                        DATE_FORMAT(sale_date, '%Y-%m') AS month,
                        SUM(sales_quantity * sale_price) AS sales_amount
                    FROM 
                        sales
                    GROUP BY 
                        month) s
                RIGHT JOIN
                    (SELECT 
                        DATE_FORMAT(a.activity_date, '%Y-%m') AS month,
                        -SUM(e.expense_amount) AS expense_amount
                    FROM 
                        activities a
                    JOIN 
                        expenses e ON a.activity_id = e.activity_id
                    GROUP BY 
                        month) e ON s.month = e.month
                ) total_financial
                ORDER BY 
                month;";

$dataPointsLineMonthly = array();
$resultMonthlyProfit = $mysqli->query($queryMonthlyProfit);

if ($resultMonthlyProfit) {
    while ($row = $resultMonthlyProfit->fetch_assoc()) {
        $month = $row['month']; 
        $profit = (float)$row['cumulative_profit'];
        $dataPointsLineMonthly[] = array("month" => $month, "cumulative_profit" => $profit);
    }
}

$queryYearlyProfit = "SELECT 
                    year,
                    profit,
                    SUM(profit) OVER (ORDER BY year) AS cumulative_profit
                    FROM
                    (SELECT 
                        s.year AS year,
                        COALESCE(s.sales_amount, 0) + COALESCE(e.expense_amount, 0) AS profit
                    FROM 
                        (SELECT 
                            YEAR(sale_date) AS year,
                            SUM(sales_quantity * sale_price) AS sales_amount
                        FROM 
                            sales
                        GROUP BY 
                            year) s
                    LEFT JOIN
                        (SELECT 
                            YEAR(a.activity_date) AS year,
                            -SUM(e.expense_amount) AS expense_amount
                        FROM 
                            activities a
                        JOIN 
                            expenses e ON a.activity_id = e.activity_id
                        GROUP BY 
                            year) e ON s.year = e.year

                    UNION

                    SELECT 
                        e.year AS year,
                        COALESCE(s.sales_amount, 0) + COALESCE(e.expense_amount, 0) AS profit
                    FROM 
                        (SELECT 
                            YEAR(sale_date) AS year,
                            SUM(sales_quantity * sale_price) AS sales_amount
                        FROM 
                            sales
                        GROUP BY 
                            year) s
                    RIGHT JOIN
                        (SELECT 
                            YEAR(a.activity_date) AS year,
                            -SUM(e.expense_amount) AS expense_amount
                        FROM 
                            activities a
                        JOIN 
                            expenses e ON a.activity_id = e.activity_id
                        GROUP BY 
                            year) e ON s.year = e.year
                    ) total_financial
                    ORDER BY 
                    year;";
$dataPointsLineYearly = array();
$resultYearlyProfit = $mysqli->query($queryYearlyProfit);

if ($resultYearlyProfit) {
    while ($row = $resultYearlyProfit->fetch_assoc()) {
        $year = $row['year'];
        $profit = (float)$row['cumulative_profit'];
        $dataPointsLineYearly[] = array("year" => $year, "cumulative_profit" => $profit);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saka-Insights</title>
    <!-- ======= Styles ====== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/dtable_style.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/icon.png"/>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js Doughnut Label Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-doughnutlabel"></script>
</head>
<body>
<div class="my-container">
    <div class="row">
        <!-- Navigation Sidebar -->
        <div class="col-md-2">
            <div class="my-navigation active">
                <ul class="my-nav">
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="#">
                            <span class="my-icon">
                                <img src="images/logo.png" alt="Your Logo">
                            </span>
                            <span class="my-title">Saka-Insights</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_dashboard.php">
                            <span class="my-icon">
                                <ion-icon name="stats-chart-outline"></ion-icon>
                            </span>
                            <span class="my-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_weather.php">
                            <span class="my-icon">
                                <ion-icon name="cloudy-night-outline"></ion-icon>
                            </span>
                            <span class="my-title">Weather</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_crops.php">
                            <span class="my-icon">
                                <ion-icon name="leaf-outline"></ion-icon>
                            </span>
                            <span class="my-title">Crops</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_activity.php">
                            <span class="my-icon">
                                <ion-icon name="reader-outline"></ion-icon>
                            </span>
                            <span class="my-title">Activities</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_harvest.php">
                            <span class="my-icon">
                                <ion-icon name="basket-outline"></ion-icon>
                            </span>
                            <span class="my-title">Harvest</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_sales.php">
                            <span class="my-icon">
                                <ion-icon name="cash-outline"></ion-icon>
                            </span>
                            <span class="my-title">Sales</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_expenses.php">
                            <span class="my-icon">
                                <ion-icon name="receipt-outline"></ion-icon>
                            </span>
                            <span class="my-title">Expenses</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_users.php">
                            <span class="my-icon">
                                <ion-icon name="people-outline"></ion-icon>
                            </span>
                            <span class="my-title">Users</span>
                        </a>
                    </li>
                    <li class="my-nav-item">
                        <a class="my-nav-link" href="admin_settings.php">
                            <span class="my-icon">
                                <ion-icon name="settings-outline"></ion-icon>
                            </span>
                            <span class="my-title">Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- ========================= Main ==================== -->
        <div class="col-md-10">
            <div class="my-main active">
                <div class="my-topbar">
                    <div class="my-toggle">
                        <ion-icon name="menu-outline"></ion-icon>
                    </div>
                    <div class="my-user" onclick="signOut()">
                        <ion-icon name="log-out-outline" class="my-settings-icon"></ion-icon>
                    </div>
                </div>

        
<!-- ======================= Cards ================== -->
<div class="my-cardBox">
    <div class="my-card">
        <div>
            <div class="numbers"><?php echo $totalSeededAreacard; ?></div>
            <div class="my-cardName">Total Seeded Area</div>
        </div>
        <div class="iconBx">
            <ion-icon name="map-outline"></ion-icon>
        </div>
    </div>
    <div class="my-card">
        <div>
            <div class="numbers"><?php echo $totalHarvestFormattedcard; ?></div>
            <div class="my-cardName"> Total Harvest</div>
        </div>
        <div class="iconBx">
            <ion-icon name="basket-outline"></ion-icon>
        </div>
    </div>
    <div class="my-card">
        <div>
            <div class="numbers"><?php echo $totalSalesFormattedcard; ?></div>
            <div class="my-cardName">Total Sales</div>
        </div>
        <div class="iconBx">
            <ion-icon name="cash-outline"></ion-icon>
        </div>
    </div>
    <div class="my-card">
        <div>
            <div class="numbers"><?php echo $totalProfitFormattedcard; ?></div>
            <div class="my-cardName">Profit</div>
            <button class="btn addCropsBtn" onclick="openPredict()" style="color: white;" onmouseover="this.style.color='white';" onmouseout="this.style.color='white';">Prediction</button>
        </div>
        <div class="iconBx">
            <ion-icon name='trending-up-outline'></ion-icon>
        </div>
    </div>
</div>

<!-- Charts Cards -->
<div class="row">
    <!-- Seaded Area Card -->
    <div class="col-md-4">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Summary Seeded area.</h3>
            </div>
            <canvas id="doughnutChartSeededArea" width="400" height="400"></canvas>
        </div>
    </div>
    <!--  Harvest Card -->
    <div class="col-md-4">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Total Harvest Comparison</h3>
            </div>
            <canvas id="barChartTotalHarvest" width="400" height="400"></canvas>
        </div>
    </div>
    <!-- Profit Trend Card -->
    <div class="col-md-4">
        <div class="my-recentOrders">
            <div class="my-cardHeader">
                <h3>Profit Trend</h3>
                <div class="my-actions">
                    <button class="btn addCropsBtn" id="btnMonthly">Monthly</button>
                    <button class="btn addCropsBtn" id="btnYearly">Yearly</button>
                </div>
            </div>
            <canvas id="lineChartMonthlyProfitLoss" width="400" height="400"></canvas>
            <canvas id="lineChartYearlyProfitLoss" width="400" height="400"></canvas>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="my-recentOrders" id="financialsummary" style="margin-top: 20px; padding-bottom: 20px;">
            <div class="my-cardHeader">
                <h3  id="financialSummaryHeading">Financial Summary</h3>
                <div class="my-actions">
                    <button class="btn addCropsBtn" id="btnIncome">Sales</button>
                    <button class="btn addCropsBtn" id="btnExpenses">Expenses</button>
                    <button class="btn addCropsBtn" id="btnProfitLoss">Profit</button>
                </div>
            </div>
            <div class="my-card text-center" style="max-width: 400px; margin: 0 auto;">
                <canvas id="barChartExpenses" width="400" height="400"></canvas>
                <canvas id="barChartTotalSales" width="400" height="400"></canvas>
                <canvas id="barChartProfitLoss" width="400" height="400"></canvas>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>

<!-- Profit Prediction Modal -->
<div class="modal fade" id="apiModal" tabindex="-1" aria-labelledby="apiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apiModalLabel">Profit Prediction Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="apiForm">
                    <div class="mb-3">
                        <label for="cropName">Crop Name:</label>
                        <select class="form-select" id="cropName" name="cropName" required>
                            <option value="">Select Crop Name</option>
                            <option value="0">Watermelon</option>
                            <option value="3">Melon</option>
                            <option value="1">Rice</option>
                            <option value="2">Sugarcane</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="seededArea">Seeded Area:</label>
                        <input type="text" class="form-control" id="seededArea" name="seededArea" required>
                    </div>
                    <div class="mb-3">
                        <label for="predProfit">Predicted Profit:</label>
                        <input type="text" class="form-control" id="predProfit" name="predProfit" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <div id="apicontent"></div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap Data Tables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<!-- =========== Scripts =========  -->
<script src="js/script.js"></script>

<!-- ====== ionicons ======= -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<script>
$(document).ready(() => {
    $("#apiForm").submit((event) => {
        event.preventDefault();

        let cropName = $("#cropName")[0].value;
        let seededArea = parseFloat($("#seededArea")[0].value);

        $("#predProfit").val("Loading... Please wait...");

        $.ajax({
            url: "https://jairusjustin.pythonanywhere.com/api/predict_profit",
            method: 'POST',
            contentType: "application/json",
            data: JSON.stringify({
                "CropName": cropName,
                "SeededArea": seededArea
            }),
            headers: {
                "Access-Control-Allow-Origin": '*',
                "Access-Control-Allow-Methods": "*"
            },
            success: function(res) {
                let formattedProfit = '₱' + parseFloat(res.profit_prediction).toFixed(2);

                $("#predProfit").val(formattedProfit);
            },
            error: function(xhr, status, error) {
                console.log("Error:", error);
            }
        });
    });
});

function openPredict() {
    $('#apiModal').modal('show');
}

const colors = [
    '#12372A',
    '#436850',
    '#ADBC9F',
    '#D1DCC2',
    '#2A4D39',
    '#509962',
    '#8BBE8A',
    '#B3D3B2',
    '#3A6148',
    '#6F9D75',
    '#A6C29F',
    '#C9E1C0',
    '#25374D',
    '#435270',
    '#A3B3C9',
    '#D2DCE4',
    '#4D5A72',
    '#6E7F96',
    '#9AA9BC',
    '#CED5DC'
];

// Get the canvas element
const ctx = document.getElementById('doughnutChartSeededArea').getContext('2d');

// Create the doughnut chart
const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($dataPointsDoughnutSeededArea, 'cropName')); ?>,
        datasets: [{
            label: 'Seeded Area per Crop (%)',
            data: <?php echo json_encode(array_column($dataPointsDoughnutSeededArea, 'percentageSeededArea')); ?>,
            backgroundColor: colors.slice(0, <?php echo count($dataPointsDoughnutSeededArea); ?>),
            borderColor: '#D1DCC2',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
            },
            title: {
                display: true,
                text: 'Seeded Area per Crop (%) of Total Seeded Area'
            },
        }
    }
});

// Get the canvas element
const barharvestctx = document.getElementById('barChartTotalHarvest').getContext('2d');

// Create the bar chart for Total Harvest Comparison
const barchart = new Chart(barharvestctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($dataPointsBarChartTotalHarvest, 'cropName')); ?>,
        datasets: [{
            label: 'Total Harvest Quantity',
            data: <?php echo json_encode(array_column($dataPointsBarChartTotalHarvest, 'totalHarvestQuantity')); ?>,
            backgroundColor: [
                '#436850',
            ],
            borderColor: '#D1DCC2',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false,
            },
            title: {
                display: true,
                text: 'Total Harvest Quantity per Crops'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Total Harvest Quantity'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Crop Name'
                }
            }
        }
    }
});
// Get the canvas element for the bar chart
const ctxBarSales = document.getElementById('barChartTotalSales').getContext('2d');

const cropNames = <?php echo json_encode(array_keys($totalSalesPerCrop)); ?>;

const barChartTotalSales = new Chart(ctxBarSales, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($totalSalesPerCrop)); ?>,
        datasets: [{
            label: 'Total Sales Amount',
            data: <?php echo json_encode(array_values($totalSalesPerCrop)); ?>,
            backgroundColor: '#ADBC9F',
            borderColor: '#D1DCC2',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                display: false,
            },
            title: {
                display: true,
                text: 'Total Sales by Crops'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Total Sales Amount'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Crop Name'
                }
            }
        }
    }
});

document.getElementById('btnMonthly').addEventListener('click', function() {
    document.getElementById('lineChartMonthlyProfitLoss').style.display = 'block';
    document.getElementById('lineChartYearlyProfitLoss').style.display = 'none';
    lineChartMonthlyProfitLoss.update();
});

document.getElementById('btnYearly').addEventListener('click', function() {
    document.getElementById('lineChartMonthlyProfitLoss').style.display = 'none';
    document.getElementById('lineChartYearlyProfitLoss').style.display = 'block';
    lineChartYearlyProfitLoss.update();
});

// Get the canvas element for the line chart
const ctxLineMonthlyProfitLoss = document.getElementById('lineChartMonthlyProfitLoss').getContext('2d');

// Create the line chart for Monthly Profit/Loss
const lineChartMonthlyProfitLoss = new Chart(ctxLineMonthlyProfitLoss, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($dataPointsLineMonthly, 'month')); ?>,
        datasets: [{
            label: 'Monthly Profit/Loss',
            data: <?php echo json_encode(array_column($dataPointsLineMonthly, 'cumulative_profit')); ?>,
            borderColor: '#12372A',
            backgroundColor: 'rgba(18, 55, 42, 0.2)',
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: '#12372A',
            pointBorderColor: '#fff',
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#12372A',
            pointHoverBorderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false,
                position: 'top',
            },
            title: {
                display: true,
                text: 'Monthly Profit/Loss Chart',
            },
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Month',
                },
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Profit/Loss',
                },
                ticks: {
                    callback: function(value, index, values) {
                        return '₱' + value.toFixed(2);
                    }
                }
            }
        }
    }
});

// Get the canvas element for the line chart
const ctxLineYearlyProfitLoss = document.getElementById('lineChartYearlyProfitLoss').getContext('2d');

// Create the line chart for Yearly Profit/Loss
const lineChartYearlyProfitLoss = new Chart(ctxLineYearlyProfitLoss, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($dataPointsLineYearly, 'year')); ?>,
        datasets: [{
            label: 'Yearly Profit/Loss',
            data: <?php echo json_encode(array_column($dataPointsLineYearly, 'cumulative_profit')); ?>,
            borderColor: '#12372A',
            backgroundColor: 'rgba(18, 55, 42, 0.2)',
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: '#12372A',
            pointBorderColor: '#fff',
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#12372A',
            pointHoverBorderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false,
                position: 'top',
            },
            title: {
                display: true,
                text: 'Yearly Profit/Loss Chart',
            },
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Year',
                },
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Profit/Loss',
                },
                ticks: {
                    callback: function(value, index, values) {
                        return '₱' + value.toFixed(2);
                    }
                }
            }
        }
    }
});

// Show the Total Sales when the page loads
document.getElementById('lineChartMonthlyProfitLoss').style.display = 'block';
document.getElementById('lineChartYearlyProfitLoss').style.display = 'none';
document.getElementById('barChartTotalSales').style.display = 'block';
document.getElementById('barChartExpenses').style.display = 'none';
document.getElementById('barChartProfitLoss').style.display = 'none';

// Button click event handlers
document.getElementById('btnIncome').addEventListener('click', function() {
    // Handle Income button click
    document.getElementById('barChartTotalSales').style.display = 'block';
    document.getElementById('barChartExpenses').style.display = 'none';
    document.getElementById('barChartProfitLoss').style.display = 'none';
    barChartTotalSales.update();
    console.log('Income button clicked');
});

document.getElementById('btnExpenses').addEventListener('click', function() {
    // Handle Expenses button click
    document.getElementById('barChartTotalSales').style.display = 'none';
    document.getElementById('barChartExpenses').style.display = 'block';
    document.getElementById('barChartProfitLoss').style.display = 'none';
    barChartExpenses.update();
    console.log('Expenses button clicked');
});

document.getElementById('btnProfitLoss').addEventListener('click', function() {
    // Handle Profit/Loss button click
    document.getElementById('barChartTotalSales').style.display = 'none';
    document.getElementById('barChartExpenses').style.display = 'none';
    document.getElementById('barChartProfitLoss').style.display = 'block';
    console.log('Profit/Loss button clicked');
});

// Get the canvas element for the expenses bar chart
const ctxBarExpenses = document.getElementById('barChartExpenses').getContext('2d');

// Create the bar chart for total expenses per activity type
const barChartExpenses = new Chart(ctxBarExpenses, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($expensesData, 'activityType')); ?>,
        datasets: [{
            label: 'Total Expenses',
            data: <?php echo json_encode(array_column($expensesData, 'totalExpenseAmount')); ?>,
            backgroundColor:
                '#436850',
            borderWidth: 1
        }]
    },
    options: {
        responsive: false,
        plugins: {
            legend: {
                position: 'top',
                display: false,
            },
            title: {
                display: true,
                text: 'Total Expenses by Activity'
            }
        },
        scales: {
            y: {
                title: {
                    display: true,
                    text: 'Total Expense Amount'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Activity Type'
                }
            }
        }
    }
});

// Get the canvas element for the bar chart
const ctxBarProfitLoss = document.getElementById('barChartProfitLoss').getContext('2d');

// Create the bar chart for Profit/Loss per Crop
const barChartProfitLoss = new Chart(ctxBarProfitLoss, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($profitLossData, 'cropName')); ?>,
        datasets: [{
            label: 'Profit/Loss Amount',
            data: <?php echo json_encode(array_column($profitLossData, 'profitLoss')); ?>,
            backgroundColor: function(context) {
                const value = context.dataset.data[context.dataIndex];
                return value >= 0 ? '#12372A' : '#D32F2F';
            },
            borderColor: '#D1DCC2',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                display: false,
            },
            title: {
                display: true,
                text: 'Profit / Loss by Crops'
            }
        },
        scales: {
            y: {
                title: {
                    display: true,
                    text: 'Profit/Loss Amount'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Crop Name'
                }
            }
        }
    }
});

// Sign Out
function signOut() {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to sign out.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#436850',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, sign out!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "logout.php";
        }
    });
}
</script>
</body>
</html>
