<?php

include('connection.php');

$statuses = ['pending', 'complete', 'incomplete'];

$results = [];

// Loop through statuses and query
foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) as status_count FROM order_product WHERE order_product.status = ?");
    $stmt->execute([$status]);
    $row = $stmt->fetch();

    $count = $row['status_count'];

    $results[] = [
        'name' => strtoupper($status),
        'y' => (int)$count
    ];
}

// Fetch sales data by category and date
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

// Fetch sales data per product
$stmt = $conn->prepare("
    SELECT category.category_name, products.product_name, sales_product.date, SUM(sales_product.sales) AS total_sales
    FROM sales_product
    JOIN products ON sales_product.product = products.id
    JOIN category ON sales_product.category = category.id
    GROUP BY category.category_name, products.product_name, sales_product.date
    ORDER BY sales_product.date ASC
");
$stmt->execute();
$product_sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$products = [];
$product_data_by_category = [];
$data_by_date = [];

// Organize data by category, product, and date
foreach ($product_sales_data as $row) {
    $category = $row['category_name'];
    $product = $row['product_name'];
    $date = $row['date'];
    $sales = $row['total_sales'];

    // Add category if not already added
    if (!isset($product_data_by_category[$category])) {
        $product_data_by_category[$category] = [];
    }

    // Add product if not already added
    if (!in_array($product, $products)) {
        $products[] = $product;
    }

    // Prepare data structure
    if (!isset($product_data_by_category[$category][$product])) {
        $product_data_by_category[$category][$product] = [];
    }

    $product_data_by_category[$category][$product][$date] = $sales;

    // Track dates
    if (!in_array($date, $data_by_date)) {
        $data_by_date[] = $date;
    }
}

// Ensure all dates have data points, even if zero sales
foreach ($products as $product) {
    foreach ($product_data_by_category as $category => $data) {
        foreach ($data_by_date as $date) {
            if (!isset($product_data_by_category[$category][$product][$date])) {
                $product_data_by_category[$category][$product][$date] = 0;
            }
        }
    }
}

// Ensure all dates have data points for each category
foreach ($categories as $category) {
    foreach ($dates as $date) {
        if (!isset($data_by_category[$category][$date])) {
            $data_by_category[$category][$date] = 0;
        }
    }
}

// Sort dates to ensure consistent ordering
sort($dates);


?>
