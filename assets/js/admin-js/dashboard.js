document.addEventListener('DOMContentLoaded', () => {
  const sections = {
    dashboardBtn: 'dashboardSection',
    posBtn: 'posSection',
    inventoryBtn: 'inventorySection'
  };

  Object.keys(sections).forEach(id => {
    document.getElementById(id).addEventListener('click', () => {
      Object.values(sections).forEach(sec => document.getElementById(sec).classList.add('d-none'));
      document.getElementById(sections[id]).classList.remove('d-none');
    });
  });

  // POS logic
  let total = 0;
  const posTotal = document.getElementById('posTotal');

  document.querySelectorAll('.add-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const price = parseFloat(btn.dataset.price);
      total += price;
      posTotal.textContent = total.toFixed(2);
    });
  });

  document.getElementById('checkoutBtn').addEventListener('click', () => {
    if (total > 0) {
      alert('Checkout complete! Total: ₱' + posTotal.textContent);
      total = 0;
      posTotal.textContent = '0';
    } else {
      alert('No items added.');
    }
  });
});
