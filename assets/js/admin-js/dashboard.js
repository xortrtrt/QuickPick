
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
