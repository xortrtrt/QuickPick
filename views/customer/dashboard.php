<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>QuickPick - Customer Dashboard</title>
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

        /* Header */
        .top-header {
            background: linear-gradient(180deg, #6DCFF6 0%, #3A9BDC 100%);
            color: #fff;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            background: #fff;
            color: #3A9BDC;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
        }

        .search-container {
            flex: 1;
            max-width: 500px;
            margin: 0 30px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-box input:focus {
            outline: 2px solid #9eca7f;
        }

        .order-timer {
            background: rgba(255, 255, 255, .1);
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .timer-highlight {
            color: #9eca7f;
            font-weight: 700;
        }

        .header-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .icon-circle {
            background: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        .cart-icon-wrapper,
        .user-icon-wrapper {
            position: relative;
            cursor: pointer;
            transition: transform .18s;
        }

        .cart-icon-wrapper:hover,
        .user-icon-wrapper:hover {
            transform: scale(1.08);
        }

        @keyframes cartPulse {

            0%,
            100% {
                transform: scale(1)
            }

            50% {
                transform: scale(1.2)
            }
        }

        .cart-icon-wrapper.pulse {
            animation: cartPulse .4s ease;
        }

        /* HERO */
        .hero-section {
            background: linear-gradient(135deg, #6DCFF6 0%, #3A9BDC 100%);
            margin: 20px;
            border-radius: 30px;
            padding: 60px;
            position: relative;
            overflow: visible;
            min-height: 380px;
            display: flex;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        .hero-section::before,
        .hero-section::after {
            content: "";
            position: absolute;
            pointer-events: none;
            z-index: 0;
            filter: blur(48px);
        }

        .hero-section::before {
            width: 380px;
            height: 380px;
            left: -40px;
            top: -20px;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .32), transparent 36%);
            opacity: .85;
        }

        .hero-section::after {
            width: 380px;
            height: 380px;
            right: 60px;
            bottom: 20px;
            background: radial-gradient(circle at 60% 60%, rgba(255, 255, 255, .12), transparent 36%);
            opacity: .45;
        }

        .floating-icon {
            position: absolute;
            font-size: 34px;
            opacity: .24;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, .06));
            z-index: 1;
            transform-origin: center;
        }

        @keyframes floatSlow {
            0% {
                transform: translateY(0) rotate(0deg)
            }

            50% {
                transform: translateY(-16px) rotate(5deg)
            }

            100% {
                transform: translateY(0) rotate(0deg)
            }
        }

        .floating-icon.one {
            left: 6%;
            top: 16%;
            animation: floatSlow 6s ease-in-out infinite .2s;
        }

        .floating-icon.two {
            left: 20%;
            top: 8%;
            animation: floatSlow 7s ease-in-out infinite .7s;
        }

        .floating-icon.three {
            left: 36%;
            top: 22%;
            animation: floatSlow 5.6s ease-in-out infinite 1s;
        }

        .floating-icon.four {
            left: 50%;
            top: 10%;
            animation: floatSlow 8s ease-in-out infinite .4s;
        }

        /* hero content */
        .hero-content {
            max-width: 560px;
            z-index: 3;
            position: relative;
        }

        .hero-title {
            font-size: 56px;
            font-weight: 800;
            color: #fff;
            line-height: 1.05;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(18px);
            animation: fadeInUp .8s ease forwards .2s;
            text-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .hero-description {
            font-size: 16px;
            color: rgba(255, 255, 255, .95);
            margin-bottom: 30px;
            line-height: 1.6;
            opacity: 0;
            transform: translateY(12px);
            animation: fadeInUp .8s ease forwards .4s;
        }

        .shop-now-btn {
            background: #fff;
            color: #3A9BDC;
            border: 2px solid #fff;
            padding: 14px 38px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all .28s cubic-bezier(.2, .9, .2, 1);
            box-shadow: 0 8px 18px rgba(0, 0, 0, .08);
            opacity: 0;
            transform: translateY(8px);
            animation: fadeInUp .8s ease forwards .6s;
        }

        .shop-now-btn:hover {
            background: #3A9BDC;
            color: #fff;
            transform: translateY(-4px) scale(1.02);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-image {
            position: absolute;
            right: 48px;
            top: 50%;
            transform: translateY(-50%);
            width: 420px;
            height: 420px;
            z-index: 4;
            pointer-events: none;
            display: block;
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            animation: bob 6s ease-in-out infinite 1s;
        }

        @keyframes bob {
            0% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-8px)
            }

            100% {
                transform: translateY(0)
            }
        }

        .hero-divider {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            z-index: 2;
            width: 100%;
            line-height: 0;
            transform: translateY(1px);
        }

        .categories-section {
            padding: 40px 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
        }

        .see-all-link {
            color: #0d5257;
            text-decoration: none;
            font-weight: 600;
        }

        .categories-scroll {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .category-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px 20px;
            min-width: 150px;
            text-align: center;
            cursor: pointer;
            transition: all .3s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            border: 2px solid transparent;
            flex-shrink: 0;
        }

        .category-card:hover {
            transform: translateY(-5px);
            border-color: #9eca7f;
        }

        .products-section {
            padding: 40px 20px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .product-card {
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all .3s;
            cursor: pointer;
            border: 3px solid transparent;
        }

        .product-card.selected {
            border-color: #9eca7f;
            background: #f9fff5;
            box-shadow: 0 4px 15px rgba(158, 202, 127, .3);
        }

        .product-image {
            width: 100%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .product-name {
            font-weight: 600;
            font-size: 14px;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .product-subtitle {
            font-size: 12px;
            color: #718096;
            margin-bottom: 5px;
        }

        .product-weight {
            font-size: 12px;
            color: #a0aec0;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
        }

        .price-currency {
            font-size: 16px;
            vertical-align: super;
        }

        .price-decimal {
            font-size: 16px;
        }

        .add-btn {
            width: 100%;
            background: #e8f4e1;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 24px;
            color: #4a5568;
            cursor: pointer;
            transition: all .3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .add-btn:hover {
            background: #d4ebc8;
            transform: scale(1.05);
        }

        .quantity-controls {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 15px;
            background: #9eca7f;
            padding: 8px 15px;
            border-radius: 25px;
            width: 100%;
        }

        .quantity-controls.active {
            display: flex;
        }

        .qty-btn {
            background: #fff;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: 700;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .2s;
            color: #2d3748;
            line-height: 1;
        }

        .qty-btn:hover {
            background: #f0f0f0;
            transform: scale(1.1);
        }

        .qty-number {
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            min-width: 30px;
            text-align: center;
        }

        @keyframes flyToCart {
            0% {
                transform: translate(0, 0) scale(1);
                opacity: 1
            }

            50% {
                transform: translate(var(--x-mid), var(--y-mid)) scale(.5);
                opacity: .8
            }

            100% {
                transform: translate(var(--x-end), var(--y-end)) scale(.1);
                opacity: 0
            }
        }

        .flying-item {
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            font-size: 40px;
            animation: flyToCart .8s cubic-bezier(.45, 0, .55, 1);
        }

        @media (max-width:1100px) {
            .hero-title {
                font-size: 46px
            }

            .hero-image {
                width: 360px;
                height: 360px;
                right: 30px
            }
        }

        @media (max-width:768px) {
            .hero-section {
                padding: 30px 20px;
                text-align: center;
                min-height: auto;
                border-radius: 18px;
            }

            .hero-image {
                position: relative;
                right: auto;
                transform: none;
                margin: 30px auto 0;
                width: 280px;
                height: 280px;
                animation: none;
                z-index: 2;
            }

            .hero-image img {
                animation: none;
            }

            .search-container {
                display: none;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .floating-icon {
                display: none;
            }
        }

        #discount-carousel .dc-card {
            position: relative;
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 16px 40px rgba(10, 25, 40, 0.06);
            max-width: 1200px;
            margin: 0 auto;
            overflow: hidden;
        }

        /* navigation buttons */
        .dc-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 30;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            border: none;
            background: #fff;
            box-shadow: 0 6px 16px rgba(12, 40, 80, 0.06);
            cursor: pointer;
            font-size: 28px;
            color: #2d3748;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dc-left {
            left: 14px;
        }

        .dc-right {
            right: 14px;
        }

        /* track container (visible area) */
        .dc-track-wrap {
            overflow: hidden;
            width: 100%;
        }

        /* track (slides) */
        .dc-track {
            list-style: none;
            display: flex;
            gap: 26px;
            transition: transform 420ms cubic-bezier(.22, .9, .3, 1);
            padding: 0;
            margin: 0;
        }

        .dc-slide {
            flex: 0 0 100%;
            display: flex;
            gap: 26px;
            align-items: stretch;
        }

        /* left: image area */
        .dc-image-wrap {
            flex: 0 0 48%;
            background: #f6fbfc;
            border-radius: 14px;
            padding: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .dc-image-wrap img {
            max-width: 86%;
            max-height: 340px;
            object-fit: contain;
            display: block;
            border-radius: 6px;
        }

        /* discount badge */
        .dc-badge {
            position: absolute;
            left: 18px;
            top: 18px;
            background: linear-gradient(180deg, #1f6fb0, #154f82);
            color: #fff;
            padding: 12px 14px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 8px 20px rgba(10, 25, 40, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 72px;
            min-height: 72px;
            text-align: center;
        }

        .dc-badge small {
            display: block;
            font-weight: 600;
            font-size: 11px;
            opacity: .95;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* right: details */
        .dc-details {
            flex: 1;
            padding: 6px 6px 6px 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .dc-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ff6b6b;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .dc-title {
            font-size: 24px;
            font-weight: 800;
            color: #063a3b;
            margin: 6px 0 14px;
        }

        .dc-rating {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .dc-price {
            font-size: 28px;
            font-weight: 900;
            color: #063a3b;
            margin-bottom: 18px;
        }

        .dc-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .dc-btn-ghost {
            background: #f6fbf7;
            border: 1px solid #eaf6ea;
            padding: 10px 16px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
        }

        .dc-btn-primary {
            background: linear-gradient(90deg, #9eca7f, #6bbf69);
            color: #fff;
            padding: 10px 18px;
            border-radius: 12px;
            border: none;
            font-weight: 800;
            cursor: pointer;
        }

        .dc-links {
            display: flex;
            gap: 18px;
            color: #2d3748;
            font-size: 13px;
            margin-bottom: 10px;
            text-decoration: none;
        }

        .dc-atrib {
            font-size: 13px;
            color: #667083;
            margin-top: 6px;
        }

        /* thumbnails row */
        .dc-thumbs {
            display: flex;
            gap: 10px;
            margin-top: 18px;
            align-items: center;
            justify-content: flex-start;
            padding-left: 18px;
        }

        .dc-thumb {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 8px 18px rgba(12, 40, 80, 0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid transparent;
        }

        .dc-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .dc-thumb.active {
            border-color: #ff7a6b22;
            transform: translateY(-4px);
        }

        /* upload area */
        .dc-upload {
            margin-top: 14px;
            padding: 8px 18px;
            display: flex;
            gap: 14px;
            align-items: center;
            justify-content: flex-start;
        }

        .dc-upload-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            background: #f3f8ff;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px dashed #cfe7ff;
            color: #1b5a9a;
            font-weight: 700;
        }

        .dc-upload input {
            display: none;
        }

        .dc-note {
            color: #718096;
            font-size: 12px;
        }

        /* small / responsive */
        @media (max-width: 900px) {
            .dc-slide {
                flex-direction: column;
                gap: 14px;
            }

            .dc-image-wrap {
                flex: 0 0 auto;
                width: 100%;
                padding: 22px;
            }

            .dc-details {
                padding: 14px 6px;
            }

            .dc-btn {
                display: none;
            }

            .dc-thumbs {
                justify-content: center;
                padding-left: 0;
            }
        }
    </style>
</head>

<body>
    <header class="top-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <div class="logo">
                    <div class="logo-icon">Q</div><span>QuickPick</span>
                </div>
                <div class="search-container">
                    <div class="search-box"><input type="text" placeholder="Search for Grocery, Stores, Vegetable or Meat"></div>
                </div>
                <div class="d-flex align-items-center gap-4">
                    <div class="order-timer">⚡ Order now and get it within <span class="timer-highlight">15 min!</span></div>
                    <div class="header-icons">
                        <div class="cart-icon-wrapper" id="headerCart">
                            <div class="icon-circle">🛒</div><span class="cart-badge" id="cartBadge">0</span>
                        </div>
                        <div class="user-icon-wrapper">
                            <div class="icon-circle">👤</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="hero-section" aria-label="Hero">
        <div class="floating-icon one" aria-hidden="true">🥦</div>
        <div class="floating-icon two" aria-hidden="true">🚚</div>
        <div class="floating-icon three" aria-hidden="true">💳</div>
        <div class="floating-icon four" aria-hidden="true">🛍️</div>

        <div class="hero-content">
            <h1 class="hero-title">We bring the store to your door</h1>
            <p class="hero-description">(DESCRIPTION)</p>
            <button class="shop-now-btn" aria-label="Shop now">Shop now →</button>
        </div>

        <div class="hero-image" aria-hidden="true">
            <img src="/assets/images/heropic.png" alt="Shopping cart illustration">
        </div>

        <div class="hero-divider" aria-hidden="true">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none" style="width:100%; height:100px; display:block;">
                <path d="M0,40 C360,120 1080,0 1440,60 L1440,120 L0,120 Z" fill="#f5f7fa"></path>
            </svg>
        </div>
    </section>

    <section class="categories-section">
        <div class="container-fluid">
            <div class="section-header">
                <h2 class="section-title">Shop by Category</h2>
                <a href="#" class="see-all-link">See all →</a>
            </div>
            <div class="categories-scroll">
                <div class="category-card">
                    <div class="category-icon">🥬</div>
                    <div class="category-name">Vegetable</div>
                    <div class="category-subtitle">Local market</div>
                </div>
                <div class="category-card">
                    <div class="category-icon">🥖</div>
                    <div class="category-name">Snacks & Breads</div>
                    <div class="category-subtitle">In store delivery</div>
                </div>
                <div class="category-card">
                    <div class="category-icon">🍎</div>
                    <div class="category-name">Fruits</div>
                    <div class="category-subtitle">Conical free</div>
                </div>
                <div class="category-card">
                    <div class="category-icon">🍗</div>
                    <div class="category-name">Chicken legs</div>
                    <div class="category-subtitle">Frozen Meal</div>
                </div>
                <div class="category-card">
                    <div class="category-icon">🥛</div>
                    <div class="category-name">Milk & Dairy</div>
                    <div class="category-subtitle">Process food</div>
                </div>
                <div class="category-card">
                    <div class="category-icon">🥩</div>
                    <div class="category-name">Meat & Seafood</div>
                    <div class="category-subtitle">Fresh daily</div>
                </div>
                <div class="category-card">
                    <div class="category-icon">🥫</div>
                    <div class="category-name">Canned Goods</div>
                    <div class="category-subtitle">Long shelf life</div>
                </div>
            </div>
        </div>
    </section>

    <section class="products-section">
        <div class="container-fluid">
            <div class="section-header">
                <h2 class="section-title">You might need</h2>
                <a href="#" class="see-all-link">See more →</a>
            </div>
            <div class="products-grid">
                <div class="product-card" data-product-id="1" onclick="viewProduct(1)">
                    <div class="product-image"><span style="font-size: 60px;">🥬</span></div>
                    <div class="product-name">Beetroot</div>
                    <div class="product-subtitle">(Local shop)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">17<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="2" onclick="viewProduct(2)">
                    <div class="product-image"><span style="font-size: 60px;">🥑</span></div>
                    <div class="product-name">Italian Avocado</div>
                    <div class="product-subtitle">(Local shop)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">12<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="3" onclick="viewProduct(3)">
                    <div class="product-image"><span style="font-size: 60px;">🧀</span></div>
                    <div class="product-name">Szam amm</div>
                    <div class="product-subtitle">(Process food)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">14<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="4" onclick="viewProduct(4)">
                    <div class="product-image"><span style="font-size: 60px;">🥩</span></div>
                    <div class="product-name">Beef Minced</div>
                    <div class="product-subtitle">(Cut Box)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">16<span class="price-decimal">.22</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="5" onclick="viewProduct(5)">
                    <div class="product-image"><span style="font-size: 60px;">🥤</span></div>
                    <div class="product-name">Cold drinks</div>
                    <div class="product-subtitle">(Sprite)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">18<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="6" onclick="viewProduct(6)">
                    <div class="product-image"><span style="font-size: 60px;">🍗</span></div>
                    <div class="product-name">Plant Hunter</div>
                    <div class="product-subtitle">(Frozen pack)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">20<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="7" onclick="viewProduct(7)">
                    <div class="product-image"><span style="font-size: 60px;">🥕</span></div>
                    <div class="product-name">Deshi Gajor</div>
                    <div class="product-subtitle">(Local Carrot)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">19<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="8" onclick="viewProduct(8)">
                    <div class="product-image"><span style="font-size: 60px;">🥒</span></div>
                    <div class="product-name">Deshi Shosha</div>
                    <div class="product-subtitle">(Local Cucumb)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">04<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="9" onclick="viewProduct(9)">
                    <div class="product-image"><span style="font-size: 60px;">🍟</span></div>
                    <div class="product-name">Lays chips</div>
                    <div class="product-subtitle">(Bacon)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">21<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>

                <div class="product-card" data-product-id="10" onclick="viewProduct(10)">
                    <div class="product-image"><span style="font-size: 60px;">🥬</span></div>
                    <div class="product-name">Badhakopi</div>
                    <div class="product-subtitle">(Local Cabbage)</div>
                    <div class="product-weight">500 gm.</div>
                    <div class="product-price">09<span class="price-decimal">.29</span><span class="price-currency">$</span></div>
                </div>
            </div>
        </div>
    </section>

    <section id="discount-carousel" style="padding:32px 20px;">
        <div class="dc-card">
            <button class="dc-btn dc-left" aria-label="Previous item">‹</button>

            <div class="dc-track-wrap" aria-live="polite">
                <ul class="dc-track"></ul>
            </div>

            <button class="dc-btn dc-right" aria-label="Next item">›</button>

            <div class="dc-thumbs" aria-hidden="false"></div>

            <div class="dc-upload">
                <label class="dc-upload-label">Add images (optional)
                    <input id="dcFileInput" type="file" accept="image/*" multiple>
                </label>
            </div>
        </div>
    </section>


    <script>
        // Function to redirect to product detail page
        function viewProduct(productId) {
            // Redirect to product page with ID parameter
            window.location.href = `/views/customer/product-details.php?id=${productId}`;
        }

        // Optional: Add hover effect enhancement
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });


        (function() {
            const initialImages = [
                '/assets/images/product1.png',
                '/assets/images/product2.png',
                '/assets/images/product3.png',
                '/assets/images/product4.png'
            ];

            const sampleData = [{
                    title: "Bobs red mill whole wheat",
                    price: "429.12$",
                    meta: "⏰ 270 : 13 : 10 : 32",
                    rating: "⭐ 4.5 (15 reviews)",
                    note: "100 sold in last 35 hour"
                },
                {
                    title: "Organic Coconut Oil",
                    price: "199.50$",
                    meta: "⏰ 120 : 05 : 04 : 12",
                    rating: "⭐ 4.7 (28 reviews)",
                    note: "56 sold recently"
                },
                {
                    title: "Fresh Avocados (Pack)",
                    price: "34.99$",
                    meta: "⏰ 14 : 02 : 11 : 45",
                    rating: "⭐ 4.3 (9 reviews)",
                    note: "20 sold recently"
                },
                {
                    title: "Whole Grain Bread",
                    price: "52.00$",
                    meta: "⏰ 05 : 00 : 09 : 12",
                    rating: "⭐ 4.8 (120 reviews)",
                    note: "120 sold recently"
                }
            ];

            const track = document.querySelector('#discount-carousel .dc-track');
            const thumbs = document.querySelector('#discount-carousel .dc-thumbs');
            const leftBtn = document.querySelector('#discount-carousel .dc-left');
            const rightBtn = document.querySelector('#discount-carousel .dc-right');
            const fileInput = document.getElementById('dcFileInput');

            let images = initialImages.slice();
            let index = 0;

            function buildSlides() {
                track.innerHTML = '';
                thumbs.innerHTML = '';

                for (let i = 0; i < images.length; i++) {
                    const img = images[i];
                    const data = sampleData[i] || sampleData[i % sampleData.length];

                    // slide
                    const li = document.createElement('li');
                    li.className = 'dc-slide';
                    li.dataset.idx = i;
                    li.innerHTML = `
          <div class="dc-image-wrap">
            <div class="dc-badge"><div style="text-align:center;">${Math.floor(Math.random()*60)+10}%<small>DISCOUNT</small></div></div>
            <img src="${img}" alt="${escapeHtml(data.title)}">
          </div>
          <div class="dc-details">
            <div class="dc-meta">${escapeHtml(data.meta)}</div>
            <div class="dc-title">${escapeHtml(data.title)}</div>
            <div class="dc-rating">${escapeHtml(data.rating)}</div>
            <div class="dc-price">${escapeHtml(data.price)}</div>
            <div class="dc-actions">
              <button class="dc-btn-ghost" aria-label="Add to bucket">Add to bucket</button>
              <button class="dc-btn-primary" aria-label="Buy now">Buy now</button>
            </div>
            <div class="dc-links">
              <a href="#" style="color:#2d3748; text-decoration:none;">Add to wishlist</a>
              <a href="#" style="color:#2d3748; text-decoration:none;">Compare</a>
            </div>
            <div class="dc-atrib">${escapeHtml(data.note)}</div>
          </div>
        `;
                    track.appendChild(li);

                    // thumb
                    const t = document.createElement('div');
                    t.className = 'dc-thumb';
                    t.dataset.idx = i;
                    t.innerHTML = `<img src="${img}" alt="${escapeHtml(data.title)}" />`;
                    t.addEventListener('click', () => goToSlide(i));
                    thumbs.appendChild(t);
                }

                // set active thumb
                updateActiveThumb();
                updateTrackPosition();
            }

            function updateActiveThumb() {
                document.querySelectorAll('#discount-carousel .dc-thumb').forEach((el) => el.classList.remove('active'));
                const a = document.querySelector(`#discount-carousel .dc-thumb[data-idx="${index}"]`);
                if (a) a.classList.add('active');
            }

            function updateTrackPosition() {
                const slideWidth = document.querySelector('#discount-carousel .dc-track-wrap').clientWidth;
                track.style.transform = `translateX(-${index * slideWidth}px)`;
                updateActiveThumb();
            }

            function prev() {
                index = (index - 1 + images.length) % images.length;
                updateTrackPosition();
            }

            function next() {
                index = (index + 1) % images.length;
                updateTrackPosition();
            }

            function goToSlide(i) {
                index = i % images.length;
                updateTrackPosition();
            }

            // helper to sanitize strings
            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, (m) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [m]));
            }

            // allow left/right buttons
            leftBtn.addEventListener('click', prev);
            rightBtn.addEventListener('click', next);

            // handle window resize (recompute slide width)
            window.addEventListener('resize', () => {
                updateTrackPosition();
            });

            // file input: add uploaded images to carousel (client-side only)
            fileInput.addEventListener('change', (e) => {
                const files = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));
                if (!files.length) return;
                const newUrls = files.map(f => URL.createObjectURL(f));
                images = images.concat(newUrls);
                // optional: add simple data entries for new images
                for (let i = 0; i < files.length; i++) sampleData.push({
                    title: files[i].name,
                    price: "—",
                    meta: "⏰ now",
                    rating: "⭐ —",
                    note: "Uploaded image"
                });
                buildSlides();
                // jump to the first of newly added
                goToSlide(images.length - files.length);
            });

            // allow swipe (mobile)
            (function addSwipe() {
                const container = document.querySelector('#discount-carousel .dc-track-wrap');
                let startX = 0,
                    moved = false;
                container.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                    moved = false;
                });
                container.addEventListener('touchmove', (e) => {
                    moved = true;
                });
                container.addEventListener('touchend', (e) => {
                    if (!moved) return;
                    const diff = (e.changedTouches[0].clientX - startX);
                    if (diff < -30) next();
                    if (diff > 30) prev();
                });
            })();

            // initialize
            buildSlides();

            // expose for debugging (optional)
            window._dc = {
                goToSlide,
                next,
                prev,
                images,
                buildSlides
            };

        })();
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>