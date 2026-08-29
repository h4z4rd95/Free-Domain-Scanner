<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'DomainHub' ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="home-page">
    <header class="main-header">
        <nav class="navbar">
            <div class="container">
                <a href="/" class="logo">
                    <span class="logo-icon">🔍</span>
                    <span class="logo-text">DomainHub</span>
                </a>
                <ul class="nav-links">
                    <li><a href="/search">جستجوی دامنه</a></li>
                    <li><a href="/dashboard">داشبورد</a></li>
                    <li><a href="/login">ورود</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">جستجو و ثبت دامنه رویایی شما</h1>
                <p class="hero-subtitle">هویت آنلاین کسب‌وکار خود را با یک دامنه منحصر به فرد شروع کنید</p>
                
                <div class="search-box-wrapper">
                    <form id="quickSearchForm" class="search-form" action="/search" method="GET">
                        <div class="search-input-group">
                            <input type="text" 
                                   name="domain" 
                                   class="search-input" 
                                   placeholder="نام دامنه مورد نظر خود را وارد کنید..."
                                   autocomplete="off"
                                   required>
                            <select name="extension" class="extension-select">
                                <option value="com">.com</option>
                                <option value="net">.net</option>
                                <option value="org">.org</option>
                                <option value="ir">.ir</option>
                                <option value="io">.io</option>
                            </select>
                            <button type="submit" class="search-btn">
                                <span>جستجو</span>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>بررسی آنی</h3>
                        <p>وضعیت دامنه را در لحظه بررسی کنید</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>امن و مطمئن</h3>
                        <p>پرداخت امن با تأیید دستی</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">💎</div>
                        <h3>پیشنهادهای ویژه</h3>
                        <p>دامنه‌های جایگزین هوشمند</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <section class="extensions-section">
        <div class="container">
            <h2 class="section-title">پسوندهای محبوب</h2>
            <div class="extensions-grid">
                <div class="extension-card">
                    <span class="ext-name">.com</span>
                    <span class="ext-desc">تجاری</span>
                </div>
                <div class="extension-card">
                    <span class="ext-name">.ir</span>
                    <span class="ext-desc">ایران</span>
                </div>
                <div class="extension-card">
                    <span class="ext-name">.net</span>
                    <span class="ext-desc">شبکه</span>
                </div>
                <div class="extension-card">
                    <span class="ext-name">.org</span>
                    <span class="ext-desc">سازمان</span>
                </div>
                <div class="extension-card">
                    <span class="ext-name">.io</span>
                    <span class="ext-desc">تکنولوژی</span>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; 2024 DomainHub - تمامی حقوق محفوظ است.</p>
        </div>
    </footer>

    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
