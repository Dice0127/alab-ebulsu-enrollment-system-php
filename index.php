<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alab E-BulSU — Student Enrollment System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        :root {
            --primary: #6c5ce7; --primary2: #a29bfe;
            --accent: #00cec9; --green: #00b894;
        }
        body { background:linear-gradient(135deg,#1a1a2e 0%,#16213e 40%,#0f3460 70%,#533483 100%), url('Bulsu_BG.png'); background-size:cover; background-attachment:fixed; background-blend-mode:multiply; min-height:100vh; overflow-x:hidden; }

        /* Blobs */
        .bg-blobs { position:fixed; inset:0; overflow:hidden; z-index:0; pointer-events:none; }
        .blob { position:absolute; border-radius:50%; filter:blur(80px); opacity:0.35; animation:blobFloat 12s ease-in-out infinite; }
        .blob-1 { width:500px;height:500px;background:#6c5ce7;top:-100px;left:-100px;animation-delay:0s; }
        .blob-2 { width:400px;height:400px;background:#00cec9;top:30%;right:-80px;animation-delay:-4s; }
        .blob-3 { width:350px;height:350px;background:#a29bfe;bottom:-80px;left:30%;animation-delay:-8s; }
        .blob-4 { width:300px;height:300px;background:#0984e3;bottom:10%;right:20%;animation-delay:-2s; }
        @keyframes blobFloat { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(20px,-20px) scale(1.05)} 66%{transform:translate(-15px,15px) scale(0.95)} }

        /* Nav */
        nav {
            position:fixed; top:0; left:0; right:0; z-index:100;
            background:rgba(15,20,60,0.55); backdrop-filter:blur(20px);
            border-bottom:1px solid rgba(255,255,255,0.1);
            padding:0 40px; height:64px;
            display:flex; align-items:center; justify-content:space-between;
        }
        .nav-brand { font-size:20px; font-weight:800; color:#fff; letter-spacing:0.5px; }
        .nav-brand span { color:#a29bfe; }
        .nav-links { display:flex; gap:12px; }
        .nav-links a {
            color:rgba(255,255,255,0.7); text-decoration:none; padding:8px 18px;
            border-radius:20px; font-size:14px; font-weight:500; transition:all 0.2s;
            border:1px solid transparent;
        }
        .nav-links a:hover { color:#fff; background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.2); }
        .nav-links a.btn-nav {
            background:linear-gradient(135deg,#6c5ce7,#a29bfe); color:#fff;
            border-color:transparent;
        }
        .nav-links a.btn-nav:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(108,92,231,0.4); }

        /* Hero */
        .hero {
            position:relative; z-index:1;
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            text-align:center; padding:100px 20px 60px;
        }
        .hero-content { max-width:750px; }
        .hero-badge {
            display:inline-block; padding:6px 18px;
            background:rgba(162,155,254,0.15); border:1px solid rgba(162,155,254,0.3);
            border-radius:20px; font-size:12px; font-weight:600; color:#a29bfe;
            letter-spacing:1px; text-transform:uppercase; margin-bottom:22px;
        }
        .hero h1 {
            font-size:58px; font-weight:800; color:#fff; line-height:1.1;
            letter-spacing:-1.5px; margin-bottom:20px;
        }
        .hero h1 .grad {
            background:linear-gradient(135deg,#a29bfe,#00cec9);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
        }
        .hero p {
            font-size:17px; color:rgba(255,255,255,0.55); line-height:1.7;
            margin-bottom:36px; max-width:580px; margin-left:auto; margin-right:auto;
        }
        .hero-actions { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
        .hero-btn {
            padding:14px 32px; border-radius:12px; font-size:15px; font-weight:600;
            text-decoration:none; transition:all 0.25s; display:inline-block;
        }
        .hero-btn-primary {
            background:linear-gradient(135deg,#6c5ce7,#a29bfe); color:#fff;
            box-shadow:0 8px 28px rgba(108,92,231,0.35);
        }
        .hero-btn-primary:hover { transform:translateY(-3px); box-shadow:0 14px 40px rgba(108,92,231,0.45); }
        .hero-btn-outline {
            background:rgba(255,255,255,0.08); color:#fff;
            border:1px solid rgba(255,255,255,0.22); backdrop-filter:blur(12px);
        }
        .hero-btn-outline:hover { background:rgba(255,255,255,0.14); transform:translateY(-3px); }

        /* Cards section */
        .portal-section {
            position:relative; z-index:1;
            padding:80px 40px; max-width:1100px; margin:0 auto;
        }
        .portal-section h2 {
            text-align:center; font-size:32px; font-weight:800; color:#fff;
            margin-bottom:10px; letter-spacing:-0.5px;
        }
        .portal-section .sub {
            text-align:center; color:rgba(255,255,255,0.45); font-size:14px; margin-bottom:48px;
        }
        .portal-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; max-width:780px; margin:0 auto; }

        .portal-card {
            background:rgba(255,255,255,0.08); backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,0.15); border-radius:22px;
            padding:36px 32px; text-decoration:none; color:#fff;
            transition:all 0.3s; position:relative; overflow:hidden;
        }
        .portal-card::before {
            content:''; position:absolute; top:0; right:0;
            width:140px; height:140px; border-radius:50%; opacity:0.08;
            transform:translate(40px,-40px);
        }
        .portal-card.student::before { background:#a29bfe; }
        .portal-card.admin::before   { background:#00cec9; }

        .portal-card:hover {
            transform:translateY(-8px);
            background:rgba(255,255,255,0.13);
            box-shadow:0 20px 60px rgba(0,0,0,0.3);
        }
        .portal-card.student:hover { border-color:rgba(162,155,254,0.5); box-shadow:0 20px 60px rgba(108,92,231,0.25); }
        .portal-card.admin:hover   { border-color:rgba(0,206,201,0.5); box-shadow:0 20px 60px rgba(0,206,201,0.2); }

        .pc-icon {
            width:54px; height:54px; border-radius:14px;
            display:flex; align-items:center; justify-content:center;
            font-size:22px; font-weight:700; margin-bottom:18px;
        }
        .portal-card.student .pc-icon { background:linear-gradient(135deg,rgba(162,155,254,0.3),rgba(108,92,231,0.2)); border:1px solid rgba(162,155,254,0.3); color:#a29bfe; }
        .portal-card.admin   .pc-icon { background:linear-gradient(135deg,rgba(0,206,201,0.25),rgba(0,184,148,0.15)); border:1px solid rgba(0,206,201,0.3); color:#00cec9; }

        .pc-title { font-size:20px; font-weight:700; margin-bottom:8px; }
        .pc-desc  { font-size:13px; color:rgba(255,255,255,0.5); line-height:1.6; margin-bottom:22px; }
        .pc-links { display:flex; gap:10px; flex-wrap:wrap; }
        .pc-link {
            padding:8px 18px; border-radius:8px; font-size:13px; font-weight:600;
            text-decoration:none; transition:all 0.2s;
        }
        .portal-card.student .pc-link.primary { background:rgba(162,155,254,0.2); color:#a29bfe; border:1px solid rgba(162,155,254,0.3); }
        .portal-card.student .pc-link.secondary{ background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.6); border:1px solid rgba(255,255,255,0.12); }
        .portal-card.admin   .pc-link.primary  { background:rgba(0,206,201,0.18); color:#00cec9; border:1px solid rgba(0,206,201,0.3); }
        .pc-link:hover { transform:translateY(-1px); filter:brightness(1.2); }

        /* Features */
        .features {
            position:relative; z-index:1;
            padding:40px 40px 80px; max-width:1100px; margin:0 auto;
        }
        .features h2 { text-align:center; font-size:28px; font-weight:700; color:#fff; margin-bottom:8px; }
        .features .sub { text-align:center; color:rgba(255,255,255,0.4); font-size:14px; margin-bottom:40px; }
        .features-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }

        .feature-item {
            background:rgba(255,255,255,0.06); backdrop-filter:blur(16px);
            border:1px solid rgba(255,255,255,0.1); border-radius:16px;
            padding:24px 22px; text-align:center;
        }
        .fi-icon {
            width:46px; height:46px; border-radius:12px; margin:0 auto 14px;
            display:flex; align-items:center; justify-content:center; font-size:18px;
        }
        .fi-title { font-size:14px; font-weight:700; color:#fff; margin-bottom:6px; }
        .fi-desc  { font-size:12px; color:rgba(255,255,255,0.45); line-height:1.6; }

        /* Footer */
        footer {
            position:relative; z-index:1; text-align:center;
            padding:24px; border-top:1px solid rgba(255,255,255,0.08);
            color:rgba(255,255,255,0.3); font-size:12px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav>
    <div class="nav-brand"><img src="Bulsu_Logo.png" alt="BulSU" style="height:32px; width:auto; margin-right:10px; vertical-align:middle;"> Alab E-BulSU</div>
    <div class="nav-links">
        <a href="student/login.php">Student Login</a>
        <a href="admin/login.php" class="btn-nav">Admin Portal</a>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">Bulacan State University</div>
        <h1>Alab E-BulSU<br><span class="grad">Student Enrollment System</span></h1>
        <p>Alab BulSU - Student Enrollment System of Bulacan State University   Commission on Higher Education (CHED) Region III.</p>
        <div class="hero-actions">
            <a href="student/register.php" class="hero-btn hero-btn-primary">Get Started — Enroll Now</a>
            <a href="student/login.php" class="hero-btn hero-btn-outline">Student Login</a>
        </div>
    </div>
</section>

<!-- Portal Cards -->
<section class="portal-section">
    <h2>Choose Your Portal</h2>
    <p class="sub">Access the system based on your role</p>
    <div class="portal-grid">
        <div class="portal-card student">
            <div class="pc-icon">S</div>
            <div class="pc-title">Student Portal</div>
            <div class="pc-desc">Create an account, submit your enrollment, and track your application status in real time.</div>
            <div class="pc-links">
                <a href="student/register.php" class="pc-link primary">Sign Up</a>
                <a href="student/login.php" class="pc-link secondary">Log In</a>
            </div>
        </div>
        <div class="portal-card admin">
            <div class="pc-icon">A</div>
            <div class="pc-title">Admin Portal</div>
            <div class="pc-desc">Manage enrollments, approve or reject applications, view analytics and generate reports.</div>
            <div class="pc-links">
                <a href="admin/login.php" class="pc-link primary">Admin Login</a>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features">
    <h2>System Features</h2>
    <p class="sub">Everything you need in one platform</p>
    <div class="features-grid">
        <div class="feature-item">
            <div class="fi-icon" style="background:rgba(162,155,254,0.15);border:1px solid rgba(162,155,254,0.25);color:#a29bfe;">EN</div>
            <div class="fi-title">Online Enrollment</div>
            <div class="fi-desc">Students can submit enrollment forms online without visiting the registrar.</div>
        </div>
        <div class="feature-item">
            <div class="fi-icon" style="background:rgba(85,239,196,0.15);border:1px solid rgba(85,239,196,0.25);color:#55efc4;">AP</div>
            <div class="fi-title">Approval System</div>
            <div class="fi-desc">Admins can approve or reject enrollments with remarks, tracked in real time.</div>
        </div>
        <div class="feature-item">
            <div class="fi-icon" style="background:rgba(116,185,255,0.15);border:1px solid rgba(116,185,255,0.25);color:#74b9ff;">ST</div>
            <div class="fi-title">Status Tracking</div>
            <div class="fi-desc">Students see their enrollment status and assigned student number instantly.</div>
        </div>
        <div class="feature-item">
            <div class="fi-icon" style="background:rgba(253,203,110,0.15);border:1px solid rgba(253,203,110,0.25);color:#fdcb6e;">SN</div>
            <div class="fi-title">Auto Student Number</div>
            <div class="fi-desc">Student numbers are automatically generated upon enrollment submission.</div>
        </div>
        <div class="feature-item">
            <div class="fi-icon" style="background:rgba(250,177,160,0.15);border:1px solid rgba(250,177,160,0.25);color:#fab1a0;">AN</div>
            <div class="fi-title">Analytics Dashboard</div>
            <div class="fi-desc">Visual charts and reports on enrollment data, programs, and demographics.</div>
        </div>
        <div class="feature-item">
            <div class="fi-icon" style="background:rgba(0,206,201,0.15);border:1px solid rgba(0,206,201,0.25);color:#00cec9;">SC</div>
            <div class="fi-title">Secure Accounts</div>
            <div class="fi-desc">Password-protected accounts for both students and administrators.</div>
        </div>
    </div>
</section>

<footer>
    IT 211 — Web Systems and Technologies &nbsp;|&nbsp; Dr. Aaron Paul M. Dela Rosa &nbsp;|&nbsp; Bulacan State University CICT
</footer>

</body>
</html>
