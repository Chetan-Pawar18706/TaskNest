<?php
/**
 * TaskNest - Landing Page
 */

require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if ($auth->isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$page_title = 'Welcome to TaskNest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>

    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/reset.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/landing.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
</head>
<body class="landing-page">

    <!-- ── Navigation ──────────────────────────── -->
    <nav class="landing-nav" id="landingNav">
        <a href="<?php echo SITE_URL; ?>/" class="nav-brand">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo-dark.png" alt="<?php echo SITE_NAME; ?>">
            <?php echo SITE_NAME; ?>
        </a>
        <ul class="nav-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#how-it-works">How It Works</a></li>
            <li><a href="#reviews">Reviews</a></li>
        </ul>
        <div class="nav-actions">
            <a href="<?php echo SITE_URL; ?>/login.php" class="btn-hero-secondary nav-btn-sm">Sign In</a>
            <a href="<?php echo SITE_URL; ?>/register.php" class="btn-hero-primary nav-btn-sm">Get Started</a>
        </div>
        <button class="landing-menu-toggle" id="landingMenuToggle" aria-label="Toggle menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </nav>

    <!-- ── Hero Section ────────────────────────── -->
    <section class="hero">
        <div class="hero-particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                Now in Version 1.0
            </div>

            <div class="hero-logo">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo">
            </div>

            <h1 class="hero-title">
                One Place.<br>
                <span class="gradient-text">Everything Organized.</span>
            </h1>

            <p class="hero-subtitle">
                Manage tasks, notes, expenses, habits, goals, and documents in one beautiful workspace. 
                Take control of your life with TaskNest.
            </p>

            <div class="hero-actions">
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn-hero-primary">
                    Start Free
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </a>
                <a href="#features" class="btn-hero-secondary">
                    See Features
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6"></path>
                    </svg>
                </a>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-value">8+</div>
                    <div class="hero-stat-label">Modules</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value">100%</div>
                    <div class="hero-stat-label">Free & Open</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value">24/7</div>
                    <div class="hero-stat-label">Your Data</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Features Section ────────────────────── -->
    <section class="features-section" id="features">
        <div class="section-header">
            <span class="section-badge">Features</span>
            <h2 class="section-title">Everything You Need</h2>
            <p class="section-subtitle">Eight powerful modules designed to help you manage every aspect of your life.</p>
        </div>

        <div class="features-grid">
            <!-- Tasks -->
            <div class="feature-card tasks">
                <div class="feature-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>
                <h3>Task Management</h3>
                <p>Create, organize, and track tasks with priorities, categories, due dates, and reminders. Never miss a deadline.</p>
            </div>

            <!-- Notes -->
            <div class="feature-card notes">
                <div class="feature-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
                        <line x1="6" y1="8" x2="18" y2="8"></line>
                        <line x1="6" y1="12" x2="18" y2="12"></line>
                        <line x1="6" y1="16" x2="18" y2="16"></line>
                    </svg>
                </div>
                <h3>Smart Notes</h3>
                <p>Capture ideas with rich notes, categories, image attachments, and pin your most important ones.</p>
            </div>

            <!-- Expenses -->
            <div class="feature-card expenses">
                <div class="feature-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <h3>Expense Tracking</h3>
                <p>Track income and expenses, set budgets, categorize spending, and visualize your financial health.</p>
            </div>

            <!-- Documents -->
            <div class="feature-card documents">
                <div class="feature-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                </div>
                <h3>Document Vault</h3>
                <p>Store, categorize, and manage important documents with expiry tracking and reminders.</p>
            </div>

            <!-- Habits -->
            <div class="feature-card habits">
                <div class="feature-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h3>Habit Tracker</h3>
                <p>Build positive habits with daily tracking, streaks, and progress visualization.</p>
            </div>

            <!-- Goals -->
            <div class="feature-card goals">
                <div class="feature-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg>
                </div>
                <h3>Goal Setting</h3>
                <p>Set meaningful goals, track progress, and celebrate achievements with visual milestones.</p>
            </div>

            <!-- Shopping -->
            <div class="feature-card shopping">
                <div class="feature-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <h3>Shopping Lists</h3>
                <p>Create smart shopping lists with price estimates, quantities, and completion tracking.</p>
            </div>

            <!-- Borrow -->
            <div class="feature-card borrow">
                <div class="feature-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3>Borrow & Lend</h3>
                <p>Track items and money you've borrowed or lent, with return date reminders.</p>
            </div>
        </div>
    </section>

    <!-- ── How It Works ────────────────────────── -->
    <section class="how-it-works" id="how-it-works">
        <div class="section-header">
            <span class="section-badge">How It Works</span>
            <h2 class="section-title">Get Started in Minutes</h2>
            <p class="section-subtitle">Three simple steps to take control of your life.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Create Account</h3>
                <p>Sign up for free in seconds. No credit card required, no hidden fees.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Add Your Data</h3>
                <p>Start adding tasks, notes, expenses, and more. The intuitive interface makes it effortless.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Stay Organized</h3>
                <p>Track progress, hit deadlines, and achieve your goals with powerful dashboards.</p>
            </div>
        </div>
    </section>

    <!-- ── Social Proof ────────────────────────── -->
    <section class="social-proof" id="reviews">
        <div class="section-header">
            <span class="section-badge">Reviews</span>
            <h2 class="section-title">Loved by Organized People</h2>
            <p class="section-subtitle">See what our users have to say about TaskNest.</p>
        </div>

        <div class="proof-grid">
            <div class="proof-card">
                <div class="proof-stars">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <p class="proof-text">"TaskNest replaced 5 different apps for me. Tasks, notes, expenses, habits - everything in one place. The dashboard is beautiful."</p>
                <div class="proof-author">
                    <div class="proof-avatar">S</div>
                    <div>
                        <div class="proof-name">Sarah K.</div>
                        <div class="proof-role">Product Manager</div>
                    </div>
                </div>
            </div>

            <div class="proof-card">
                <div class="proof-stars">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <p class="proof-text">"The habit tracker and goal system helped me build a consistent morning routine. I've never been more productive."</p>
                <div class="proof-author">
                    <div class="proof-avatar">M</div>
                    <div>
                        <div class="proof-name">Marcus T.</div>
                        <div class="proof-role">Freelance Designer</div>
                    </div>
                </div>
            </div>

            <div class="proof-card">
                <div class="proof-stars">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <p class="proof-text">"Finally a life management tool that looks great and actually works. The expense tracker alone saved me hundreds."</p>
                <div class="proof-author">
                    <div class="proof-avatar">J</div>
                    <div>
                        <div class="proof-name">Jamie L.</div>
                        <div class="proof-role">Student</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Final CTA ───────────────────────────── -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Get Organized?</h2>
            <p class="cta-subtitle">Join thousands of people who use TaskNest to manage their lives. It's free, it's beautiful, it's yours.</p>
            <div class="hero-actions">
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn-hero-primary">
                    Create Free Account
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ── Footer ──────────────────────────────── -->
    <footer class="landing-footer">
        <div class="footer-content">
            <a href="<?php echo SITE_URL; ?>/" class="footer-brand">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo-dark.png" alt="<?php echo SITE_NAME; ?>">
                <?php echo SITE_NAME; ?>
            </a>
            <ul class="footer-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="<?php echo SITE_URL; ?>/login.php">Sign In</a></li>
                <li><a href="<?php echo SITE_URL; ?>/register.php">Sign Up</a></li>
            </ul>
            <div class="footer-divider"></div>
            <p class="footer-copy">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script>
    // Mobile menu toggle
    const menuToggle = document.getElementById('landingMenuToggle');
    const landingNav = document.getElementById('landingNav');
    if (menuToggle && landingNav) {
        menuToggle.addEventListener('click', () => {
            landingNav.classList.toggle('mobile-open');
        });
        // Close menu on link click
        landingNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => landingNav.classList.remove('mobile-open'));
        });
    }

    // Navbar scroll effect
    const nav = document.getElementById('landingNav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 50);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Animate elements on scroll
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.feature-card, .step-card, .proof-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
    </script>
</body>
</html>
