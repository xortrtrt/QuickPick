<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-admin">
            <div class="logo-icon">Q</div>
            <span>QuickPick Admin</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#dashboard" class="nav-link active">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="#products" class="nav-link">
                    <i class="fas fa-box"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="#orders" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="#customers" class="nav-link">
                    <i class="fas fa-users"></i>
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a href="#inventory" class="nav-link">
                    <i class="fas fa-warehouse"></i>
                    Inventory
                </a>
            </li>
            <li class="nav-item">
                <a href="#settings" class="nav-link">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </li>
            <li class="nav-item" style="margin-top: 40px;">
                <a href="#logout" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1 class="page-title">Product Management</h1>
            <div class="admin-profile">
                <div class="admin-avatar">A</div>
                <div>
                    <div style="font-weight: 600; color: #2d3748;">Admin User</div>
                    <div style="font-size: 12px; color: #718096;">Administrator</div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e6fffa; color: #319795;">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value" id="totalProducts">0</div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef5e7; color: #d69e2e;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value" id="lowStock">0</div>
                <div class="stat-label">Low Stock Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fce7f3; color: #be185d;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$0.00</div>
                <div class="stat-label">Today's Revenue</div>
            </div>
        </div>

        <!-- Add Product Form -->
        <div class="form-card">
            <h2 class="form-title">Add New Product</h2>
            <form id="productForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="productName" required placeholder="e.g., Fresh Organic Apples">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select class="form-select" id="productCategory" required>
                                <option value="">Select Category</option>
                                <option value="vegetables">Vegetables</option>
                                <option value="fruits">Fruits</option>
                                <option value="dairy">Dairy & Eggs</option>
                                <option value="meat">Meat & Seafood</option>
                                <option value="bakery">Bakery</option>
                                <option value="snacks">Snacks</option>
                                <option value="beverages">Beverages</option>
                                <option value="canned">Canned Goods</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Price ($) *</label>
                            <input type="number" class="form-control" id="productPrice" required step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Weight *</label>
                            <input type="text" class="form-control" id="productWeight" required placeholder="e.g., 500 gm, 1 kg">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Stock Quantity *</label>
                            <input type="number" class="form-control" id="productStock" required min="0" placeholder="0">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="productDescription" rows="3" placeholder="Enter product description..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Product Images</label>
                    <div class="image-upload" onclick="document.getElementById('imageInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p style="margin: 0; color: #718096;">Click to upload images</p>
                        <small style="color: #a0aec0;">PNG, JPG up to 5MB</small>
                    </div>
                    <input type="file" id="imageInput" accept="image/*" multiple style="display: none;">
                    <div class="image-preview" id="imagePreview"></div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Product
                </button>
            </form>
        </div>

        <!-- Products Table -->
        <div class="table-card">
            <h2 class="form-title">All Products</h2>
            <table class="products-table" id="productsTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Weight</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <!-- Products will be dynamically inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="/assets/js/admin-js/dashboard.js"></script>
</body>

</html>