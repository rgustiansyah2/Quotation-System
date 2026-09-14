<?php
include 'config/database.php';

header('Content-Type: application/json');

$customers = [];
$result = $conn->query("SELECT c.id, c.customer_name, c.address, c.pic, c.phone, c.email
                       FROM tbl_customer c
                       INNER JOIN (
                           SELECT MAX(id) AS max_id
                           FROM tbl_customer
                           GROUP BY customer_name
                       ) latest ON c.id = latest.max_id
                       ORDER BY c.customer_name ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $customers]);
