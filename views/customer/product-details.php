<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Product Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
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

        /* Same header as dashboard */
        .top-header {
            background: linear-gradient(180deg, #6DCFF6 0%, #3A9BDC 100%);
            color: #fff;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .product-detail-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .product-detail-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 40px;
        }

        .product-images {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            background: #f8f9fa;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 200px;
        }

        .thumbnail-images {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid transparent;
            font-size: 40px;
        }

        .thumbnail.active {
            border-color: #6DCFF6;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .product-title {
            font-size: 32px;
            font-weight: 800;
            color: #2d3748;
        }

        .product-subtitle {
            font-size: 16px;
            color: #718096;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-price {
            font-size: 48px;
            font-weight: 900;
            color: #3A9BDC;
        }

        .product-description {
            color: #4a5568;
            line-height: 1.8;
        }

        .quantity-section {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
        }

        .quantity-label {
            font-weight: 600;
            color: #2d3748;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 50px;
        }

        .qty-btn {
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: 700;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            color: #2d3748;
        }

        .qty-btn:hover {
            background: #6DCFF6;
            color: white;
            transform: scale(1.1);
        }

        .qty-btn:disabled {
            background: #e2e8f0;
            color: #a0aec0;
            cursor: not-allowed;
        }

        .qty-display {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            min-width: 50px;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .add-to-cart-btn {
            flex: 1;
            background: linear-gradient(135deg, #6DCFF6, #3A9BDC);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(58, 155, 220, 0.4);
        }

        .buy-now-btn {
            flex: 1;
            background: #2d3748;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .buy-now-btn:hover {
            background: #1a202c;
            transform: translateY(-3px);
        }

        .product-details-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }

        .detail-value {
            color: #2d3748;
        }

        @media (max-width: 768px) {
            .product-layout {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .main-image {
                height: 300px;
                font-size: 150px;
            }

            .product-title {
                font-size: 24px;
            }

            .product-price {
                font-size: 36px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="top-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <button class="back-btn" onclick="window.history.back()">← Back to Dashboard</button>
                <div style="color: white; font-size: 20px; font-weight: 700;">Product Details</div>
                <div style="width: 100px;"></div>
            </div>
        </div>
    </header>

    <!-- Product Detail -->
    <div class="product-detail-container">
        <div class="product-detail-card">
            <div class="product-layout">
                <!-- Left Side - Images -->
                <div class="product-images">
                    <div class="main-image" id="mainImage">
                        🥬
                    </div>
                    <div class="thumbnail-images">
                        <div class="thumbnail active">🥬</div>
                        <div class="thumbnail">🥬</div>
                        <div class="thumbnail">🥬</div>
                    </div>
                </div>

                <!-- Right Side - Info -->
                <div class="product-info">
                    <h1 class="product-title" id="productTitle">Beetroot</h1>
                    <p class="product-subtitle" id="productSubtitle">(Local shop)</p>
                    
                    <div class="product-rating">
                        <span>⭐⭐⭐⭐⭐</span>
                        <span style="color: #718096;">(124 reviews)</span>
                    </div>

                    <div class="product-price" id="productPrice">
                        $17.29
                    </div>

                    <p class="product-description">
                        Fresh, organic beetroot sourced from local farms. Rich in nutrients and perfect for salads, juices, or cooking. Delivered fresh to maintain maximum nutritional value.
                    </p>

                    <div class="quantity-section">
                        <span class="quantity-label">Quantity:</span>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="decreaseQty()" id="minusBtn">−</button>
                            <span class="qty-display" id="quantity">1</span>
                            <button class="qty-btn" onclick="increaseQty()" id="plusBtn">+</button>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="add-to-cart-btn" onclick="addToCart()">Add to Cart 🛒</button>
                        <button class="buy-now-btn" onclick="buyNow()">Buy Now</button>
                    </div>

                    <div class="product-details-section">
                        <div class="detail-item">
                            <span class="detail-label">Weight:</span>
                            <span class="detail-value">500 gm</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Origin:</span>
                            <span class="detail-value">Local Farm</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Availability:</span>
                            <span class="detail-value" style="color: #48bb78;">In Stock</span>
                        </div>
                        <div class="detail-item" style="border-bottom: none;">
                            <span class="detail-label">Delivery:</span>
                            <span class="detail-value">Within 15 mins</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let quantity = 1;
        const maxQty = 10;

        // Get product ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');

        // Product data (in real app, fetch from backend)
        const products = {
            1: { name: 'Beetroot', icon: '🥬', price: '17.29', subtitle: '(Local shop)' },
            2: { name: 'Italian Avocado', icon: '🥑', price: '12.29', subtitle: '(Local shop)' },
            3: { name: 'Szam amm', icon: '🧀', price: '14.29', subtitle: '(Process food)' },
            4: { name: 'Beef Minced', icon: '🥩', price: '16.22', subtitle: '(Cut Box)' },
            5: { name: 'Cold drinks', icon: '🥤', price: '18.29', subtitle: '(Sprite)' },
            6: { name: 'Plant Hunter', icon: '🍗', price: '20.29', subtitle: '(Frozen pack)' },
            7: { name: 'Deshi Gajor', icon: '🥕', price: '19.29', subtitle: '(Local Carrot)' },
            8: { name: 'Deshi Shosha', icon: '🥒', price: '04.29', subtitle: '(Local Cucumb)' },
            9: { name: 'Lays chips', icon: '🍟', price: '21.29', subtitle: '(Bacon)' },
            10: { name: 'Badhakopi', icon: '🥬', price: '09.29', subtitle: '(Local Cabbage)' }
        };

        // Load product data
        if (productId && products[productId]) {
            const product = products[productId];
            document.getElementById('productTitle').textContent = product.name;
            document.getElementById('productSubtitle').textContent = product.subtitle;
            document.getElementById('productPrice').textContent = `$${product.price}`;
            document.getElementById('mainImage').textContent = product.icon;
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.textContent = product.icon;
            });
        }

        function increaseQty() {
            if (quantity < maxQty) {
                quantity++;
                updateQuantityDisplay();
            }
        }

        function decreaseQty() {
            if (quantity > 1) {
                quantity--;
                updateQuantityDisplay();
            }
        }

        function updateQuantityDisplay() {
            document.getElementById('quantity').textContent = quantity;
            document.getElementById('minusBtn').disabled = quantity === 1;
            document.getElementById('plusBtn').disabled = quantity === maxQty;
        }

        function addToCart() {
            // Add to cart logic here
            alert(`Added ${quantity} item(s) to cart!`);
            
            // You can also store in localStorage or send to backend
            // localStorage.setItem('cart', JSON.stringify(cartItems));
            
            // Or redirect to cart page
            // window.location.href = '/cart.php';
        }

        function buyNow() {
            // Direct checkout logic
            alert(`Proceeding to checkout with ${quantity} item(s)`);
            
            // Redirect to checkout
            // window.location.href = '/checkout.php';
        }

        // Thumbnail click handling
        document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
            thumb.addEventListener('click', function() {
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                // In real app, change main image here
            });
        });

        // Initialize
        updateQuantityDisplay();
    </script>
</body>
</html>