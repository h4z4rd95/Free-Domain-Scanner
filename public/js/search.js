/**
 * DomainHub - Search Page JavaScript
 * Handles advanced domain search with multiple extensions
 */

class AdvancedDomainSearch {
    constructor() {
        this.form = document.getElementById('domainSearchForm');
        this.domainInput = document.getElementById('domainInput');
        this.extensionsGrid = document.getElementById('extensionsGrid');
        this.resultsContainer = document.getElementById('searchResults');
        this.tabButtons = document.querySelectorAll('.tab-btn');
        this.selectAllBtn = document.querySelector('.select-all-btn');
        
        this.selectedExtensions = [];
        this.isSearching = false;
        
        this.init();
    }

    init() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Category tabs
        this.tabButtons.forEach(btn => {
            btn.addEventListener('click', () => this.filterByCategory(btn.dataset.category));
        });

        // Select all button
        if (this.selectAllBtn) {
            this.selectAllBtn.addEventListener('click', () => this.toggleSelectAll());
        }

        // Extension checkboxes
        this.initExtensionCheckboxes();

        // Initialize selected extensions
        this.updateSelectedExtensions();
    }

    initExtensionCheckboxes() {
        const checkboxes = this.extensionsGrid?.querySelectorAll('input[type="checkbox"]') || [];
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateSelectedExtensions();
            });
        });
    }

    filterByCategory(category) {
        // Update active tab
        this.tabButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.category === category);
        });

        // Filter extensions
        const checkboxes = this.extensionsGrid.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (category === 'all' || checkbox.dataset.category === category) {
                checkbox.closest('.extension-checkbox').style.display = 'flex';
            } else {
                checkbox.closest('.extension-checkbox').style.display = 'none';
            }
        });
    }

    toggleSelectAll() {
        const visibleCheckboxes = Array.from(this.extensionsGrid.querySelectorAll('input[type="checkbox"]'))
            .filter(cb => cb.closest('.extension-checkbox').style.display !== 'none');
        
        const allChecked = visibleCheckboxes.every(cb => cb.checked);
        
        visibleCheckboxes.forEach(cb => {
            cb.checked = !allChecked;
        });

        this.updateSelectedExtensions();
        this.selectAllBtn.textContent = allChecked ? 'انتخاب همه' : 'لغو انتخاب همه';
    }

    updateSelectedExtensions() {
        const checkboxes = this.extensionsGrid.querySelectorAll('input[type="checkbox"]:checked');
        this.selectedExtensions = Array.from(checkboxes).map(cb => cb.value);
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        const domainName = this.domainInput.value.trim().toLowerCase();
        
        if (!domainName) {
            window.toast.show('لطفاً نام دامنه را وارد کنید', 'error');
            return;
        }

        if (!/^[a-z0-9-]+$/.test(domainName)) {
            window.toast.show('نام دامنه فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد', 'warning');
            return;
        }

        if (this.selectedExtensions.length === 0) {
            window.toast.show('حداقل یک پسوند را انتخاب کنید', 'warning');
            return;
        }

        if (this.isSearching) {
            window.toast.show('در حال بررسی... لطفاً صبر کنید', 'info');
            return;
        }

        this.isSearching = true;
        this.resultsContainer.innerHTML = `
            <div class="loading-state">
                <div class="spinner"></div>
                <p>در حال بررسی وضعیت دامنه‌ها...</p>
            </div>
        `;

        try {
            const results = await Promise.all(
                this.selectedExtensions.map(ext => this.checkDomain(domainName, ext))
            );

            this.displayResults(results);
            
            // Save to history
            results.forEach(result => {
                if (result.success) {
                    saveGuestSearch(result.domain, result.status);
                }
            });

        } catch (error) {
            console.error('Search error:', error);
            window.toast.show('خطا در برقراری ارتباط با سرور', 'error');
        } finally {
            this.isSearching = false;
        }
    }

    async checkDomain(domain, extension) {
        try {
            const response = await fetch('/api/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ domain, extension })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error(`Error checking ${domain}.${extension}:`, error);
            return {
                success: false,
                domain: `${domain}.${extension}`,
                message: 'خطا در بررسی'
            };
        }
    }

    displayResults(results) {
        const available = results.filter(r => r.success && r.data?.status === 'available');
        const registered = results.filter(r => r.success && r.data?.status === 'registered');
        const errors = results.filter(r => !r.success);

        let html = `
            <div class="results-summary">
                <div class="summary-item success">
                    <span class="count">${available.length}</span>
                    <span class="label">دامنه آزاد</span>
                </div>
                <div class="summary-item warning">
                    <span class="count">${registered.length}</span>
                    <span class="label">دامنه ثبت‌شده</span>
                </div>
                ${errors.length > 0 ? `
                    <div class="summary-item error">
                        <span class="count">${errors.length}</span>
                        <span class="label">خطا</span>
                    </div>
                ` : ''}
            </div>
        `;

        // Show available domains first
        if (available.length > 0) {
            html += `
                <div class="results-section">
                    <h3 class="section-title success">✓ دامنه‌های آزاد</h3>
                    <div class="domains-grid">
            `;

            available.forEach(result => {
                html += this.createDomainCard(result.data, 'available');
            });

            html += `
                    </div>
                </div>
            `;
        }

        // Show registered domains
        if (registered.length > 0) {
            html += `
                <div class="results-section">
                    <h3 class="section-title warning">⊗ دامنه‌های ثبت‌شده</h3>
                    <div class="domains-grid">
            `;

            registered.forEach(result => {
                html += this.createDomainCard(result.data, 'registered');
            });

            html += `
                    </div>
                </div>
            `;
        }

        // Show errors
        if (errors.length > 0) {
            html += `
                <div class="results-section">
                    <h3 class="section-title error">⚠ خطاها</h3>
                    <ul class="error-list">
            `;

            errors.forEach(error => {
                html += `<li class="error-item">${error.domain}: ${error.message}</li>`;
            });

            html += `
                    </ul>
                </div>
            `;
        }

        this.resultsContainer.innerHTML = html;

        if (available.length > 0) {
            window.toast.show(`${available.length} دامنه آزاد یافت شد!`, 'success');
        } else if (registered.length > 0) {
            window.toast.show('متأسفانه دامنه‌های انتخابی قبلاً ثبت شده‌اند', 'warning');
        }
    }

    createDomainCard(data, status) {
        const domainName = data.domain;
        const price = data.price_toman ? formatPrice(data.price_toman) : 'تماس بگیرید';
        
        let actions = '';
        if (status === 'available') {
            actions = `
                <div class="card-actions">
                    <button class="btn btn-success" onclick="orderDomain('${domainName}')">
                        ثبت سفارش
                    </button>
                </div>
            `;
        } else if (status === 'registered' && data.whois) {
            actions = `
                <div class="card-actions">
                    <button class="btn btn-secondary" onclick='showWhois(${JSON.stringify(data)})'>
                        مشاهده Whois
                    </button>
                </div>
            `;
        }

        return `
            <div class="domain-card ${status}">
                <div class="card-header">
                    <h4 class="domain-name">${domainName}</h4>
                    <span class="status-badge ${status}">${status === 'available' ? 'آزاد' : 'ثبت‌شده'}</span>
                </div>
                <div class="card-body">
                    <p class="domain-price">${price} تومان</p>
                    ${data.message ? `<p class="domain-message">${data.message}</p>` : ''}
                </div>
                ${actions}
            </div>
        `;
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new AdvancedDomainSearch();
});

