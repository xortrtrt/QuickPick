<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Get all orders with customer info
$stmt = $pdo->prepare("
    SELECT o.orderID, c.name, c.phoneNumber, o.totalAmount, o.status, o.orderDate, COUNT(oi.orderItemID) as itemCount
    FROM orders o
    JOIN customers c ON o.customerID = c.customerID
    LEFT JOIN orderitems oi ON o.orderID = oi.orderID
    GROUP BY o.orderID
    ORDER BY o.orderDate DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Orders Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
</head>

<body>
    <div style="display: flex;">
        <!-- Sidebar -->
        <?php include("../../includes/admin-sidebar.php"); ?>


        <!-- Main Content -->
        <div class="main-content" style="flex: 1;">
            <div class="top-bar">
                <h1 class="page-title">Orders Management</h1>
            </div>

            <!-- Filters -->
            <div class="form-card">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="filterCustomer" placeholder="Filter by customer...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filterDate">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="applyFilters()">Apply Filters</button>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="table-card">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['orderID']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><?php echo htmlspecialchars($order['phoneNumber']); ?></td>
                                <td><?php echo $order['itemCount']; ?></td>
                                <td><strong>₱<?php echo number_format($order['totalAmount'], 2); ?></strong></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['orderDate'])); ?></td>
                                <td>
                                    <?php
                                    $statusClass = match (strtolower($order['status'])) {
                                        'completed' => 'stock-in',
                                        'pending' => 'stock-low',
                                        'cancelled' => 'stock-low',
                                        default => 'stock-in'
                                    };
                                    ?>
                                    <span class="stock-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn btn-edit" onclick="viewOrder(<?php echo $order['orderID']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn btn-delete" onclick="updateOrderStatus(<?php echo $order['orderID']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrder(orderID) {
            window.location.href = `/views/admin/order_details.php?id=${orderID}`;
        }

        function updateOrderStatus(orderID) {
            const newStatus = prompt('Enter new status (pending/completed/cancelled):');
            if (newStatus) {
                fetch('/controllers/admin/update_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `orderID=${orderID}&status=${newStatus}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Order updated successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }

        function applyFilters() {
            const customer = document.getElementById('filterCustomer').value;
            const status = document.getElementById('filterStatus').value;
            const date = document.getElementById('filterDate').value;

            let query = '?';
            if (customer) query += `customer=${encodeURIComponent(customer)}&`;
            if (status) query += `status=${status}&`;
            if (date) query += `date=${date}&`;

            window.location.href = `/views/admin/orders.php${query}`;
        }
    </script>
</body>

</html>