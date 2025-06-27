// Admin Order Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Update status counts
    function updateStatusCounts() {
        const statuses = ['menunggu', 'memasak', 'selesai', 'ditolak'];
        statuses.forEach(status => {
            const count = document.querySelectorAll(`.status-${status}`).length;
            const countElement = document.getElementById(`count-${status}`);
            if (countElement) {
                countElement.textContent = count;
            }
        });
    }

    // Handle status updates
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('status-btn')) {
            const orderId = e.target.dataset.orderId;
            const newStatus = e.target.dataset.status;
            
            if (confirm(`Apakah Anda yakin ingin mengubah status pesanan menjadi "${newStatus}"?`)) {
                updateOrderStatus(orderId, newStatus);
            }
        }
    });

    function updateOrderStatus(orderId, newStatus) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('api/update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('Status berhasil diupdate!', 'success');
                
                // Reload page after short delay
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification('Gagal mengupdate status: ' + data.message, 'error');
                // Restore button
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat mengupdate status', 'error');
            // Restore button
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Animate out and remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Auto-refresh functionality
    let autoRefreshInterval;
    let autoRefreshEnabled = true;

    function startAutoRefresh() {
        if (autoRefreshEnabled) {
            autoRefreshInterval = setInterval(() => {
                showAutoRefreshIndicator();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }, 30000); // 30 seconds
        }
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshEnabled = false;
        }
    }

    function showAutoRefreshIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'auto-refresh-indicator';
        indicator.textContent = 'Memperbarui data...';
        document.body.appendChild(indicator);
        
        setTimeout(() => {
            indicator.classList.add('show');
        }, 100);
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + R to toggle auto-refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            if (autoRefreshEnabled) {
                stopAutoRefresh();
                showNotification('Auto-refresh dinonaktifkan', 'info');
            } else {
                startAutoRefresh();
                showNotification('Auto-refresh diaktifkan', 'success');
            }
        }
        
        // F5 to manual refresh
        if (e.key === 'F5') {
            e.preventDefault();
            location.reload();
        }
    });

    // Search functionality for table-based search
    function setupSearch() {
        const searchInput = document.getElementById('search-orders');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const tableGroups = document.querySelectorAll('.border.border-gray-200.rounded-lg');
                
                tableGroups.forEach(tableGroup => {
                    const tableText = tableGroup.textContent.toLowerCase();
                    const hasMatch = tableText.includes(searchTerm);
                    
                    if (hasMatch) {
                        tableGroup.style.display = '';
                        // Highlight matching text if needed
                        highlightMatchingText(tableGroup, searchTerm);
                    } else {
                        tableGroup.style.display = 'none';
                    }
                });
            });
        }
    }

    // Highlight matching text in search results
    function highlightMatchingText(element, searchTerm) {
        if (!searchTerm) return;
        
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );
        
        const textNodes = [];
        let node;
        while (node = walker.nextNode()) {
            textNodes.push(node);
        }
        
        textNodes.forEach(textNode => {
            const text = textNode.textContent;
            if (text.toLowerCase().includes(searchTerm)) {
                const span = document.createElement('span');
                span.innerHTML = text.replace(
                    new RegExp(searchTerm, 'gi'),
                    match => `<mark class="bg-yellow-200">${match}</mark>`
                );
                textNode.parentNode.replaceChild(span, textNode);
            }
        });
    }

    // Filter functionality for status-based filtering
    function setupFilters() {
        const filterSelect = document.getElementById('status-filter');
        if (filterSelect) {
            filterSelect.addEventListener('change', function(e) {
                const selectedStatus = e.target.value;
                const tableGroups = document.querySelectorAll('.border.border-gray-200.rounded-lg');
                
                tableGroups.forEach(tableGroup => {
                    const statusBadges = tableGroup.querySelectorAll('.status-badge');
                    let hasMatchingStatus = false;
                    
                    if (selectedStatus === 'all') {
                        hasMatchingStatus = true;
                    } else {
                        statusBadges.forEach(badge => {
                            if (badge.classList.contains(`status-${selectedStatus}`)) {
                                hasMatchingStatus = true;
                            }
                        });
                    }
                    
                    tableGroup.style.display = hasMatchingStatus ? '' : 'none';
                });
            });
        }
    }

    // Table collapse/expand functionality
    function setupTableCollapse() {
        const tableHeaders = document.querySelectorAll('.bg-gradient-to-r.from-blue-50.to-indigo-50');
        
        tableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const tableGroup = this.closest('.border.border-gray-200.rounded-lg');
                const ordersList = tableGroup.querySelector('.divide-y.divide-gray-200');
                const isCollapsed = ordersList.style.display === 'none';
                
                ordersList.style.display = isCollapsed ? '' : 'none';
                
                // Add visual indicator
                const indicator = this.querySelector('.collapse-indicator');
                if (indicator) {
                    indicator.innerHTML = isCollapsed ? '<i class="fas fa-chevron-up"></i>' : '<i class="fas fa-chevron-down"></i>';
                }
            });
            
            // Add collapse indicator
            const indicator = document.createElement('div');
            indicator.className = 'collapse-indicator text-gray-400 hover:text-gray-600 cursor-pointer';
            indicator.innerHTML = '<i class="fas fa-chevron-down"></i>';
            header.appendChild(indicator);
        });
    }

    // Initialize everything
    function init() {
        updateStatusCounts();
        setupSearch();
        setupFilters();
        setupTableCollapse();
        startAutoRefresh();
        
        // Show welcome message
        showNotification('Selamat datang di Admin Order Management!', 'info');
    }

    // Start initialization
    init();
}); 