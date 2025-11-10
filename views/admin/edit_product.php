<?php

session_start();

if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Get product ID from URL
$productID = intval($_GET['id'] ?? 0);

if ($productID <= 0) {
    header("Location: /views/admin/products.php");
    exit;
}

// Fetch product data
$stmt = $pdo->prepare("
    SELECT p.*, c.categoryName
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    WHERE p.productID = ?
");
$stmt->execute([$productID]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['error'] = 'Product not found';
    header("Location: /views/admin/products.php");
    exit;
}

// Get all categories for dropdown
$categories = $pdo->query("SELECT * FROM categories WHERE isActive = 1 ORDER BY categoryName")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['productName'] ?? '');
    $categoryID = $_POST['categoryID'] ?? null;
    $price = floatval($_POST['price'] ?? 0);
    $stockQuantity = intval($_POST['stockQuantity'] ?? 0);
    $unit = trim($_POST['unit'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isActive = isset($_POST['isActive']) ? 1 : 0;
    $imageURL = $product['imageURL']; // Keep existing image by default

    // Validation
    if (empty($productName)) {
        $errorMsg = 'Product name is required';
    } elseif ($price <= 0) {
        $errorMsg = 'Price must be greater than 0';
    } elseif ($stockQuantity < 0) {
        $errorMsg = 'Stock quantity cannot be negative';
    } elseif (empty($unit)) {
        $errorMsg = 'Unit is required';
    }

    // Handle image upload
    if (!$errorMsg && isset($_FILES['imageURL']) && $_FILES['imageURL']['error'] === 0) {
        // Validate image
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExt = strtolower(pathinfo($_FILES['imageURL']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            $errorMsg = 'Invalid image format. Allowed: JPG, PNG, GIF, WEBP';
        } elseif ($_FILES['imageURL']['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errorMsg = 'Image size must not exceed 5MB';
        } else {
            $uploadDir = __DIR__ . '/../../assets/images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Delete old image if exists
            $oldImagePath = $uploadDir . $product['imageURL'];
            if ($product['imageURL'] !== 'default.jpg' && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            // Save new image
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['imageURL']['name']));
            if (move_uploaded_file($_FILES['imageURL']['tmp_name'], $uploadDir . $fileName)) {
                $imageURL = $fileName;
            } else {
                $errorMsg = 'Failed to upload image';
            }
        }
    }

    // Update product if no errors
    if (!$errorMsg) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE products
                SET productName = ?, categoryID = ?, price = ?, stockQuantity = ?, 
                    unit = ?, description = ?, imageURL = ?, isActive = ?
                WHERE productID = ?
            ");

            $updateStmt->execute([
                $productName,
                $categoryID ?: null,
                $price,
                $stockQuantity,
                $unit,
                $description,
                $imageURL,
                $isActive,
                $productID
            ]);

            $successMsg = 'Product updated successfully!';

            // Refresh product data
            $stmt = $pdo->prepare("
                SELECT p.*, c.categoryName
                FROM products p
                LEFT JOIN categories c ON p.categoryID = c.categoryID
                WHERE p.productID = ?
            ");
            $stmt->execute([$productID]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errorMsg = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - QuickPick Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
    <link rel="stylesheet" href="/assets/css/admin-css/edit_products.css">

</head>

<body>
    <div class="edit-product-wrapper">
        <!-- Main Content -->
        <div class="main-edit">
            <!-- Header -->
            <div class="edit-header">
                <h1>
                    <i class="fas fa-edit"></i> Edit Product
                </h1>
                <div class="breadcrumb-nav">
                    <a href="/views/admin/dashboard.php">Dashboard</a> /
                    <a href="/views/admin/products.php">Products</a> /
                    <span><?php echo htmlspecialchars($product['productName']); ?></span>
                </div>
            </div>

            <!-- Alert Messages -->
            <div class="edit-content">
                <?php if ($successMsg): ?>
                    <div class="alert-message alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($successMsg); ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorMsg): ?>
                    <div class="alert-message alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </div>
                <?php endif; ?>

                <!-- Edit Form -->
                <form method="POST" enctype="multipart/form-data" class="edit-form-container">
                    <!-- Left Column - Form Fields -->
                    <div>
                        <div class="form-card">
                            <!-- Basic Info Section -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-info-circle"></i> Basic Information
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Product Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="productName"
                                        value="<?php echo htmlspecialchars($product['productName']); ?>"
                                        placeholder="Enter product name" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="categoryID">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['categoryID']; ?>"
                                                <?php echo $product['categoryID'] == $cat['categoryID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['categoryName']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description"
                                        placeholder="Enter product description"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Pricing & Stock Section -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-tag"></i> Pricing & Stock
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Price (₱) <span class="required">*</span></label>
                                    <input type="number" class="form-control" name="price"
                                        value="<?php echo $product['price']; ?>"
                                        step="0.01" placeholder="0.00" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Stock Quantity <span class="required">*</span></label>
                                    <input type="number" class="form-control" name="stockQuantity"
                                        value="<?php echo $product['stockQuantity']; ?>"
                                        placeholder="0" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Unit <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="unit"
                                        value="<?php echo htmlspecialchars($product['unit']); ?>"
                                        placeholder="e.g., kg, piece, box" required>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="action-buttons">
                                <button type="submit" class="btn-save">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <a href="/views/admin/products.php" class="btn-cancel" style="text-decoration: none;">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Image & Status -->
                    <div>
                        <!-- Image Preview Section -->
                        <div class="image-preview-section">
                            <div class="image-preview-title">
                                <i class="fas fa-image"></i> Product Image
                            </div>

                            <div class="current-image-container">
                                <label class="current-image-label">Current Image</label>
                                <div class="current-image">
                                    <img src="/assets/images/products/<?php echo htmlspecialchars($product['imageURL']); ?>"
                                        alt="<?php echo htmlspecialchars($product['productName']); ?>">
                                </div>
                            </div>

                            <div class="image-upload-area" onclick="document.getElementById('imageInput').click();"
                                ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
                                <div class="image-upload-icon">📷</div>
                                <div class="image-upload-text">
                                    <strong>Click to upload</strong> or drag and drop<br>
                                    PNG, JPG, GIF or WEBP (Max 5MB)
                                </div>
                            </div>

                            <input type="file" id="imageInput" name="imageURL" class="image-upload-input"
                                accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewImage(event)">
                        </div>

                        <!-- Status Section -->
                        <div class="status-section" style="margin-top: 25px;">
                            <div class="status-title">
                                <i class="fas fa-toggle-on"></i> Status
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="isActive" name="isActive"
                                    <?php echo $product['isActive'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isActive">
                                    Product is Active
                                </label>
                            </div>

                            <div style="margin-top: 15px; padding: 12px; background: #f0f7ff; border-radius: 8px;">
                                <div style="font-size: 12px; color: #718096; margin-bottom: 6px;">
                                    <strong>Product ID:</strong> #<?php echo $product['productID']; ?>
                                </div>
                                <div style="font-size: 12px; color: #718096; margin-bottom: 6px;">
                                    <strong>Added:</strong> <?php echo date('M d, Y H:i', strtotime($product['dateAdded'])); ?>
                                </div>
                                <div style="font-size: 12px; color: #718096;">
                                    <strong>Stock Status:</strong>
                                    <?php
                                    if ($product['stockQuantity'] <= 0) {
                                        echo '<span style="color: #f56565;">Out of Stock</span>';
                                    } elseif ($product['stockQuantity'] < 10) {
                                        echo '<span style="color: #ffa502;">Low Stock</span>';
                                    } else {
                                        echo '<span style="color: #48bb78;">In Stock</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.current-image img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Drag and drop
        function handleDragOver(event) {
            event.preventDefault();
            event.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(event) {
            event.currentTarget.classList.remove('dragover');
        }

        function handleDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('dragover');

            const files = event.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('imageInput').files = files;
                previewImage({
                    target: {
                        files: files
                    }
                });
            }
        }

        // Auto-hide alert messages after 5 seconds
        document.querySelectorAll('.alert-message').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>

</html>