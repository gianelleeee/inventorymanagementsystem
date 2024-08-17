<?php

include('connection.php');

$purchase_orders = $_POST['payload'];

try {
    foreach ($purchase_orders as $po) {
        $delivered = (int) $po['qtyDelivered'];
        $cur_qty_received = (int) $po['qtyReceived'];
        $status = $po['status'];
        $row_id = $po['id'];
        $qty_ordered = (int) $po['qtyOrdered'];
        $product_id = (int) $po['pid'];
        
        // Check if the delivered quantity is negative
        if ($delivered < 0) {
            $response = [
                'success' => false,
                'message' => "Error processing your request: Quantity Delivered cannot be negative."
            ];
            echo json_encode($response);
            exit;
        }

        // Check if the delivered quantity exceeds the ordered quantity
        if ($cur_qty_received + $delivered > $qty_ordered) {
            $response = [
                'success' => false,
                'message' => "Error processing your request: Quantity received exceeds quantity ordered"
            ];
            echo json_encode($response);
            exit;
        }

        // Only process if delivered quantity is greater than 0
        if ($delivered > 0) {
            // Update quantity received and calculate remaining quantity
            $updated_qty_received = $cur_qty_received + $delivered;
            $qty_remaining = $qty_ordered - $updated_qty_received;

            // Determine new status
            if ($updated_qty_received >= $qty_ordered) {
                $status = 'complete';
            } elseif ($updated_qty_received > 0) {
                $status = 'incomplete';
            } else {
                $status = 'pending';
            }

            // Update order_product table
            $sql = "UPDATE order_product SET quantity_received=?, status=?, quantity_remaining=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$updated_qty_received, $status, $qty_remaining, $row_id]);

            // Insert into order_product_history
            $deliver_history = [
                'order_product_id' => $row_id,
                'qty_received' => $delivered,
                'date_received' => date('Y-m-d H:i:s'),
                'date_updated' => date('Y-m-d H:i:s')
            ];
            $sql = "INSERT INTO order_product_history
                        (order_product_id, qty_received, date_received, date_updated) 
                    VALUES
                        (:order_product_id, :qty_received, :date_received, :date_updated)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($deliver_history);

            // Update product stock
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            $cur_stock = (int) $product['stock'];
            $updated_stock = $cur_stock + $delivered;
            $sql = "UPDATE products SET stock=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$updated_stock, $product_id]);
        }
    }

    $response = [
        'success' => true,
        'message' => "Purchase Order Successfully Updated!"
    ];

} catch (\Exception $e) {
    $response = [
        'success' => false,
        'message' => "Error Processing Your Request: " . $e->getMessage()
    ];
}

echo json_encode($response);
