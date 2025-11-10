<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Handle search and filters
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$sortBy = $_GET['sort'] ?? 'customerID';
$sortOrder = $_GET['order'] ?? 'DESC';

// Build query
$query = "
    SELECT c.*, 
           COUNT(o.orderID) as totalOrders,
           COALESCE(SUM(o.totalAmount), 0) as totalSpent,
           MAX(o.orderDate) as lastOrder
    FROM customers c
    LEFT JOIN orders o ON c.customerID = o.customerID
    WHERE 1=1
";

$params = [];

// Add search filter
if (!empty($searchTerm)) {
    $query .= " AND (c.name LIKE ? OR c.phoneNumber LIKE ? OR c.email LIKE ?)";
    $searchWildcard = "%{$searchTerm}%";
    $params = [$searchWildcard, $searchWildcard, $searchWildcard];
}

// Add status filter
if (!empty($statusFilter)) {
    $query .= " AND c.status = ?";
    $params[] = $statusFilter;
}

$query .= " GROUP BY c.customerID ORDER BY $sortBy $sortOrder";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$activeCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'Online'")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(totalAmount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Customers Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
    <style>
        .customers-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .customers-main {
            flex: 1;
            overflow-y: auto;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #6dcff6;
        }

        .stat-box-value {
            font-size: 32px;
            font-weight: 800;
            color: #2d3748;
            margin: 10px 0;
        }

        .stat-box-label {
            font-size: 13px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
        }

        .filter-group input,
        .filter-group select {
            flex: 1;
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #6dcff6;
        }

        .btn-filter {
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(58, 155, 220, 0.3);
        }

        .btn-reset {
            background: #e2e8f0;
            color: #2d3748;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-reset:hover {
            background: #cbd5e0;
        }

        .customers-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .table th {
            padding: 15px;
            font-weight: 700;
            color: #2d3748;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .table tbody tr:hover {
            background: #f7fafc;
        }

        .customer-name {
            font-weight: 600;
            color: #2d3748;
        }

        .customer-phone {
            color: #718096;
            font-size: 13px;
        }

        .badge-online {
            background: #c6f6d5;
            color: #22543d;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-offline {
            background: #fed7d7;
            color: #742a2a;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-view {
            background: #bee3f8;
            color: #2c5282;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-view:hover {
            background: #90cdf4;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #fed7d7;
            color: #742a2a;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background: #fc8181;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            background: white;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            text-decoration: none;
            color: #2d3748;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #6dcff6;
            color: white;
            border-color: #6dcff6;
        }

        .info-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0c4a6e;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .modal-header {
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            color: white;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="customers-wrapper">
        <!-- Sidebar -->
        <?php include("../../includes/admin-sidebar.php"); ?>


        <!-- Main Content -->
        <div class="main-content customers-main">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1 class="page-title">Customers Management</h1>
                <div style="color: #718096; font-size: 14px;">
                    <i class="fas fa-users"></i> Total: <strong><?php echo $totalCustomers; ?></strong>
                </div>
            </div>

            <!-- Statistics -->
            <div style="padding: 0 20px;">
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-box-label"><i class="fas fa-users"></i> Total Customers</div>
                        <div class="stat-box-value"><?php echo $totalCustomers; ?></div>
                    </div>
                    <div class="stat-box" style="border-left-color: #48bb78;">
                        <div class="stat-box-label"><i class="fas fa-user-check"></i> Active Now</div>
                        <div class="stat-box-value"><?php echo $activeCustomers; ?></div>
                    </div>
                    <div class="stat-box" style="border-left-color: #ed8936;">
                        <div class="stat-box-label"><i class="fas fa-shopping-cart"></i> Total Orders</div>
                        <div class="stat-box-value"><?php echo $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(); ?></div>
                    </div>
                    <div class="stat-box" style="border-left-color: #f6ad55;">
                        <div class="stat-box-label"><i class="fas fa-money-bill"></i> Total Revenue</div>
                        <div class="stat-box-value">₱<?php echo number_format($totalRevenue, 0); ?></div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div style="padding: 0 20px;">
                <form method="GET" class="filter-section">
                    <div class="filter-row">
                        <input type="text" name="search" placeholder="Search by name, phone, or email..."
                            value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Online" <?php echo $statusFilter === 'Online' ? 'selected' : ''; ?>>Online</option>
                            <option value="Offline" <?php echo $statusFilter === 'Offline' ? 'selected' : ''; ?>>Offline</option>
                        </select>
                        <select name="sort" class="form-select">
                            <option value="customerID" <?php echo $sortBy === 'customerID' ? 'selected' : ''; ?>>Latest</option>
                            <option value="totalSpent" <?php echo $sortBy === 'totalSpent' ? 'selected' : ''; ?>>Highest Spenders</option>
                            <option value="totalOrders" <?php echo $sortBy === 'totalOrders' ? 'selected' : ''; ?>>Most Orders</option>
                            <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        </select>
                    </div>
                    <div class="filter-row">
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="/views/admin/customers.php" class="btn-reset">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Customers Table -->
            <div style="padding: 0 20px 20px;">
                <?php if (empty($customers)): ?>
                    <div class="customers-table">
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>No Customers Found</h3>
                            <p>No customers match your search criteria</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="customers-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Last Order</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><span class="info-badge">#<?php echo $customer['customerID']; ?></span></td>
                                        <td>
                                            <div class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></div>
                                            <div class="customer-phone"><?php echo htmlspecialchars($customer['address'] ?? 'No address'); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($customer['phoneNumber']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="<?php echo $customer['status'] === 'Online' ? 'badge-online' : 'badge-offline'; ?>">
                                                <i class="fas fa-circle"></i> <?php echo $customer['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo $customer['totalOrders']; ?></strong>
                                        </td>
                                        <td>
                                            <strong>₱<?php echo number_format($customer['totalSpent'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            if ($customer['lastOrder']) {
                                                echo date('M d, Y', strtotime($customer['lastOrder']));
                                            } else {
                                                echo '<span style="color: #cbd5e0;">Never</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($customer['dateCreated'])); ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-view" onclick="viewCustomerDetails(<?php echo $customer['customerID']; ?>, '<?php echo htmlspecialchars($customer['name']); ?>')">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <button class="btn-delete" onclick="deleteCustomer(<?php echo $customer['customerID']; ?>, '<?php echo htmlspecialchars($customer['name']); ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Customer Details Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="customerDetails">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewCustomerDetails(customerID, name) {
            const modal = new bootstrap.Modal(document.getElementById('customerModal'));

            fetch(`/controllers/admin/get_customer_details.php?id=${customerID}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('customerDetails').innerHTML = `
                            <div>
                                <h5>${data.customer.name}</h5>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>Phone:</strong> ${data.customer.phoneNumber}</p>
                                        <p><strong>Email:</strong> ${data.customer.email || 'N/A'}</p>
                                        <p><strong>Status:</strong> <span class="badge bg-${data.customer.status === 'Online' ? 'success' : 'secondary'}">${data.customer.status}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Total Orders:</strong> ${data.stats.totalOrders}</p>
                                        <p><strong>Total Spent:</strong> ₱${Number(data.stats.totalSpent).toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
                                        <p><strong>Joined:</strong> ${new Date(data.customer.dateCreated).toLocaleDateString()}</p>
                                    </div>
                                </div>
                                <h6>Recent Orders</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.orders.map(order => `
                                            <tr>
                                                <td>#${order.orderID}</td>
                                                <td>₱${Number(order.totalAmount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                                                <td><span class="badge bg-${order.status === 'completed' ? 'success' : order.status === 'pending' ? 'warning' : 'danger'}">${order.status}</span></td>
                                                <td>${new Date(order.orderDate).toLocaleDateString()}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                    modal.show();
                });
        }

        function deleteCustomer(customerID, name) {
            if (confirm(`Are you sure you want to delete customer "${name}"? This will also delete all their orders and cart items.`)) {
                fetch('/controllers/admin/delete_customer.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `customerID=${customerID}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Customer deleted successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }
    </script>
</body>

</html>