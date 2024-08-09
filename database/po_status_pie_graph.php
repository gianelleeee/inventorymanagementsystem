<?php

include('connection.php');

$statuses = ['pending', 'complete', 'incomplete'];

$results = [];

//loop through statuses and query
foreach($statuses as $status){
    $stmt = $conn->prepare("SELECT COUNT(*) as status_count FROM order_product WHERE order_product.status='" . $status . "'");
    $stmt->execute();
    $row = $stmt->fetch();

    $count = $row['status_count'];
    

    $results[] = [
        'name'=> strtoupper($status),
        'y'=> (int)$count
    ];

};

$stmt = $conn->prepare("
    SELECT category.category_name, sales_product.date, SUM(sales_product.sales) AS total_sales
    FROM sales_product
    JOIN category ON sales_product.category = category.id
    GROUP BY category.category_name, sales_product.date
    ORDER BY sales_product.date ASC
");
$stmt->execute();
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$dates = [];
$data_by_category = [];

// Organize data by category and date
foreach ($sales_data as $row) {
    $category = $row['category_name'];
    $date = $row['date'];
    $sales = $row['total_sales'];

    // Add category if not already added
    if (!in_array($category, $categories)) {
        $categories[] = $category;
    }

    // Add date if not already added
    if (!in_array($date, $dates)) {
        $dates[] = $date;
    }

    // Prepare data structure
    if (!isset($data_by_category[$category])) {
        $data_by_category[$category] = [];
    }

    $data_by_category[$category][$date] = $sales;
}

// Ensure all dates have data points, even if zero sales
foreach ($categories as $category) {
    foreach ($dates as $date) {
        if (!isset($data_by_category[$category][$date])) {
            $data_by_category[$category][$date] = 0;
        }
    }
}


?>