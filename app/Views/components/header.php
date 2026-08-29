<header class="main-header">
    <nav class="navbar">
        <div class="container">
            <a href="/" class="logo">
                <span class="logo-icon">🔍</span>
                <span class="logo-text">DomainHub</span>
            </a>
            <ul class="nav-links">
                <li><a href="/search">جستجوی دامنه</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="/dashboard">داشبورد</a></li>
                    <li><a href="/orders">سفارشات</a></li>
                    <li><a href="/logout">خروج</a></li>
                <?php else: ?>
                    <li><a href="/login">ورود</a></li>
                    <li><a href="/register" class="btn-register">ثبت‌نام</a></li>
                <?php endif; ?>
            </ul>
            <button class="mobile-menu-btn" aria-label="منو">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>
</header>
