<?php
require_once __DIR__ . '/config/init.php';
if (is_logged_in()) redirect('dashboard.php');
$page_title = 'Smart Attendance, Simplified';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container landing-page">
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow">Workforce attendance platform</span>
            <h1>Attendance that keeps every workday on track.</h1>
            <p>A focused HR workspace for clock-ins, shift punctuality, monthly insights, and reliable employee records.</p>
            <div class="hero-actions">
                <a href="login.php" class="btn btn-primary">Open employee portal</a>
                <a href="register.php" class="btn btn-secondary">Create an account</a>
            </div>
            <div class="hero-proof" aria-label="Product highlights">
                <span><strong>Real-time</strong> punch tracking</span><span><strong>Role-based</strong> reporting</span><span><strong>CSV</strong> bulk import</span>
            </div>
        </div>
        <div class="hero-preview" aria-label="Attendance dashboard preview">
            <div class="preview-top"><span>Today’s attendance</span><span class="live-dot">Live</span></div>
            <div class="preview-status"><div class="status-icon">✓</div><div><small>Current status</small><strong>Checked in</strong></div><time>08:54</time></div>
            <div class="preview-grid"><div><strong>21</strong><span>Present</span></div><div><strong>02</strong><span>Late</span></div><div><strong>01</strong><span>Absent</span></div></div>
            <div class="preview-row"><span>Mon, Sep 28</span><span class="badge badge-success">On time</span></div>
            <div class="preview-row"><span>Fri, Sep 25</span><span class="badge badge-warning">Late</span></div>
        </div>
    </section>
    <section class="feature-section">
        <div class="section-heading"><span class="eyebrow">Built for both sides of HR</span><h2>One system, two clear experiences.</h2></div>
        <div class="feature-grid">
            <article class="feature-card"><span class="feature-number">01</span><h3>Employee self-service</h3><p>Clock in and out, review monthly attendance, and see punctuality status without chasing spreadsheets.</p></article>
            <article class="feature-card"><span class="feature-number">02</span><h3>Actionable HR reports</h3><p>Filter attendance by month and department, inspect employee history, and correct records when needed.</p></article>
            <article class="feature-card"><span class="feature-number">03</span><h3>Practical data tools</h3><p>Import CSV attendance exports with validation and row-level feedback for a dependable workflow.</p></article>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
