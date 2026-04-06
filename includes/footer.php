<?php
// Public website footer
?>
    </main><!-- /main content -->

    <footer class="footer">
        <div class="footer-container">
            
            <!-- Footer Top -->
            <div class="footer-top">
                <div class="footer-section">
                    <h4>About <?= e(SITE_NAME) ?></h4>
                    <p><?= e(SITE_DESCRIPTION) ?></p>
                    <div class="social-links">
                        <a href="#" class="social-link" title="Facebook" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" title="Twitter" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" title="Instagram" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" title="LinkedIn" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>/products.php">Products</a></li>
                        <li><a href="<?= SITE_URL ?>/properties.php">Properties</a></li>
                        <li><a href="<?= SITE_URL ?>/vehicles.php">Vehicles</a></li>
                        <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Track Order</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Disclaimer</a></li>
                    </ul>
                </div>

                <div class="footer-section newsletter">
                    <h4>Newsletter</h4>
                    <p>Subscribe to get special offers and updates</p>
                    <form class="newsletter-form" action="<?= SITE_URL ?>/api/subscribe.php" method="POST">
                        <input type="email" name="email" placeholder="Your email" required>
                        <button type="submit" class="btn-subscribe">Subscribe</button>
                    </form>
                </div>

            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
                </div>
                <div class="footer-payment">
                    <span>We Accept:</span>
                    <i class="fas fa-credit-card"></i>
                </div>
            </div>

        </div>
    </footer>

    <style>
        /* ===== FOOTER ===== */
        .footer {
            background: #1a1a2e;
            color: #e5e7eb;
            margin-top: 40px;
            padding: 40px 0 20px;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .footer-top {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 32px;
            margin-bottom: 32px;
        }

        .footer-section h4 {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-section p {
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 14px;
            color: #9ca3af;
        }

        /* Social Links */
        .social-links {
            display: flex;
            gap: 10px;
            margin-top: 14px;
        }

        .social-link {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,.1);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all .2s;
            font-size: 14px;
        }

        .social-link:hover {
            background: #f97316;
            transform: translateY(-2px);
        }

        /* Footer Links */
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 8px;
        }

        .footer-links a {
            color: #9ca3af;
            text-decoration: none;
            font-size: 13px;
            transition: color .2s;
        }

        .footer-links a:hover {
            color: #f97316;
        }

        /* Newsletter */
        .newsletter {
            grid-column: 1 / -1;
        }

        @media (min-width: 768px) {
            .newsletter {
                grid-column: auto;
            }
        }

        .newsletter-form {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #374151;
            border-radius: 6px;
            background: rgba(255,255,255,.05);
            color: #fff;
            font-size: 13px;
        }

        .newsletter-form input::placeholder {
            color: #6b7280;
        }

        .newsletter-form input:focus {
            outline: none;
            border-color: #f97316;
            background: rgba(255,255,255,.1);
        }

        .btn-subscribe {
            padding: 10px 18px;
            background: #f97316;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: background .2s;
            white-space: nowrap;
        }

        .btn-subscribe:hover {
            background: #ea580c;
        }

        /* Footer Bottom */
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,.1);
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-copyright {
            font-size: 12px;
            color: #6b7280;
        }

        .footer-copyright p {
            margin: 0;
        }

        .footer-payment {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #6b7280;
        }

        .footer-payment i {
            color: #9ca3af;
            font-size: 16px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .footer {
                margin-top: 30px;
                padding: 24px 0 16px;
            }

            .footer-container {
                padding: 0 12px;
            }

            .footer-top {
                gap: 24px;
                margin-bottom: 24px;
                grid-template-columns: 1fr;
            }

            .footer-section h4 {
                font-size: 14px;
                margin-bottom: 12px;
            }

            .footer-section p {
                font-size: 12px;
                margin-bottom: 12px;
            }

            .social-links {
                gap: 8px;
            }

            .social-link {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }

            .footer-links li {
                margin-bottom: 6px;
            }

            .footer-links a {
                font-size: 12px;
            }

            .newsletter {
                grid-column: 1;
            }

            .newsletter-form {
                flex-direction: column;
                gap: 6px;
            }

            .newsletter-form input {
                padding: 9px 10px;
                font-size: 12px;
            }

            .btn-subscribe {
                padding: 9px 16px;
                font-size: 12px;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 12px;
                padding-top: 12px;
            }

            .footer-copyright {
                font-size: 11px;
            }

            .footer-payment {
                font-size: 11px;
                justify-content: center;
            }
        }

        @media (min-width: 481px) and (max-width: 767px) {
            .footer {
                margin-top: 35px;
                padding: 32px 0 18px;
            }

            .footer-top {
                gap: 28px;
                margin-bottom: 28px;
                grid-template-columns: repeat(2, 1fr);
            }

            .newsletter {
                grid-column: 1 / -1;
            }

            .footer-section h4 {
                font-size: 14px;
                margin-bottom: 12px;
            }

            .footer-section p {
                font-size: 12px;
                margin-bottom: 12px;
            }

            .footer-links a {
                font-size: 12px;
            }

            .newsletter-form {
                flex-direction: column;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
        }
    </style>

</body>
</html>