// Global functions for onclick handlers
window.orderDomain = function(domain) {
    window.toast.show('در حال انتقال به فرم ثبت سفارش...', 'info');
    setTimeout(() => {
        // Phase 1: Redirect to Telegram
        const telegramId = 'your_telegram_id'; // Replace with actual ID
        window.open(`https://t.me/${telegramId}?text=سلام، می‌خواهم دامنه ${domain} را ثبت کنم`, '_blank');
    }, 1000);
};

window.showWhois = function(data) {
    const whoisData = data.whois;
    if (!whoisData) {
        window.toast.show('اطلاعات Whois موجود نیست', 'warning');
        return;
    }

    const content = `
        <div class="whois-details">
            <div class="whois-row">
                <strong>دامنه:</strong>
                <span>${data.domain}</span>
            </div>
            ${whoisData.registrar ? `
                <div class="whois-row">
                    <strong>رجیسترار:</strong>
                    <span>${whoisData.registrar}</span>
                </div>
            ` : ''}
            ${whoisData.registration_date ? `
                <div class="whois-row">
                    <strong>تاریخ ثبت:</strong>
                    <span>${whoisData.registration_date}</span>
                </div>
            ` : ''}
            ${whoisData.expiry_date ? `
                <div class="whois-row">
                    <strong>تاریخ انقضا:</strong>
                    <span>${whoisData.expiry_date}</span>
                </div>
            ` : ''}
            <div class="whois-row">
                <strong>وضعیت مالک:</strong>
                <span>${whoisData.owner_hidden ? 'مخفی شده برای حریم خصوصی' : 'قابل مشاهده'}</span>
            </div>
        </div>
    `;

    window.modal.open('اطلاعات Whois', content, `
        <button class="btn btn-primary" onclick="modal.close()">بستن</button>
    `);
};
