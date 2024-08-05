<?php
include 'config.php';

session_start();

// Check if user is logged in and is a user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

// Fetch sum of sizes of all fields
$sql_sum = "SELECT SUM(field_area) as total_size FROM fields WHERE is_deleted = 0 AND field_status = 'Active'";
$result_sum = mysqli_query($mysqli, $sql_sum);
$total_size = 0;
if (mysqli_num_rows($result_sum) > 0) {
    $row_sum = mysqli_fetch_assoc($result_sum);
    $total_size = $row_sum['total_size'];
}

// Fetch farm details
$farm_id_to_edit = 1; 
$sql_farm = "SELECT * FROM farm_details WHERE farm_id = $farm_id_to_edit";
$result_farm = $mysqli->query($sql_farm);
$row_farm = $result_farm->fetch_assoc();


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
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/icon.png"/>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.min.js"></script>
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
                    <a class="my-nav-link" href="user_weather.php">
                        <span class="my-icon">
                            <ion-icon name="cloudy-night-outline"></ion-icon>
                        </span>
                        <span class="my-title">Weather</span>
                    </a>
                </li>

                <li class="my-nav-item">
                    <a class="my-nav-link" href="user_crops.php">
                        <span class="my-icon">
                            <ion-icon name="leaf-outline"></ion-icon>
                        </span>
                        <span class="my-title">Crops</span>
                    </a>
                </li>

                <li class="my-nav-item">
                    <a class="my-nav-link" href="user_activity.php">
                        <span class="my-icon">
                            <ion-icon name="reader-outline"></ion-icon>
                        </span>
                        <span class="my-title">Activities</span>
                    </a>
                </li>

                <li class="my-nav-item">
                    <a class="my-nav-link" href="user_harvest.php">
                        <span class="my-icon">
                            <ion-icon name="basket-outline"></ion-icon>
                        </span>
                        <span class="my-title">Harvest</span>
                    </a>
                </li>

                <li class="my-nav-item">
                    <a class="my-nav-link" href="user_settings.php">
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
        
        <!-- Analytics Cards -->
        <div class="row">
        <div class="my-cardBox">


                <div class="my-card">
                    <div>
                        <div class="numbers">                            
                            <p id="weather-description"></p>
                        </div>
                        <div class="my-cardName"></div>
                    </div>

                    <div class="iconBx">
                    <ion-icon name="cloud-outline"></ion-icon>                    
                </div>
                </div>

                <div class="my-card">
                    <div>
                        <div class="numbers"><p id="weather-temperature"></p></div>
                        <div class="my-cardName"></div>
                    </div>

                    <div class="iconBx">
                    <ion-icon name="thermometer-outline"></ion-icon>                    
                </div>
                </div>

                <div class="my-card">
                    <div>
                    <div class="numbers"><p id="weather-humidity"></p></div>
                        <div class="my-cardName"></div>
                    </div>

                    <div class="iconBx">
                    <ion-icon name="water-outline"></ion-icon>                    
                </div>
                </div>

                <div class="my-card">
                    <div>
                        <div class="numbers">                                
                        <p id="weather-wind"></p>
                    </div>
                        <div class="my-cardName"></div>
                    </div>

                    <div class="iconBx">
                    <ion-icon name="cloudy-outline"></ion-icon>                   
                </div>
                </div>

                
            </div>
                    </div>
            <!-- Farm and Weather Cards -->
                <div class="row">
                    <!-- Manage Farm Card -->
                    <div class="col-md-6">
                        <div class="my-recentOrders">
                            <div class="my-cardHeader">
                                <h2>Farm Details</h2>
                            </div>
                            <div class="my-card-body">
                                <!-- Edit Farm Card -->
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Farm Name:</h5>
                                        <p class="card-text"><?php echo $row_farm['farm_name']; ?></p>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">Location</h5>
                                        <p class="card-text"><?php echo $row_farm['farm_location']; ?></p>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">Farm Size</h5>
                                        <p class="card-text">Size: <?php echo number_format($total_size, 1); ?> ha</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                <!-- Farm Location Card -->
                    <div class="col-md-6">
                        <div class="my-recentOrders">
                            <div class="my-cardHeader">
                                <h2>Farm Location</h2>
                            </div>
                            <div class="my-card-body">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d2355.223521133829!2d120.6569201329994!3d14.031449540789124!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sph!4v1714331395613!5m2!1sen!2sph" width="600" height="380" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>                                
                                <div class="card">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
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
        $(document).ready(function () {
        const apiKey = "8c8b2ce03ef974a2f3ef27b6c24aaddf";
        const location = "Lian"; 

        $.ajax({
            url: `https://api.openweathermap.org/data/2.5/weather?q=${location}&appid=${apiKey}&units=metric`,
            type: "GET",
            dataType: "jsonp",
            success: function (data) {
            $("#weather-location").text(`${data.name}`);
            $("#weather-description").text(`Description: ${data.weather[0].description}`);
            $("#weather-temperature").text(`Temperature: ${data.main.temp}°C`);
            $("#weather-humidity").text(`Humidity: ${data.main.humidity}%`);
            $("#weather-wind").text(`Wind: ${data.wind.speed} m/s`);
            },
        });
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