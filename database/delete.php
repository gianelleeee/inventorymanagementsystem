<?php
    $data = $_POST;
    $id = (int) $data['id'];
    $table = $data['table'];

    // Adding the record
    try {
        include('connection.php');

        //delete junction table
        if($table === 'category'){
            $category_id = $id;
            $command = "DELETE FROM productscategory WHERE category={$id}";
            $conn->exec($command);
        }

        if($table === 'products'){
            $category_id = $id;
            $command = "DELETE FROM productscategory WHERE product={$id}";
            $conn->exec($command);
        }


        //delete main table
        $command = "DELETE FROM $table WHERE id={$id}";
        
        
        $conn->exec($command);

        echo json_encode([
            'success' => true,
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
        ]);
    }

?>