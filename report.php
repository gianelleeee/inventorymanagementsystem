<?php
    // Start the session
    session_start();
    if(!isset($_SESSION['user'])) header('location: index.php');

    $user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>IMS Dashboard</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div id="dashboardMainContainer">
        <?php include('partials/sidebar.php') ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/topnav.php') ?>
            <div id="reportsContainer">
                <div class="reportTypeContainer">
                    <div class="reportType">
                        <p>Export Products</p>
                        <div class="alignRight">
                            <a href="database/report_csv.php?report=product" class="reportExportBtn">Excel</a>
                            <a href="database/report_pdf.php?report=product" class="reportExportBtn">PDF</a>
                        </div>
                    </div>
                    <div class="reportType">
                        <p>Export Categories</p>
                        <div class="alignRight">
                            <a href="database/report_csv.php?report=category" class="reportExportBtn">Excel</a>
                            <a href="database/report_pdf.php?report=category" class="reportExportBtn">PDF</a>
                        </div>
                    </div>
                </div>
                <div class="reportTypeContainer">
                    <div class="reportType">
                        <p>Export Deliveries</p>
                        <div class="alignRight">
                            <a href="database/report_csv.php?report=delivery" class="reportExportBtn">Excel</a>
                            <a href="database/report_pdf.php?report=delivery" class="reportExportBtn">PDF</a>
                        </div>
                    </div>
                    <div class="reportType">
                        <p>Export Purchase Orders</p>
                        <div class="alignRight">
                            <a href="database/report_csv.php?report=purchase" class="reportExportBtn">Excel</a>
                            <a href="database/report_pdf.php?report=purchase" class="reportExportBtn">PDF</a>
                        </div>
                    </div>
                </div>
                <div class="reportTypeContainer2">
                    <div class="reportType one">
                        <p>Export Sales</p>
                        <div class="alignRight">
                            <a href="database/report_csv.php?report=sale" class="reportExportBtn">Excel</a>
                            <a href="database/report_pdf.php?report=sale" class="reportExportBtn">PDF</a>
                        </div>
                    </div>
                    <!-- <div class="reportType">
                        <p>Export Users</p>
                        <div class="alignRight">
                            <a href="database/report_csv.php?report=user" class="reportExportBtn">Excel</a>
                            <a href="" class="reportExportBtn">PDF</a>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>

    

    
   
</body>
</html>
