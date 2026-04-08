<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Parkify - Dashboard Petugas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- css databatables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <!-- css icon awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- kustom css -->
    <link rel="stylesheet" href="/parkir-app/public/assets/css/style.css?v=1">
</head>

<body>

    <div class="dashboard">
        <h2>Selamat Datang, Petugas!</h2>
        <a href="#" onclick="confirmLogout()" style="text-decoration:none; color:inherit;">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>

    <div class="row">
        <div class="col">
            <div class="card">
                Sedang Parkir<br>
                <b><?= $stat['masuk'] ?></b> <!-- kendaraan yang sedang parkir -->
            </div>
        </div>

        <div class="col">
            <div class="card">
                Selesai Parkir<br>
                <b><?= $stat['selesai'] ?? 0 ?></b> <!-- kendaraan selesai parkir, default 0 jika kosong -->
            </div>
        </div>
    </div>

    <!-- data kendaraan masuk -->
    <h2 class="headline">
        <i class="fa-solid fa-arrow-right-to-bracket"></i>
        Kendaraan Masuk
    </h2>

    <table id="tblMasuk" class="display">
        <thead>
            <tr>
                <th>ID Card</th>
                <th>Masuk</th>
            </tr>
        </thead>
        <tbody>
            <!-- looping data kendaraan masuk -->
            <?php foreach ($masuk as $r): ?>
                <tr>
                    <td><?= $r['card_id'] ?></td> <!-- menampilkan id kartu rfid -->
                    <td><?= $r['checkin_time'] ?></td> <!-- menampilkan waktu check-in -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- data kendaraan keluar -->
    <h2 class="headline">
        <i class="fa-solid fa-hourglass-half"></i>
        Menunggu Keluar
    </h2>

    <table id="tblKeluar" class="display">
        <thead>
            <tr>
                <th>ID Card</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Durasi</th>
                <th>Biaya</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <!-- looping data kendaraan yang akan keluar -->
            <?php foreach ($keluar as $r): ?>
                <tr>
                    <td><?= $r['card_id'] ?></td>
                    <td><?= $r['checkin_time'] ?></td>
                    <td><?= $r['checkout_time'] ?></td>
                    <td><?= gmdate("H:i:s", $r['duration']) ?></td> <!-- format durasi > jam:menit:detik -->
                    <td>Rp<?= number_format($r['fee']) ?></td> <!-- format biaya rupiah -->
                    <td>
                        <form method="POST" action="index.php?url=TransaksiController/selesai">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>"> <!-- mengirim id transaksi -->
                            <button type="submit" class="button-palang"> <!-- tombol buka palang -->
                                Buka Palang
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- data riwayat parkir kendaraan-->
    <h2 class="headline">
        <i class="fa-solid fa-clock-rotate-left"></i>
        Riwayat Parkir
    </h2>

    <table id="tblHistory" class="display">
        <thead>
            <tr>
                <th>ID Card</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Durasi</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            <!-- looping data riwayat parkir -->
            <?php foreach ($history as $r): ?>
                <tr>
                    <td><?= $r['card_id'] ?></td>
                    <td><?= $r['checkin_time'] ?></td>
                    <td><?= $r['checkout_time'] ?></td>
                    <td><?= gmdate("H:i:s", $r['duration']) ?></td> <!-- format durasi > jam:menit:detik -->
                    <td>Rp<?= number_format($r['fee']) ?></td> <!-- format rupiah -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- SCRIPT -->
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- datatables -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <!-- sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- inisialisasi datatables -->
    <script>
        $('#tblMasuk, #tblKeluar, #tblHistory').DataTable({
            searching: false,   // disable search
            lengthChange: false, // disable show entries
            info: false,        // hide info
            pageLength: 5,      // tampil 5 data per halaman
            ordering: false     // disable sorting
        });
    </script>

    <!-- alert message sukses parkir-->
    <?php if (isset($_GET['msg'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {

                // ambil parameter msg dari url
                let msg = "<?= $_GET['msg'] ?>";

                // sukses
                if (msg === "sukses") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Palang terbuka & transaksi selesai',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "index.php?url=TransaksiController";
                    });

                    // gagal
                } else if (msg === "gagal") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Transaksi gagal diproses'
                    }).then(() => {
                        window.location.href = "index.php?url=TransaksiController";
                    });
                }

                else if (msg === "login") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil!',
                        text: 'Selamat datang di Dashboard Petugas',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // hapus parameter msg dari url tnpa reload
                        window.history.replaceState({}, document.title, window.location.pathname + "?url=TransaksiController/index");
                    });
                }
            });
        </script>
    <?php endif; ?>

    <!-- alert konfirmasi logout -->
    <script>
        function confirmLogout() {
            console.log('Fungsi dipanggil');
            Swal.fire({
                title: 'Apakah Anda yakin ingin logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#01796f',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }
    </script>

    <!-- auto refresh 30 dtk -->
    <script>
        setInterval(function () {
            window.location.reload();
        }, 30000); // = 30 detik
    </script>

</body>

</html>