<style>
    .main-footer {
        background-color: #fff;
        padding: 60px 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin-top: 50px;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 40px;
    }

    .footer-col {
        flex: 1;
        min-width: 200px;
    }

    /* Branding Column  */
    .footer-brand {
        display: flex;
        flex-direction: column;
        gap: 20px;
        max-width: 300px;
    }

    .footer-logo {
        width: 60px; 
    }

    .social-icons {
        display: flex;
        gap: 15px;
    }

    .social-icon {
        width: 24px;
        height: 24px;

        opacity: 0.6; 
        transition: opacity 0.2s;
    }

    .social-icon:hover {
        opacity: 1;
    }

    .copyright {
        font-size: 0.9rem;
        color: #666;
        margin-top: 30px;
    }

    /* Links Columns (Middle & Right) */
    .footer-col h3 {
        font-size: 1.2rem;
        font-weight: 800;
        margin-bottom: 25px;
        color: #000;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        text-decoration: none;
        color: #333;
        font-size: 1rem;
        transition: color 0.2s;
        font-weight: 400;
    }

    .footer-links a:hover {
        color: #2F80ED;
        text-decoration: underline;
    }
</style>

<footer class="main-footer">
    <div class="footer-container">
        
        <div class="footer-col footer-brand">
            <img src="/assets/img/homepage/logo.svg" alt="Haarlem Festival Logo" class="footer-logo">
            
            <div class="social-icons">
                <a href="#"><img src="/assets/img/headerfooter/facebook.svg" alt="Facebook" class="social-icon"></a>
                <a href="#"><img src="/assets/img/headerfooter/yt.svg" alt="YouTube" class="social-icon"></a>
                <a href="#"><img src="/assets/img/headerfooter/insta.svg" alt="Instagram" class="social-icon"></a>
            </div>

            <p class="copyright">@2025 Haarlem Festival</p>
        </div>

        <div class="footer-col">
            <h3>Support</h3>
            <ul class="footer-links">
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Tickets Refund</a></li>
                <li><a href="#">Terms & Conditions</a></li>
                <li><a href="#">Privacy Policy</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h3>Quick Menu</h3>
            <ul class="footer-links">
                <li><a href="#">Home</a></li>
                <li><a href="#">Dance!</a></li>
                <li><a href="#">Haarlem Jazz</a></li>
                <li><a href="#">Yummy!</a></li>
                <li><a href="#">Stories in Haarlem</a></li>
                <li><a href="#">A Stroll through History</a></li>
            </ul>
        </div>

    </div>
</footer>