/**
 * DomainHub - Main JavaScript
 * Handles search, toast notifications, and modal interactions
 */

// ==================== Toast Notification System ====================
class ToastManager {
    constructor() {
        this.container = null;
        this.queue = [];
        this.isAnimating = false;
        this.init();
    }

    init() {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    }

    show(message, type = 'info', duration = 5000) {
        const toast = {
            id: Date.now(),
            message,
            type,
            duration
        };

        this.queue.push(toast);
        this.processQueue();
    }

    processQueue() {
        if (this.isAnimating || this.queue.length === 0) return;

        this.isAnimating = true;
        const toast = this.queue.shift();
        this.createToastElement(toast);

        setTimeout(() => {
            this.removeToast(toast.id);
        }, toast.duration);
    }

    createToastElement(toast) {
        const element = document.createElement('div');
        element.className = `toast ${toast.type}`;
        element.dataset.id = toast.id;

        const icon = this.getIconForType(toast.type);
        element.innerHTML = `
            <span class="toast-icon">${icon}</span>
            <span class="toast-message">${toast.message}</span>
        `;

        this.container.appendChild(element);

        // Trigger animation
        requestAnimationFrame(() => {
            element.classList.add('show');
        });
    }

    getIconForType(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    removeToast(id) {
        const element = this.container.querySelector(`[data-id="${id}"]`);
        if (element) {
            element.classList.remove('show');
            setTimeout(() => {
                element.remove();
                this.isAnimating = false;
                this.processQueue();
            }, 300);
        }
    }

    clear() {
        this.container.innerHTML = '';
        this.queue = [];
        this.isAnimating = false;
    }
}

// Initialize global toast manager
window.toast = new ToastManager();

// ==================== Modal System ====================
class ModalManager {
    constructor() {
        this.overlay = null;
        this.modal = null;
        this.init();
    }

