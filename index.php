<?php
/**
 * Index Page
 * Includes core functions for session management
 */
require_once __DIR__ . '/settings/core.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedLink</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header and Navbar -->
    <header>
        <nav class="navbar">
            <div class="brand">
                <img src="/assets/logo.png" alt="MedLink Logo" class="logo">
                <span>MedLink</span>
            </div>
            <ul class="nav-links">
                <li><a href="#medicines">Medicines</a></li>
                <li><a href="#hospitals">Hospitals</a></li>
                <li><a href="#pharmacies">Pharmacies</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#help">Help</a></li>
            </ul>
            <div class="nav-actions">
                <?php
                // Use core function to check if user is logged in
                $isLoggedIn = is_logged_in();
                $userType = get_user_type();
                $userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
                
                if (!$isLoggedIn):
                ?>
                    <a href="view/login.php" class="btn secondary" style="text-decoration: none; display: inline-block;">Login</a>
                    <button class="btn primary get-started" onclick="window.location.href='view/register.php'">Register</button>
                <?php else: ?>
                    <span style="color: rgba(255,255,255,0.9); margin-right: 12px; font-size: 0.9rem;">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                    <?php
                    // Show dashboard link based on user type
                    if ($userType === 'patient') {
                        echo '<a href="view/patients.php" class="btn secondary" style="text-decoration: none; display: inline-block; margin-right: 8px;">Dashboard</a>';
                    } elseif ($userType === 'hospital') {
                        echo '<a href="view/hospital.php" class="btn secondary" style="text-decoration: none; display: inline-block; margin-right: 8px;">Dashboard</a>';
                    } elseif ($userType === 'pharmacy') {
                        echo '<a href="view/pharmacy.php" class="btn secondary" style="text-decoration: none; display: inline-block; margin-right: 8px;">Dashboard</a>';
                    } elseif ($userType === 'admin') {
                        echo '<a href="view/admin.php" class="btn secondary" style="text-decoration: none; display: inline-block; margin-right: 8px;">Dashboard</a>';
                    }
                    ?>
                    <a href="actions/logout_action.php" class="btn secondary" style="text-decoration: none; display: inline-block; background: rgba(220, 38, 38, 0.1); border-color: rgba(220, 38, 38, 0.3); color: #FCA5A5;">Logout</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <button class="badge">100% Verified Medicine</button>
                <h1>Safe, Affordable Access to <span class="highlight">Genuine Medicines</span></h1>
                <p>Connect with verified hospitals and licensed pharmacies. Combat counterfeit drugs and ensure prescription compliance across Africa.</p>
                <div class="hero-buttons">
                    <a href="#find-medicine" class="btn primary">Find Medicine</a>
                    <a href="#upload-prescription" class="btn secondary">Upload Prescription</a>
                </div>
                <div class="features-row">
                    <div class="feature"><span class="feature-icon"></span>Licensed Pharmacies</div>
                    <div class="feature"><span class="feature-icon"></span>Anti-Counterfeit</div>
                    <div class="feature"><span class="feature-icon"></span>Fast Delivery</div>
                </div>
            </div>
            <div class="hero-image">
                <img src="/assets/hero-image.jpg" alt="Doctor typing, stethoscope">
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stat">
            <div class="icon"></div>
            <h2>100%</h2>
            <span>Verified Products</span>
        </div>
        <div class="stat">
            <div class="icon"></div>
            <h2>500+</h2>
            <span>Licensed Pharmacies</span>
        </div>
        <div class="stat">
            <div class="icon"></div>
            <h2>50K+</h2>
            <span>Happy Customers</span>
        </div>
        <div class="stat">
            <div class="icon"></div>
            <h2>24/7</h2>
            <span>Customer Support</span>
        </div>
    </section>

    <!-- Find Medicine Section -->
    <section class="find-medicine" id="medicines">
        <h2>Find Your Medicine</h2>
        <p>Browse our extensive catalog of verified, genuine medicines from licensed pharmacies</p>
        <div class="search-sort-filter">
            <input type="text" placeholder="Search medicines...">
            <select><option>All Categories</option></select>
            <button class="icon filter"></button>
        </div>
        <div class="medicine-cards-row">
            <!-- Repeat this card structure for multiple products; product data will be injected later by MVC controller -->
            <div class="medicine-card">
                <img src="/assets/product.jpg" alt="Medicine Product" class="product-image">
                <span class="verified-badge">Verified</span>
                <h3>Paracetamol 500mg</h3>
                <p class="sub">Pain Relief</p>
                <div class="rating">★ 4.8 (234)</div>
                <div class="price">GHS 12.50</div>
                <a href="#add-to-cart" class="btn add-cart">Add to Cart</a>
            </div>
            <!-- More static product cards ... -->
        </div>
    </section>

    <!-- Hospitals Section -->
    <section class="hospitals" id="hospitals">
        <h2>Reputable Hospitals in Ghana</h2>
        <div class="hospital-cards-row">
            <div class="hospital-card">
                <h3>Korle Bu Teaching Hospital</h3>
                <p class="location">Accra</p>
                <p>Ghana's largest hospital, established in 1923, and premier teaching facility. Specialist in cardiology, neurosurgery, oncology, and pediatric care.</p>
            </div>
            <div class="hospital-card">
                <h3>Komfo Anokye Teaching Hospital</h3>
                <p class="location">Kumasi</p>
                <p>Main referral for northern Ghana, renowned for trauma, emergency, maternal/child health, neurology, and orthopedics.</p>
            </div>
            <div class="hospital-card">
                <h3>Nyaho Medical Centre</h3>
                <p class="location">Accra</p>
                <p>Private hospital offering high-standard care in internal medicine, cardiology, endocrinology, and travel medicine.</p>
            </div>
            <div class="hospital-card">
                <h3>Lister Hospital & Fertility Centre</h3>
                <p class="location">Accra</p>
                <p>Ultra-modern healthcare facility offering specialties such as physiotherapy, pediatric hematology, and internal medicine.</p>
            </div>
            <div class="hospital-card">
                <h3>Ho Teaching Hospital</h3>
                <p class="location">Ho, Volta Region</p>
                <p>Quality healthcare provider in the eastern corridor, specializing in surgery, ENT, ophthalmology, and general medicine.</p>
            </div>
        </div>
    </section>
    <!-- Pharmacies Section -->
    <section class="pharmacies" id="pharmacies">
        <h2>Reputable Pharmacies in Ghana</h2>
        <div class="pharmacy-cards-row">
            <div class="pharmacy-card">
                <h3>Ernest Chemists Limited</h3>
                <p class="location">Accra (Head Office)</p>
                <p>One of Ghana's top pharmaceutical companies, with manufacturing, distribution, and retailing arms. Since 1986.</p>
            </div>
            <div class="pharmacy-card">
                <h3>Kama Health Services</h3>
                <p class="location">Accra</p>
                <p>Wide pharmacy chain across Ghana providing medications and health products.</p>
            </div>
            <div class="pharmacy-card">
                <h3>AddPharma Limited</h3>
                <p class="location">Accra</p>
                <p>Retail pharmacy chain with broad prescription and wellness inventory.</p>
            </div>
            <div class="pharmacy-card">
                <h3>Cecil Pharmacy</h3>
                <p class="location">Accra</p>
                <p>Pharmaceutical services, medication dispensing, and health consultations.</p>
            </div>
            <div class="pharmacy-card">
                <h3>Mek Pharmacy</h3>
                <p class="location">Accra</p>
                <p>Diverse pharmaceutical products and a focus on quality customer care.</p>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <h2>How It Works</h2>
        <div class="steps-row">
            <div class="step">
                <div class="icon search"></div>
                <h3>Search Medicine</h3>
                <p>Browse our verified catalog or search for specific medicines you need</p>
            </div>
            <div class="step">
                <div class="icon upload"></div>
                <h3>Upload Prescription</h3>
                <p>Securely upload your prescription for verification by licensed pharmacists</p>
            </div>
            <div class="step">
                <div class="icon payment"></div>
                <h3>Choose Payment</h3>
                <p>Pay via MTN MoMo, Telecel Cash, ATMoney, or cash-on-delivery</p>
            </div>
            <div class="step">
                <div class="icon delivery"></div>
                <h3>Receive Order</h3>
                <p>Get your genuine medicines delivered safely to your doorstep</p>
            </div>
        </div>
    </section>

    <!-- Prescription Verification Section -->
    <section class="prescription-verification">
        <div class="verification-info">
            <h3>Prescription Verification</h3>
            <ul>
                <li>Secure Upload: Your prescription is encrypted and stored securely</li>
                <li>Fast Verification: Licensed pharmacists verify within 30 minutes</li>
                <li>Compliance Assured: We ensure proper prescription requirements</li>
            </ul>
        </div>
        <div class="upload-drop">
            <div class="dropbox">
                <span>Upload Prescription</span>
                <span>Supported formats: JPG, PNG, PDF (Max 5MB)</span>
                <div class="drop-area">Drop your file here or <a href="#">browse</a></div>
                <button class="select-file-btn">Or Select File</button>
            </div>
        </div>
    </section>

    <!-- Payment Options Section -->
    <section class="payment-options">
        <h2>Flexible Payment Options</h2>
        <div class="payment-methods-row">
            <div class="method popular">
                <span class="icon momo"></span>MTN MoMo
            </div>
            <div class="method popular">
                <span class="icon telecel"></span>Telecel Cash
            </div>
            <div class="method">
                <span class="icon atmoney"></span>ATMoney
            </div>
            <div class="method">
                <span class="icon cash"></span>Cash on Delivery
            </div>
        </div>
        <div class="payment-security-info">All transactions are encrypted with bank-level security. Your payment information is never stored on our servers.</div>
    </section>

    <!-- Mission & Impact Section -->
    <section class="mission-impact">
        <div class="mission-row">
            <div class="mission-tile"><h3>100% Verified</h3><span>Fighting Counterfeit Medicines</span><p>We work with verified suppliers to ensure every medicine is genuine and safe</p></div>
            <div class="mission-tile"><h3>24/7 Verification</h3><span>Prescription Compliance</span><p>Licensed pharmacists verify all prescriptions to ensure proper medication use</p></div>
            <div class="mission-tile"><h3>50K+ Patients Served</h3><span>Public Health Impact</span><p>Making healthcare accessible and affordable for urban and rural communities</p></div>
            <div class="mission-tile"><h3>5+ Countries</h3><span>Expanding Across Africa</span><p>Building a sustainable healthcare ecosystem for the entire continent</p></div>
        </div>
        <blockquote>"Every person deserves access to genuine medicines. We're building a future where counterfeit drugs are a thing of the past."<br><span class="team-sign">— MedLink Team</span></blockquote>
    </section>

    <!-- Help Center Section -->
    <section class="help-center" id="help">
        <h2>Help Center</h2>
        <div class="faq-support">
            <div class="faqs">
                <h3>Frequently Asked Questions</h3>
                <ul class="faq-list">
                    <li class="faq-item">
                        <div class="faq-question">
                            <span>How do I know the medicines are genuine and verified?</span>
                            <span class="faq-arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>All medicines on MedLink are sourced exclusively from licensed pharmacies and verified suppliers. Each product undergoes strict quality checks and comes with authentication codes. We work directly with pharmaceutical manufacturers and authorized distributors to ensure 100% genuine products. Every medicine is verified by licensed pharmacists before being listed, and you can verify authenticity using the unique product codes provided with your order.</p>
                        </div>
                    </li>
                    <li class="faq-item">
                        <div class="faq-question">
                            <span>How do I order prescription medicines?</span>
                            <span class="faq-arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>To order prescription medicines, simply upload a clear photo or scan of your valid prescription from a licensed healthcare provider. Our licensed pharmacists will verify your prescription within 30 minutes during business hours. Once verified, you can proceed to checkout. Prescriptions must be recent (within 6 months) and include your name, the doctor's signature, medication name, dosage, and quantity. We comply with all Ghanaian pharmaceutical regulations.</p>
                        </div>
                    </li>
                    <li class="faq-item">
                        <div class="faq-question">
                            <span>What are your delivery times and areas covered?</span>
                            <span class="faq-arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>We offer fast and reliable delivery across Ghana. In Accra and major cities, standard delivery takes 24-48 hours. For other regions, delivery takes 3-5 business days. Express delivery (same-day) is available in Accra for orders placed before 2 PM. Delivery fees vary by location and order size. We partner with trusted courier services to ensure your medicines arrive safely and on time. You'll receive real-time tracking updates via SMS and email.</p>
                        </div>
                    </li>
                    <li class="faq-item">
                        <div class="faq-question">
                            <span>What payment methods are available?</span>
                            <span class="faq-arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>We accept multiple secure payment options: Mobile Money (MTN MoMo, Telecel Cash, ATMoney), Credit/Debit Cards (Visa, Mastercard), Bank Transfer, and Cash on Delivery. All online payments are processed through encrypted, PCI-compliant payment gateways. Your payment information is never stored on our servers. Cash on delivery is available for orders above GHS 50 in major cities. Payment must be completed before prescription medicines are dispensed.</p>
                        </div>
                    </li>
                    <li class="faq-item">
                        <div class="faq-question">
                            <span>Can I return or exchange medicines?</span>
                            <span class="faq-arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Due to health and safety regulations, we cannot accept returns of opened or used medicines. However, if you receive the wrong product, damaged packaging, or expired medication, we'll provide a full refund or replacement within 7 days of delivery. Unopened, unexpired medicines in original packaging can be returned within 48 hours for a refund (excluding prescription medicines). Contact our support team immediately if you encounter any issues with your order.</p>
                        </div>
                    </li>
                    <li class="faq-item">
                        <div class="faq-question">
                            <span>How is my personal and medical information protected?</span>
                            <span class="faq-arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Your privacy and security are our top priorities. All data is encrypted using bank-level SSL encryption. We comply with Ghana's Data Protection Act and never share your medical information with third parties. Prescriptions are stored securely and only accessible to licensed pharmacists for verification purposes. We use secure servers and regular security audits. You can request to view, update, or delete your data at any time through your account settings or by contacting our privacy team.</p>
                        </div>
                    </li>
                    <li class="faq-item">
                        <div class="faq-question">
                            <span>What if I have questions about my medication or need medical advice?</span>
                            <span class="faq-arrow">▼</span>
                        </div>
                        <div class="faq-answer">
                            <p>Our licensed pharmacists are available 24/7 via live chat, phone, or email to answer questions about medications, dosages, interactions, and side effects. However, we cannot provide medical diagnoses or replace consultations with your doctor. For medical emergencies, please contact emergency services (193) or visit the nearest hospital. We provide medication information leaflets with every order and can connect you with healthcare professionals for consultations if needed.</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="support-contact">
                <h3>Contact Support</h3>
                <div>Call Us: <a href="tel:+233241234567">+233 24 123 4567</a></div>
                <div>Live Chat: <a href="#">Chat Now</a> <span class="small">(Average response: 2 min)</span></div>
                <div>Email: <a href="mailto:support@medlink.com">support@medlink.com</a> <span class="small">(24hr response)</span></div>
            </div>
        </div>
        <div class="immediate-help">
            <div>Need Immediate Help?</div>
            <button class="btn primary">Start Live Chat</button>
            <button class="btn">Call Emergency Line</button>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="subscribe-row">
            <div class="subscribe-title">Stay Updated</div>
            <form class="subscribe-form">
                <input type="email" placeholder="Enter your email">
                <button class="btn">Subscribe</button>
            </form>
        </div>
        <div class="footer-links-row">
            <div class="footer-brand">
                <img src="/assets/logo.png" alt="MedLink Logo footer" class="logo">
                <span>MedLink</span>
                <p>Connecting patients with verified hospitals and licensed pharmacies for safe, affordable access to genuine medicines across Africa.</p>

                <div class="social-icons">
                    <a href="#" class="icon facebook"></a>
                    <a href="#" class="icon twitter"></a>
                    <a href="#" class="icon instagram"></a>
                    <a href="#" class="icon linkedin"></a>
                </div>
            </div>
            <div class="footer-columns">
                <div class="footer-col">
                    <strong>Products</strong>
                    <ul>
                        <li><a href="#">Browse Medicines</a></li>
                        <li><a href="#">Hospitals</a></li>
                        <li><a href="#">Pharmacies</a></li>
                        <li><a href="#">Health Products</a></li>
                        <li><a href="#">Prescription Upload</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <strong>Company</strong>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Our Mission</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press Kit</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <strong>Support</strong>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Order Tracking</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <strong>Legal</strong>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Compliance</a></li>
                        <li><a href="#">Security</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright-row">
            <span>© 2025 MedLink. All rights reserved. Fighting counterfeit medicine across Africa.</span>
            <span>Privacy Terms Cookies Accessibility</span>
        </div>
    </footer>

    <script>
        // FAQ Dropdown Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');
                const arrow = item.querySelector('.faq-arrow');
                
                // Initially hide all answers
                answer.style.maxHeight = '0';
                answer.style.opacity = '0';
                answer.style.overflow = 'hidden';
                
                question.addEventListener('click', function() {
                    const isOpen = item.classList.contains('active');
                    
                    // Close all other FAQ items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                            const otherAnswer = otherItem.querySelector('.faq-answer');
                            const otherArrow = otherItem.querySelector('.faq-arrow');
                            otherAnswer.style.maxHeight = '0';
                            otherAnswer.style.opacity = '0';
                            otherArrow.style.transform = 'rotate(0deg)';
                        }
                    });
                    
                    // Toggle current item
                    if (isOpen) {
                        item.classList.remove('active');
                        answer.style.maxHeight = '0';
                        answer.style.opacity = '0';
                        arrow.style.transform = 'rotate(0deg)';
                    } else {
                        item.classList.add('active');
                        answer.style.maxHeight = answer.scrollHeight + 'px';
                        answer.style.opacity = '1';
                        arrow.style.transform = 'rotate(180deg)';
                    }
                });
            });
        });
    </script>
</body>
</html>
