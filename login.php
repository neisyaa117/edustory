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

    <title>Masuk - EduStory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body class="auth-page">


<nav class="navbar">

    <div class="nav-container">

        <a href="index.php" class="brand">
            <span class="brand-icon">✦</span>
            <span>EduStory</span>
        </a>

        <a href="index.php" class="back-home">
            ← Beranda
        </a>

    </div>

</nav>


<main class="auth-wrapper">

    <div class="auth-decoration decoration-left">
        📚
    </div>

    <div class="auth-decoration decoration-right">
        💛
    </div>


    <div class="auth-box">

        <div class="auth-heading">

            <div class="auth-icon">
                👋
            </div>

            <span class="section-label">HALO LAGI!</span>

            <h1>Selamat datang</h1>

            <p>
                Masuk dan lanjutkan cerita sekolahmu.
            </p>

        </div>


        <form action="proses/proses-login.php" method="POST">

            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Masukkan email"
                    required
                >

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >

            </div>


            <button type="submit" class="btn-primary btn-full">
                Masuk
                <span>→</span>
            </button>

        </form>


        <div class="auth-footer">

            Belum punya akun?

            <a href="daftar.php">
                Daftar sekarang
            </a>

        </div>

    </div>

</main>


<footer class="footer">
    <p>© <?php echo date('Y'); ?> EduStory</p>
</footer>


</body>
</html>