<?php
/*
 * Topindex.php
 *
 * Enhanced landing page with comprehensive onboarding for new users
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BiblioBros – University Mentoring Platform</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <!-- AOS Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css" />
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 600px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .feature-card {
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            background: white;
            border-radius: 10px;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #ffc107;
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .feature-icon i {
            color: white;
            font-size: 1.75rem;
        }
        
        .how-it-works {
            background: #f8f9fa;
            padding: 5rem 0;
        }
        
        .step-card {
            text-align: center;
            padding: 2rem;
            position: relative;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: #ffc107;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .step-card::after {
            content: '';
            position: absolute;
            top: 25px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #ffc107;
            z-index: -1;
        }
        
        .step-card:last-child::after {
            display: none;
        }
        
        .role-section {
            padding: 5rem 0;
        }
        
        .role-card {
            padding: 2rem;
            border-radius: 15px;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .role-card.mentor {
            background: linear-gradient(135deg, #fff8e1 0%, #ffffff 100%);
            border: 2px solid #ffc107;
        }
        
        .role-card.mentee {
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
            border: 2px solid #17a2b8;
        }
        
        .role-card:hover {
            transform: scale(1.02);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .faq-section {
            background: white;
            padding: 5rem 0;
        }
        
        .faq-item {
            margin-bottom: 1rem;
        }
        
        .faq-button {
            width: 100%;
            text-align: left;
            background: white;
            border: 1px solid #e9ecef;
            padding: 1.25rem;
            font-weight: 500;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .faq-button:hover {
            background: #f8f9fa;
            border-color: #ffc107;
        }
        
        .faq-button:focus {
            box-shadow: none;
            border-color: #ffc107;
        }
        
        .faq-button::after {
            content: '+';
            float: right;
            font-size: 1.5rem;
            line-height: 1;
            color: #ffc107;
        }
        
        .faq-button[aria-expanded="true"]::after {
            content: '−';
        }
        
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 0;
            text-align: center;
        }
        
        .stats-section {
            padding: 3rem 0;
            background: white;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #ffc107;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .demo-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #ffc107;
            color: #333;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            text-decoration: none;
            font-weight: 500;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .demo-button:hover {
            background: #ffdb4d;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        @media (max-width: 768px) {
            .step-card::after {
                display: none;
            }
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Dynamic navbar container -->
    <div id="navbar-placeholder"></div>

    <!-- HERO SECTION -->
    <main class="hero-section text-white d-flex align-items-center justify-content-center">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-4">Connect. Learn. Succeed.</h1>
                    <p class="lead mb-4">
                        BiblioBros is the premier university mentoring platform that connects students 
                        who need help with experienced peers ready to guide them.
                    </p>
                    <p class="mb-5">
                        Join thousands of students already benefiting from peer-to-peer academic support.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#how-it-works" class="btn btn-warning btn-lg">
                            <i class="fas fa-play-circle me-2"></i>See How It Works
                        </a>
                        <a href="Topregister.php" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i>Get Started Free
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="assets/img/hero-illustration.svg" alt="Students collaborating" class="img-fluid" 
                         onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </main>

    <!-- STATS SECTION -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 stat-item" data-aos="zoom-in">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Active Mentors</div>
                </div>
                <div class="col-md-3 col-6 stat-item" data-aos="zoom-in" data-aos-delay="100">
                    <div class="stat-number">2,000+</div>
                    <div class="stat-label">Students Helped</div>
                </div>
                <div class="col-md-3 col-6 stat-item" data-aos="zoom-in" data-aos-delay="200">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Subjects Covered</div>
                </div>
                <div class="col-md-3 col-6 stat-item" data-aos="zoom-in" data-aos-delay="300">
                    <div class="stat-number">4.8/5</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS SECTION -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">
                <i class="fas fa-cogs me-2 text-warning"></i>
                How BiblioBros Works
            </h2>
            <div class="row">
                <div class="col-md-3 step-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-number">1</div>
                    <h5>Sign Up</h5>
                    <p>Register with your university email and select your university from our database.</p>
                </div>
                <div class="col-md-3 step-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-number">2</div>
                    <h5>Choose Your Role</h5>
                    <p>Select whether you want to be a mentor, mentee, or both for each subject.</p>
                </div>
                <div class="col-md-3 step-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-number">3</div>
                    <h5>Connect</h5>
                    <p>Post questions as a mentee or accept requests as a mentor to start chatting.</p>
                </div>
                <div class="col-md-3 step-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-number">4</div>
                    <h5>Learn & Grow</h5>
                    <p>Exchange knowledge, track progress, and rate your experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="container py-5">
        <h2 class="text-center mb-5" data-aos="fade-up">
            <i class="fas fa-star me-2 text-warning"></i>
            Platform Features
        </h2>
        <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h5 class="fw-bold text-center mb-3">Smart Matching</h5>
                    <p class="text-center">Our system connects you with the right mentors based on your subject needs and their expertise.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h5 class="fw-bold text-center mb-3">Real-Time Chat</h5>
                    <p class="text-center">Instant messaging system for seamless communication between mentors and mentees.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="fw-bold text-center mb-3">Safe Environment</h5>
                    <p class="text-center">University-verified users and moderated interactions ensure a safe learning space.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="fw-bold text-center mb-3">Progress Tracking</h5>
                    <p class="text-center">Monitor your learning journey with detailed statistics and history.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h5 class="fw-bold text-center mb-3">Rating System</h5>
                    <p class="text-center">Build your reputation through peer ratings and feedback.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h5 class="fw-bold text-center mb-3">Mobile Friendly</h5>
                    <p class="text-center">Access the platform anytime, anywhere from any device.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ROLES SECTION -->
    <section class="role-section bg-light">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">
                <i class="fas fa-users me-2 text-warning"></i>
                Choose Your Path
            </h2>
            <div class="row">
                <div class="col-md-6 mb-4" data-aos="fade-right">
                    <div class="role-card mentor">
                        <h3 class="text-warning mb-3">
                            <i class="fas fa-user-graduate me-2"></i>
                            Become a Mentor
                        </h3>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check text-warning me-2"></i>
                                Share your knowledge and experience
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-warning me-2"></i>
                                Build your professional portfolio
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-warning me-2"></i>
                                Earn recognition through ratings
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-warning me-2"></i>
                                Flexible schedule - help when you can
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-warning me-2"></i>
                                Strengthen your understanding by teaching
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 mb-4" data-aos="fade-left">
                    <div class="role-card mentee">
                        <h3 class="text-info mb-3">
                            <i class="fas fa-user me-2"></i>
                            Join as a Mentee
                        </h3>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check text-info me-2"></i>
                                Get help from experienced students
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-info me-2"></i>
                                Ask questions without judgment
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-info me-2"></i>
                                Access help 24/7 from anywhere
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-info me-2"></i>
                                Choose from multiple mentors
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-info me-2"></i>
                                Track your academic improvement
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <p class="text-center mt-4 text-muted" data-aos="fade-up">
                <i class="fas fa-info-circle me-2"></i>
                You can be both a mentor and mentee in different subjects!
            </p>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section class="faq-section">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">
                <i class="fas fa-question-circle me-2 text-warning"></i>
                Frequently Asked Questions
            </h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="faq-item" data-aos="fade-up">
                            <button class="faq-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq1" aria-expanded="false">
                                Is BiblioBros free to use?
                            </button>
                            <div id="faq1" class="collapse" data-bs-parent="#faqAccordion">
                                <div class="card-body">
                                    Yes! BiblioBros is completely free for all university students. 
                                    We believe academic support should be accessible to everyone.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-aos="fade-up" data-aos-delay="100">
                            <button class="faq-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq2" aria-expanded="false">
                                How do I know mentors are qualified?
                            </button>
                            <div id="faq2" class="collapse" data-bs-parent="#faqAccordion">
                                <div class="card-body">
                                    All mentors are verified university students who have demonstrated 
                                    proficiency in their subjects. Our rating system helps identify 
                                    the most helpful mentors based on peer feedback.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-aos="fade-up" data-aos-delay="200">
                            <button class="faq-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq3" aria-expanded="false">
                                Can I switch between being a mentor and mentee?
                            </button>
                            <div id="faq3" class="collapse" data-bs-parent="#faqAccordion">
                                <div class="card-body">
                                    Absolutely! You can be a mentor in subjects where you can help and 
                                    mentee in  that same subject if you have any question! Your role is not
                                    subject-specific!
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-aos="fade-up" data-aos-delay="300">
                            <button class="faq-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq4" aria-expanded="false">
                                How quickly can I expect responses?
                            </button>
                            <div id="faq4" class="collapse" data-bs-parent="#faqAccordion">
                                <div class="card-body">
                                    Response times vary, but most questions receive responses within 
                                    a few hours. Popular subjects often have multiple active mentors, 
                                    ensuring quicker help.
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-aos="fade-up" data-aos-delay="400">
                            <button class="faq-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq5" aria-expanded="false">
                                Is my university supported?
                            </button>
                            <div id="faq5" class="collapse" data-bs-parent="#faqAccordion">
                                <div class="card-body">
                                    We support most major universities. During registration, you'll 
                                    see a list of available universities. If yours isn't listed, 
                                    contact us and we'll work on adding it!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta-section">
        <div class="container" data-aos="zoom-in">
            <h2 class="mb-4">Ready to Transform Your Academic Journey?</h2>
            <p class="lead mb-5">
                Join thousands of students already benefiting from peer-to-peer mentoring
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="Topregister.php" class="btn btn-warning btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Sign Up Now
                </a>
                <a href="Toplogin.php" class="btn btn-light btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        </div>
    </section>

    <!-- Interactive Demo Button -->
    <a href="#how-it-works" class="demo-button">
        <i class="fas fa-play me-2"></i>Watch Demo
    </a>

    <div id="modal-container"></div>
    <!-- Dynamic footer container -->
    <div id="footer-placeholder"></div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS Animations -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>

    <!-- Main JS for shared logic -->
    <script src="assets/js/main.js"></script>
</body>

</html>