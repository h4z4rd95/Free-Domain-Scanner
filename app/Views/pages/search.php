<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'جستجوی دامنه' ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="search-page">
    <?php include BASE_PATH . '/app/Views/components/header.php'; ?>

    <main class="search-main">
        <div class="container">
            <div class="search-section">
                <h1 class="page-title">جستجوی دامنه</h1>
                <p class="page-description">دامنه مورد نظر خود را جستجو کنید و از آزاد بودن آن مطمئن شوید</p>

                <div class="search-container">
                    <form id="domainSearchForm" class="advanced-search-form">
                        <div class="search-input-wrapper">
                            <input type="text" 
                                   id="domainInput" 
                                   class="search-input-large" 
                                   placeholder="نام دامنه (بدون پسوند)..."
                                   autocomplete="off"
                                   required>
                            <button type="submit" class="search-btn-large">
                                <span>بررسی وضعیت</span>
                            </button>
                        </div>

                        <div class="extensions-filter">
                            <div class="filter-header">
                                <span class="filter-label">پسوندهای مورد نظر:</span>
                                <button type="button" class="select-all-btn" data-action="selectAll">انتخاب همه</button>
                            </div>
                            <div class="categories-tabs">
                                <button type="button" class="tab-btn active" data-category="all">همه</button>
                                <button type="button" class="tab-btn" data-category="commercial">تجاری</button>
                                <button type="button" class="tab-btn" data-category="tech">تکنولوژی</button>
                                <button type="button" class="tab-btn" data-category="country">کشوری</button>
                                <button type="button" class="tab-btn" data-category="general">عمومی</button>
                            </div>
                            <div class="extensions-grid" id="extensionsGrid">
                                <?php foreach ($extensions as $ext): ?>
                                    <label class="extension-checkbox">
                                        <input type="checkbox" 
                                               name="extensions[]" 
                                               value="<?= sanitize($ext['extension']) ?>"
                                               data-category="<?= sanitize($ext['category']) ?>"
                                               <?= in_array($ext['extension'], ['com', 'ir']) ? 'checked' : '' ?>>
                                        <span class="ext-badge">.<?= sanitize($ext['extension']) ?></span>
                                        <span class="ext-price"><?= number_format(usdToToman($ext['price_usd'])) ?> تومان</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </form>

                    <div id="searchResults" class="search-results"></div>
                </div>

                <?php if (!empty($guestHistory)): ?>
                <div class="search-history">
                    <h3>تاریخچه جستجوهای شما</h3>
                    <div class="history-list">
                        <?php foreach (array_slice($guestHistory, 0, 10) as $item): ?>
                            <div class="history-item">
                                <span class="history-domain"><?= sanitize($item['domain']) ?></span>
                                <span class="history-status status-<?= sanitize($item['status']) ?>">
                                    <?= sanitize($item['status'] === 'available' ? 'آزاد' : 'ثبت‌شده') ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Whois Modal -->
    <div id="whoisModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>اطلاعات Whois</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="whoisContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Order Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>ثبت سفارش دامنه</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="orderForm">
                    <!-- Order form will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <script>
        const CSRF_TOKEN = '<?= generateCsrfToken() ?>';
        const extensions = <?= json_encode($extensions) ?>;
    </script>
    <script src="<?= asset('js/search.js') ?>"></script>
</body>
</html>
