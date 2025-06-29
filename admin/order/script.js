// Admin Order Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Order Management JavaScript loaded');
    
    // Update statistik saat halaman dimuat
    updateStats();
    
    // Event listeners untuk tombol status pesanan
    document.addEventListener('click', function(e) {
        console.log('Click event detected on:', e.target);
        
        // Cari elemen tombol terdekat yang memiliki class yang kita cari
        let targetElement = e.target;
        
        // Cari parent element yang memiliki class status-btn
        while (targetElement && !targetElement.classList.contains('status-btn')) {
            targetElement = targetElement.parentElement;
        }
        
        if (targetElement && targetElement.classList.contains('status-btn')) {
            console.log('Status button clicked:', targetElement);
            const orderId = targetElement.getAttribute('data-order-id');
            const newStatus = targetElement.getAttribute('data-status');
            console.log('Order ID:', orderId, 'New Status:', newStatus);
            
            if (orderId && newStatus) {
                updateOrderStatus(orderId, newStatus, targetElement);
            } else {
                console.error('Missing order ID or status');
                alert('Error: Missing order ID or status');
            }
        }
        
        // Cari parent element yang memiliki class table-action-btn
        targetElement = e.target;
        while (targetElement && !targetElement.classList.contains('table-action-btn')) {
            targetElement = targetElement.parentElement;
        }
        
        if (targetElement && targetElement.classList.contains('table-action-btn')) {
            console.log('Table action button clicked:', targetElement);
            const tableId = targetElement.getAttribute('data-table-id');
            const action = targetElement.getAttribute('data-action');
            console.log('Table ID:', tableId, 'Action:', action);
            
            if (tableId && action) {
                handleTableAction(tableId, action, targetElement);
            } else {
                console.error('Missing table ID or action');
                alert('Error: Missing table ID or action');
            }
        }
    });
    
    // Search dan filter functionality
    const searchInput = document.getElementById('search-orders');
    const statusFilter = document.getElementById('status-filter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterOrders);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterOrders);
    }
    
    // Test if buttons exist
    const statusButtons = document.querySelectorAll('.status-btn');
    const tableActionButtons = document.querySelectorAll('.table-action-btn');
    console.log('Found status buttons:', statusButtons.length);
    console.log('Found table action buttons:', tableActionButtons.length);
});

