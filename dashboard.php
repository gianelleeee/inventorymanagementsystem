<?php
    // Start the session
    session_start();
    if(!isset($_SESSION['user'])) header('location: index.php');

    $user = $_SESSION['user'];

    // Get graph data - purchase order by status
    include('database/po_status_pie_graph.php');
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
            <div class="dashboard_content">
                <div class="dashboard_content_main">
                     <div>
                        <canvas id="salesCategoryChart" width="400" height="200"></canvas>
                    </div>
                    <div>
                        <figure class="highcharts-figure">
                            <div id="container"></div>
                            <p class="highcharts-description">
                                Here is the breakdown of purchase orders by status.
                            </p>
                        </figure>
                    </div id="deliveryHistory">
                    <div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script>
        var graphData = <?= json_encode($results) ?>;
        Highcharts.chart('container', {
            chart: {
                type: 'pie',
                backgroundColor: '#f4f4f4', // Light background
            },
            title: {
                text: 'Purchase Order By Status',
                style: {
                    color: '#333',
                    fontSize: '20px',
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                pointFormatter: function() {
                    return `<b>${this.name}</b>: ${this.y} (${this.percentage.toFixed(1)}%)`;
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '{point.name}: {point.y} ({point.percentage:.1f}%)',
                        style: {
                            fontSize: '14px',
                            color: '#333',
                        },
                        distance: -30, // Adjust label distance from the pie slices
                        filter: {
                            property: 'percentage',
                            operator: '>',
                            value: 5
                        }
                    },
                    showInLegend: false // Disable legend
                }
            },
            series: [{
                name: 'Status',
                colorByPoint: true,
                data: graphData,
                colors: ['#a6cee3', '#b2df8a', '#fb9a99', '#fdbf6f', '#cab2d6', '#ffff99', '#1f78b4', '#33a02c', '#e31a1c']
            }],
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        plotOptions: {
                            pie: {
                                dataLabels: {
                                    distance: -20
                                }
                            }
                        }
                    }
                }]
            }
        });

        
    </script>
    <script>
        
        // Prepare the data for Chart.js
        const labels = <?= json_encode($dates) ?>;

        const datasets = [
            <?php foreach ($categories as $category): ?>
            {
                label: '<?= $category ?>',
                data: <?= json_encode(array_values($data_by_category[$category])) ?>,
                fill: false,
                borderColor: '<?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)) ?>',
                tension: 0.1
            },
            <?php endforeach; ?>
        ];

        // Create the line chart
        const ctx = document.getElementById('salesCategoryChart').getContext('2d');
        const salesCategoryChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Sales'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Sales per Category',
                        font: {
                            size: 24 // Adjust the font size here
                        }
                    }
                }
            }
        });
        </script>
    

    
   
</body>
</html>
