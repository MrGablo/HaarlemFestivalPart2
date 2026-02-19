<style>
    .main-header {
        background-color: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 15px 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .header-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-logo img {
        height: 40px; /* Adjust based on your actual logo size */
        display: block;
    }

    .main-nav {
        display: flex;
        align-items: center;
        gap: 15px; /* Spacing between links */
    }

    .nav-link {
        text-decoration: none;
        color: #000;
        font-weight: 700;
        font-size: 1rem;
        padding: 10px 18px;
        transition: all 0.2s;
        border-radius: 25px; /* Pill shape for hover effects */
    }

    .nav-link:hover {
        background-color: #f5f5f5;
    }

    .nav-link.nav-active {
        background-color: #2F80ED; /* Bright Blue */
        color: white;
        box-shadow: 0 4px 10px rgba(47, 128, 237, 0.3);
    }

    .nav-link.nav-active:hover {
        background-color: #1c6ddb;
    }

    .cart-link {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cart-icon-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .cart-icon {
        width: 24px;
        height: 24px;
    }

    .cart-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #E63946;
        color: white;
        font-size: 0.7rem;
        font-weight: bold;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
    }
</style>

<header class="main-header">
    <div class="header-container">
        <a href="/" class="header-logo">
            <img src="/assets/img/homepage/logo.svg" alt="Haarlem Festival">
        </a>

        <nav class="main-nav">
            <a href="/" class="nav-link nav-active">Home</a>
            
            <a href="/dance" class="nav-link">Dance</a>
            <a href="/jazz" class="nav-link">Jazz</a>
            <a href="/yummy" class="nav-link">Yummy</a>
            <a href="/stories" class="nav-link">Stories</a>
            <a href="/history" class="nav-link">History</a>
            <?php if (!empty($isLoggedIn)): ?>
                <a class="topbar-link" href="/account/manage" title="Manage account" aria-label="Manage account">
                    <img
                        class="topbar-avatar"
                        src="<?php echo htmlspecialchars($profilePicturePath ?: '/assets/img/default-user.png'); ?>"
                        alt="Account">
                    <span>Account</span>
                </a>
            <?php else: ?>
                <a class="topbar-link" href="/login">Login</a>
            <?php endif; ?>
            
            <a href="/cart" class="nav-link cart-link">
                Program
                <div class="cart-icon-wrapper">
                    <img src="/assets/img/headerfooter/cart.svg" alt="Cart" class="cart-icon">
                    <span class="cart-badge">0</span>
                </div>
            </a>
        </nav>
    </div>
</header>