    init() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'modal-overlay';
        this.overlay.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title"></h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer"></div>
            </div>
        `;
        document.body.appendChild(this.overlay);

        // Close on overlay click
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.close();
            }
        });

        // Close button
        const closeBtn = this.overlay.querySelector('.modal-close');
        closeBtn.addEventListener('click', () => this.close());

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.overlay.classList.contains('active')) {
                this.close();
            }
        });
    }

    open(title, content, footer = '') {
        this.overlay.querySelector('.modal-title').textContent = title;
        this.overlay.querySelector('.modal-body').innerHTML = content;
        this.overlay.querySelector('.modal-footer').innerHTML = footer;
        this.overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    isOpen() {
        return this.overlay.classList.contains('active');
    }
}

// Initialize global modal manager
window.modal = new ModalManager();

// ==================== Domain Search ====================
class DomainSearch {
    constructor() {
        this.searchForm = document.getElementById('domainSearchForm');
        this.searchInput = document.getElementById('domainInput');
        this.extensionSelect = document.getElementById('extensionSelect');
        this.resultsContainer = document.getElementById('searchResults');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        
        if (this.searchForm) {
            this.init();
        }
    }

    init() {
        this.searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });

        // Real-time validation
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.validateDomain(e.target.value);
            });
        }
    }

    validateDomain(domain) {
        const validPattern = /^[a-z0-9-]+$/i;
        if (domain && !validPattern.test(domain)) {
            window.toast.show('نام دامنه فقط می‌تواند شامل حروف، اعداد و خط تیره باشد', 'warning');
        }
    }

    async performSearch() {
        const domain = this.searchInput.value.trim();
        const extension = this.extensionSelect.value;

        if (!domain) {
            window.toast.show('لطفاً نام دامنه را وارد کنید', 'error');
            return;
        }

        // Show loading
        if (this.loadingSpinner) {
            this.loadingSpinner.classList.remove('hidden');
        }
        if (this.resultsContainer) {
            this.resultsContainer.innerHTML = '';
        }

        try {
            const response = await fetch('/api/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ domain, extension })
            });

            const result = await response.json();

            if (result.success) {
                this.displayResults(result.data);
            } else {
                window.toast.show(result.message || 'خطا در جستجوی دامنه', 'error');
            }
        } catch (error) {
            console.error('Search error:', error);
            window.toast.show('خطا در ارتباط با سرور. لطفاً مجدداً تلاش کنید.', 'error');
        } finally {
            if (this.loadingSpinner) {
                this.loadingSpinner.classList.add('hidden');
            }
        }
    }

    displayResults(data) {
        if (!this.resultsContainer) return;

        const statusClass = data.status === 'available' ? 'success' : 
                           data.status === 'registered' ? 'warning' : 'error';
        
        const statusText = data.status === 'available' ? 'آزاد' : 
                          data.status === 'registered' ? 'ثبت شده' : 'نامشخص';

        let html = `
            <div class="search-result-card ${statusClass}">
                <div class="result-header">
                    <h3>${data.domain}</h3>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
                <div class="result-body">
                    <p class="result-message">${data.message}</p>
                    ${data.price_usd ? `<p class="result-price">قیمت: ${formatPrice(data.price_toman)} تومان</p>` : ''}
                </div>
                <div class="result-actions">
        `;

        if (data.status === 'available') {
            html += `
                <button class="btn btn-success" onclick="orderDomain('${data.domain}')">
                    ثبت سفارش
                </button>
            `;
        } else if (data.status === 'registered') {
            html += `
                <button class="btn btn-secondary" onclick="showWhois('${data.domain}')">
                    مشاهده Whois
                </button>
            `;

            if (data.suggestions && data.suggestions.length > 0) {
                html += `
                    <div class="suggestions-section">
                        <h4>پیشنهادهای جایگزین:</h4>
                        <div class="suggestions-list">
                            ${data.suggestions.map(s => `
                                <div class="suggestion-item">
                                    <span>${s.domain}</span>
                                    <span class="price">${formatPrice(usdToToman(s.price))}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        }

        html += `
                </div>
            </div>
        `;

        this.resultsContainer.innerHTML = html;
        window.toast.show('نتیجه جستجو آماده است', 'success');
    }
}

// ==================== Helper Functions ====================
function formatPrice(amount) {
    return new Intl.NumberFormat('fa-IR').format(amount);
}

function usdToToman(usd) {
    // This should come from server, using default for now
    const rate = 50000; // Default fallback
    return usd * rate;
}

function orderDomain(domain) {
    window.toast.show('در حال انتقال به صفحه ثبت سفارش...', 'info');
    setTimeout(() => {
        // For phase 1, redirect to Telegram
        window.location.href = 'https://t.me/your_telegram_id?text=سفارش دامنه: ' + domain;
    }, 1000);
}

function showWhois(domain) {
    // Fetch Whois data and display in modal
    window.toast.show('در حال دریافت اطلاعات Whois...', 'info');
    
    // Mock data for now - will be replaced with actual API call
    setTimeout(() => {
        const whoisContent = `
            <div class="whois-info">
                <p><strong>دامنه:</strong> ${domain}</p>
                <p><strong>رجیسترار:</strong> Namecheap Inc.</p>
                <p><strong>تاریخ ثبت:</strong> 2020-01-15</p>
                <p><strong>تاریخ انقضا:</strong> 2025-01-15</p>
                <p><strong>وضعیت مالک:</strong> مخفی شده برای حریم خصوصی</p>
            </div>
        `;
        
        window.modal.open(
            'اطلاعات Whois',
            whoisContent,
            '<button class="btn btn-primary" onclick="modal.close()">بستن</button>'
        );
    }, 1000);
}

// ==================== Search History (Guest) ====================
function saveGuestSearch(domain, status) {
    const history = getGuestSearchHistory();
    history.unshift({ domain, status, timestamp: Date.now() });
    
    // Limit to 50 items
    if (history.length > 50) {
        history.splice(50);
    }
    
    localStorage.setItem('dh_search_history', JSON.stringify(history));
}

function getGuestSearchHistory() {
    const stored = localStorage.getItem('dh_search_history');
    return stored ? JSON.parse(stored) : [];
}

function displayGuestHistory() {
    const history = getGuestSearchHistory();
    const container = document.getElementById('guestHistory');
    
    if (!container || history.length === 0) return;
    
    container.innerHTML = `
        <h3>تاریخچه جستجوهای شما</h3>
        <div class="history-list">
            ${history.map(item => `
                <div class="history-item ${item.status}">
                    <span class="domain">${item.domain}</span>
                    <span class="status">${item.status === 'available' ? 'آزاد' : 'ثبت شده'}</span>
                </div>
            `).join('')}
        </div>
        <button class="btn btn-secondary" onclick="clearGuestHistory()">پاک کردن تاریخچه</button>
    `;
}

function clearGuestHistory() {
    localStorage.removeItem('dh_search_history');
    displayGuestHistory();
    window.toast.show('تاریخچه پاک شد', 'success');
}

// ==================== Initialize on DOM Ready ====================
document.addEventListener('DOMContentLoaded', () => {
    new DomainSearch();
    displayGuestHistory();
    
    // Add scroll reveal animations
    const scrollElements = document.querySelectorAll('.feature-card, .extension-card');
    scrollElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.animation = `fadeInUp 0.6s ease-out ${index * 0.1}s forwards`;
    });
});

// ==================== Service Worker Registration (Optional) ====================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            console.log('Service Worker registration failed');
        });
    });
}