// Fungsi untuk update status pesanan
function updateOrderStatus(orderId, newStatus, button) {
    console.log('updateOrderStatus called with:', { orderId, newStatus });
    const originalText = button.innerHTML;
    
    // Disable button dan tampilkan loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...';
    
    console.log('Sending request to api/update_order_status.php');
    
    // Buat FormData untuk mengirim data
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    
    fetch('api/update_order_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Update tampilan
            updateOrderDisplay(orderId, newStatus);
            updateStats();
            showNotification('Status pesanan berhasil diperbarui', 'success');
        } else {
            showNotification(data.message || 'Gagal memperbarui status pesanan', 'error');
            // Restore button
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat memperbarui status', 'error');
        // Restore button
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Fungsi untuk handle aksi meja
function handleTableAction(tableId, action, button) {
    console.log('handleTableAction called with:', { tableId, action });
    const originalText = button.innerHTML;
    const sessionId = button.getAttribute('data-session-id');
    
    // Disable button dan tampilkan loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...';
    
    let endpoint = '';
    let formData = new FormData();
    formData.append('table_id', tableId);
    
    switch(action) {
        case 'complete-all':
            endpoint = 'api/complete_all_orders.php';
            break;
        case 'vacate-table':
            endpoint = 'api/mark_vacant.php';
            formData.append('session_id', sessionId);
            break;
        case 'complete-booking':
            endpoint = 'api/mark_completed.php';
            break;
        default:
            showNotification('Aksi tidak valid', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
            return;
    }
    
    console.log('Sending request to:', endpoint);
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showNotification(data.message, 'success');
            // Reload halaman untuk memperbarui data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Gagal melakukan aksi', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat melakukan aksi', 'error');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Fungsi untuk update tampilan pesanan
function updateOrderDisplay(orderId, newStatus) {
    const orderElement = document.querySelector(`[data-order-id="${orderId}"]`).closest('.px-6.py-4');
    if (!orderElement) {
        console.error('Order element not found');
        return;
    }
    
    const statusBadge = orderElement.querySelector('.status-badge');
    const actionButtons = orderElement.querySelector('.action-buttons');
    
    if (statusBadge) {
        // Update status badge
        statusBadge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium status-badge status-${newStatus}`;
        statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    }
    
    if (actionButtons) {
        // Update action buttons berdasarkan status baru
        updateActionButtons(actionButtons, newStatus, orderId);
    }
}

// Fungsi untuk update action buttons
function updateActionButtons(container, status, orderId) {
    container.innerHTML = '';
    
    if (status === 'menunggu') {
        container.innerHTML = `
            <button class="status-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                    data-order-id="${orderId}" data-status="memasak">
                <i class="fas fa-fire mr-1"></i>Terima Pesanan
            </button>
            <button class="status-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                    data-order-id="${orderId}" data-status="ditolak">
                <i class="fas fa-times mr-1"></i>Tolak Pesanan
            </button>
        `;
    } else if (status === 'memasak') {
        container.innerHTML = `
            <button class="status-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                    data-order-id="${orderId}" data-status="selesai">
                <i class="fas fa-check mr-1"></i>Tandai Sebagai Selesai
            </button>
            <button class="status-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                    data-order-id="${orderId}" data-status="ditolak">
                <i class="fas fa-times mr-1"></i>Tolak Pesanan
            </button>
        `;
    }
    // Untuk status 'selesai' dan 'ditolak' tidak ada action buttons
}

// Fungsi untuk update statistik
function updateStats() {
    const pendingCount = document.querySelectorAll('.status-badge.status-menunggu').length;
    const cookingCount = document.querySelectorAll('.status-badge.status-memasak').length;
    const completedCount = document.querySelectorAll('.status-badge.status-selesai').length;
    const rejectedCount = document.querySelectorAll('.status-badge.status-ditolak').length;
    
    const menungguElement = document.getElementById('count-menunggu');
    const memasakElement = document.getElementById('count-memasak');
    const selesaiElement = document.getElementById('count-selesai');
    const ditolakElement = document.getElementById('count-ditolak');
    
    if (menungguElement) menungguElement.textContent = pendingCount;
    if (memasakElement) memasakElement.textContent = cookingCount;
    if (selesaiElement) selesaiElement.textContent = completedCount;
    if (ditolakElement) ditolakElement.textContent = rejectedCount;
}

// Fungsi untuk filter orders
function filterOrders() {
    const searchTerm = document.getElementById('search-orders').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const tableGroups = document.querySelectorAll('.border.border-gray-200.rounded-lg');
    
    tableGroups.forEach(tableGroup => {
        const orders = tableGroup.querySelectorAll('.px-6.py-4');
        let hasVisibleOrders = false;
        
        orders.forEach(order => {
            const menuName = order.querySelector('.text-sm.font-medium')?.textContent.toLowerCase() || '';
            const username = order.querySelector('.text-gray-500 .font-medium')?.textContent.toLowerCase() || '';
            const status = order.querySelector('.status-badge')?.textContent.toLowerCase() || '';
            
            const matchesSearch = menuName.includes(searchTerm) || 
                                username.includes(searchTerm) ||
                                tableGroup.querySelector('h4').textContent.toLowerCase().includes(searchTerm);
            
            const matchesStatus = statusFilter === 'all' || status === statusFilter;
            
            if (matchesSearch && matchesStatus) {
                order.style.display = 'block';
                hasVisibleOrders = true;
            } else {
                order.style.display = 'none';
            }
        });
        
        // Tampilkan/sembunyikan table group berdasarkan apakah ada orders yang visible
        if (hasVisibleOrders || searchTerm === '') {
            tableGroup.style.display = 'block';
        } else {
            tableGroup.style.display = 'none';
        }
    });
}

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm transform transition-all duration-300 translate-x-full`;
    
    const bgColor = type === 'success' ? 'bg-green-500' : 
                   type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
    notification.className += ` ${bgColor} text-white`;
    
    notification.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
} 