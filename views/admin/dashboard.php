<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
            padding: 20px;
            z-index: 1000;
            overflow-y: auto;
        }

        .logo-admin {
            color: white;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            background: #6DCFF6;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(109, 207, 246, 0.1);
            color: #6DCFF6;
        }

        .nav-link,
        .nav-link:visited {
            opacity: 1;
            color: rgba(255, 255, 255, 0.9);
            transition: background-color 0.25s, color 0.25s;
        }

        .nav-link:focus,
        .nav-link:active {
            outline: none;
            opacity: 1 !important;
            color: #6DCFF6 !important;
            background: rgba(109, 207, 246, 0.10) !important;
        }

        .nav-link:focus {
            box-shadow: 0 0 0 3px rgba(109, 207, 246, 0.12);
            border-radius: 8px;
        }

        .nav-link i {
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }

        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6DCFF6, #3A9BDC);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #718096;
            font-size: 14px;
        }

        /* Product Form */
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            display: block;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #6DCFF6;
        }

        .image-upload {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .image-upload:hover {
            border-color: #6DCFF6;
            background: #f7fafc;
        }

        .image-upload i {
            font-size: 48px;
            color: #cbd5e0;
            margin-bottom: 15px;
        }

        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6DCFF6, #3A9BDC);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(58, 155, 220, 0.4);
        }

        /* Products Table */
        .table-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th {
            background: #f7fafc;
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
        }

        .products-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .product-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }

        .stock-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .stock-in {
            background: #c6f6d5;
            color: #22543d;
        }

        .stock-low {
            background: #fed7d7;
            color: #742a2a;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #bee3f8;
            color: #2c5282;
        }

        .btn-delete {
            background: #fed7d7;
            color: #742a2a;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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

    <script>
        let products = [];
        let uploadedImages = [];

        // Image upload handling
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            const preview = document.getElementById('imagePreview');

            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        uploadedImages.push(event.target.result);

                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                            <img src="${event.target.result}" alt="Preview">
                            <button class="remove-image" onclick="removeImage(${uploadedImages.length - 1})">×</button>
                        `;
                        preview.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        function removeImage(index) {
            uploadedImages.splice(index, 1);
            renderImagePreview();
        }

        function renderImagePreview() {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            uploadedImages.forEach((img, index) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = `
                    <img src="${img}" alt="Preview">
                    <button class="remove-image" onclick="removeImage(${index})">×</button>
                `;
                preview.appendChild(previewItem);
            });
        }

        // Form submission
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const product = {
                id: Date.now(),
                name: document.getElementById('productName').value,
                category: document.getElementById('productCategory').value,
                price: parseFloat(document.getElementById('productPrice').value),
                weight: document.getElementById('productWeight').value,
                stock: parseInt(document.getElementById('productStock').value),
                description: document.getElementById('productDescription').value,
                images: [...uploadedImages],
                createdAt: new Date().toISOString()
            };

            products.push(product);

            // Save to localStorage (in real app, send to backend)
            localStorage.setItem('quickpick_products', JSON.stringify(products));

            // Reset form
            this.reset();
            uploadedImages = [];
            document.getElementById('imagePreview').innerHTML = '';

            // Refresh table
            renderProductsTable();
            updateStats();

            alert('Product added successfully!');
        });

        function renderProductsTable() {
            const tbody = document.getElementById('productsTableBody');
            tbody.innerHTML = '';

            products.forEach(product => {
                const row = document.createElement('tr');
                const stockStatus = product.stock < 10 ? 'stock-low' : 'stock-in';
                const stockText = product.stock < 10 ? 'Low Stock' : 'In Stock';

                row.innerHTML = `
                    <td><img src="${product.images[0] || 'placeholder.jpg'}" class="product-img" alt="${product.name}"></td>
                    <td><strong>${product.name}</strong></td>
                    <td>${product.category}</td>
                    <td>$${product.price.toFixed(2)}</td>
                    <td>${product.weight}</td>
                    <td>${product.stock}</td>
                    <td><span class="stock-badge ${stockStatus}">${stockText}</span></td>
                    <td>
                        <button class="action-btn btn-edit" onclick="editProduct(${product.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn btn-delete" onclick="deleteProduct(${product.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function editProduct(id) {
            const product = products.find(p => p.id === id);
            if (product) {
                document.getElementById('productName').value = product.name;
                document.getElementById('productCategory').value = product.category;
                document.getElementById('productPrice').value = product.price;
                document.getElementById('productWeight').value = product.weight;
                document.getElementById('productStock').value = product.stock;
                document.getElementById('productDescription').value = product.description;

                // Remove product from array (will be re-added on submit)
                products = products.filter(p => p.id !== id);
                renderProductsTable();
                updateStats();

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                products = products.filter(p => p.id !== id);
                localStorage.setItem('quickpick_products', JSON.stringify(products));
                renderProductsTable();
                updateStats();
            }
        }

        function updateStats() {
            document.getElementById('totalProducts').textContent = products.length;
            document.getElementById('lowStock').textContent = products.filter(p => p.stock < 10).length;
        }

        // Load products from localStorage on page load
        window.addEventListener('load', function() {
            const stored = localStorage.getItem('quickpick_products');
            if (stored) {
                products = JSON.parse(stored);
                renderProductsTable();
                updateStats();
            }
        });
    </script>
</body>

</html>