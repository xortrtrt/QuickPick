// Handle quantity changes
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                const cartItemID = this.dataset.itemid;
                const action = this.dataset.action;

                fetch('/controllers/orders/update_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `cartID=${cartItemID}&action=${action}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update quantity
                            const qtyElem = document.getElementById(`qty-${cartItemID}`);
                            qtyElem.innerText = data.quantity;

                            // Update total
                            document.querySelectorAll('#cart-total').forEach(el => el.innerText = `₱${data.total}`);
                        } else {
                            alert('Failed to update cart: ' + data.message);
                        }
                    })
                    .catch(err => console.error(err));
            });
        });




document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const cartItemID = this.dataset.itemid;
        if(!cartItemID) return;
        if(!confirm('Remove this item from cart?')) return;

        fetch('../../controllers/orders/remove_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `cartItemID=${cartItemID}`
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success'){
                // Remove the cart card
                const card = document.getElementById(`cart-item-${cartItemID}`);
                if(card) card.remove();

                // Update totals
                const subtotalElem = document.getElementById('cart-subtotal');
                const totalElem = document.getElementById('cart-total');
                if(subtotalElem) subtotalElem.innerText = `₱${data.total}`;
                if(totalElem) totalElem.innerText = `₱${data.total}`;

                // If cart is empty, show empty message
                const cartContainer = document.getElementById('cart-items-container');
                if(cartContainer && cartContainer.children.length === 0){
                    cartContainer.innerHTML = '<p>Your cart is empty. <a href="/views/customer/dashboard.php">Start shopping now!</a></p>';
                }
            } else {
                alert("Failed to remove item: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error removing item.');
        });
    });
});
