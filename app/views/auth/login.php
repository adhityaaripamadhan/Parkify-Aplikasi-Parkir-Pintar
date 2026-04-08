<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Parkify - Login Akun</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/parkir-app/public/assets/css/style.css?v=1">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #003c37, #019a8a);
        }
    </style>

</head>

<body>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="login-wrapper">
        <div class="login-box">

            <div class="login-left">
                <img src="/parkir-app/public/assets/image/Parkify.png" alt="Logo">
            </div>

            <div class="login-right">
                <p class="text_login">Login</p>
                <center>
                    <text class="text_login2">Silahkan masuk ke akun milik Anda!</text>
                </center>
                <br>
                <form id="formLogin" action="index.php?url=AuthController/login" method="POST">
                    <input type="text" name="username" id="username" class="form_login" placeholder="Username">
                    <input type="password" name="password" id="password" class="form_login" placeholder="Password">
                    <button type="submit" class="tombol_login">Masuk</button>
                </form>

            </div>

        </div>
    </div>

    <script>
        function tutuppopup() {
            document.getElementById("popupError").style.display = "none";
        }
    </script>

    <!-- validasi form -->
    <script>
        const form = document.getElementById("formLogin");

        form.addEventListener("submit", function (e) {
            e.preventDefault(); // hentikan submit default

            let username = document.getElementById("username").value.trim();
            let password = document.getElementById("password").value.trim();

            // jika dua-duanya kosong
            if (username === "" && password === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Kosong!',
                    text: 'Username dan Password wajib diisi.'
                });
                return;
            }

            // jika salah satu kosong
            if (username === "" || password === "") {
                Swal.fire({
                    icon: 'info',
                    title: 'Input Belum Lengkap!',
                    text: 'Kedua kolom harus diisi.'
                });
                return;
            }

            // jika benar akan mengirim ke server
            form.submit();
        });
    </script>

    <!-- alert login -->
    <!-- berhasil -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 'login') { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: 'Selamat datang di sistem Parkify',
                timer: 1500,
                showConfirmButton: false
            });
        </script>
    <?php } ?>

    <!-- gagal -->
    <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid') { ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: 'Username atau Password tidak sesuai.'
            });
        </script>
    <?php } ?>

</body>

</html>