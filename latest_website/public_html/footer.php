<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer</title>
    <style>
        footer {
            background-color: #f9f9f9;
            padding: 20px 50px;
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .footer-links div {
            text-align: left;
        }

        .footer-links h4 {
            margin-bottom: 10px;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links ul li a {
            text-decoration: none;
            color: #666;
            font-size: 14px;
        }

        .footer-links a {
            color: #007bff;
            text-decoration: none;
        }

        .copyright {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <footer>
        <div class="footer-links">
            <div>
                <h4>About FarmMarket</h4>
                <p>Connecting farmers and buyers in one place. We provide a trusted space for advertising farm animals, ensuring safe and responsible trading practices.</p>
                <a href="about.php">Contact Us</a>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="marketplace.php">List Animal</a></li>
                    <li><a href="#">Animal Guide</a></li>
                </ul>
            </div>
            <div>
                <h4>Legal</h4>
                <ul>
                    <li><a href="agreement.php">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Selling Guidelines</a></li>
                </ul>
            </div>
        </div>
        <p class="copyright">© 2025 FarmMarket. All rights reserved.</p>
    </footer>
</body>
</html>