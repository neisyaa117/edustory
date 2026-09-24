<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="google-site-verification" content="TNqkQhL54n57nRMTW2v8rR4Q6kJ2p6yLGnfzn1alhSY" />

    <title>EduStory - Cerita Sekolah</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="brand">
            <span class="brand-icon">✦</span>
            <span>EduStory</span>
        </a>

        <div class="nav-actions">
            <a href="login.php" class="nav-login">Masuk</a>
            <a href="daftar.php" class="nav-register">Daftar</a>
        </div>
    </div>
</nav>


<main>

    <section class="hero">

        <div class="hero-content">

            <div class="small-label">
                ✨ Cerita dari sekolah, untuk semua
            </div>

            <h1>
                Setiap sekolah<br>
                punya <span>cerita.</span>
            </h1>

            <p>
                EduStory membantu sekolah membuat cerita tentang
                kegiatan, prestasi, anak-anak hebat, dan berbagai
                hal menarik lainnya dengan lebih mudah.
            </p>

            <div class="hero-buttons">
                <a href="daftar.php" class="btn-primary">
                    Mulai Bercerita
                    <span>→</span>
                </a>

                <a href="login.php" class="btn-text">
                    Sudah punya akun? Masuk
                </a>
            </div>

        </div>


        <div class="hero-illustration">

            <div class="illustration-paper">

                <div class="paper-top">
                    <span>📚</span>
                    <span>✏️</span>
                </div>

                <div class="paper-title">
                    Cerita Sekolah
                </div>

                <div class="paper-line long"></div>
                <div class="paper-line"></div>
                <div class="paper-line medium"></div>

                <div class="paper-photo">
                    🏫
                </div>

                <div class="paper-bottom">
                    <span>🌱</span>
                    <span>⭐</span>
                    <span>💛</span>
                </div>

            </div>

            <div class="floating-note note-one">
                🌟 Prestasi
            </div>

            <div class="floating-note note-two">
                🎉 Kegiatan
            </div>

            <div class="floating-note note-three">
                💬 Cerita
            </div>

        </div>

    </section>


    <section class="intro-section">

        <div class="section-heading">
            <span class="section-label">APA YANG BISA DICERITAKAN?</span>
            <h2>Hal-hal kecil yang membuat sekolah berarti.</h2>
            <p>
                Tidak harus cerita besar. Kegiatan sehari-hari,
                pengalaman siswa, hingga prestasi sekolah juga
                layak untuk diceritakan.
            </p>
        </div>


        <div class="intro-items">

            <div class="intro-item">
                <div class="intro-icon purple">🏫</div>
                <div>
                    <h3>Profil Sekolah</h3>
                    <p>Kenalkan sekolah dan orang-orang di dalamnya.</p>
                </div>
            </div>

            <div class="intro-item">
                <div class="intro-icon orange">🎈</div>
                <div>
                    <h3>Kegiatan</h3>
                    <p>Abadikan kegiatan seru dan bermakna.</p>
                </div>
            </div>

            <div class="intro-item">
                <div class="intro-icon yellow">🏆</div>
                <div>
                    <h3>Prestasi</h3>
                    <p>Bagikan pencapaian dan karya sekolah.</p>
                </div>
            </div>

        </div>

    </section>


    <section class="bottom-cta">

        <div>
            <span class="cta-small">SIAP MEMULAI?</span>
            <h2>Yuk, mulai ceritakan sekolahmu.</h2>
        </div>

        <a href="daftar.php" class="btn-white">
            Buat Akun →
        </a>

    </section>

</main>


<footer class="footer">
    <p>© <?php echo date('Y'); ?> EduStory · Cerita sekolah, jadi lebih mudah.</p>
</footer>

</body>
</html>