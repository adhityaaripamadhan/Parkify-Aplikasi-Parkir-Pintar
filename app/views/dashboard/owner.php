<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Parkify - Dashboard Owner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- kustom css -->
    <link rel="stylesheet" href="/parkir-app/public/assets/css/style.css?">
    <!-- css datatables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <!-- datatables export button -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <!-- css icon awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- chart.js grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <?php
    $totalPendapatan = 0; // total semua pendapatan
    $totalTransaksi = count($rekap); // jumlah transaksi
    $aktif = 0; // transaksi masih aktif
    $selesai = 0; // transaksi selesai
    $keluar = 0;

    // array untuk grafik
    $pendapatanPerBulan = [];
    $transaksiPerBulan = [];

    foreach ($rekap as $row) {

        $fee = $row['fee'] ?? 0; // ambil fee (jika null maka 0)
        $totalPendapatan += $fee; // total pendapatan
    
        if ($row['status'] == 'IN') {
            $aktif++;
        } elseif ($row['status'] == 'OUT') {
            $keluar++;
        } elseif ($row['status'] == 'DONE') {
            $selesai++;
        }

        // ambil bulan dari tanggal check-in
        $bulan = date('Y-m', strtotime($row['checkin_time']));

        // jika bulan belum di array
        if (!isset($pendapatanPerBulan[$bulan])) {
            $pendapatanPerBulan[$bulan] = 0;
            $transaksiPerBulan[$bulan] = 0;
        }

        // akumulasi data perbulan
        $pendapatanPerBulan[$bulan] += $fee;
        $transaksiPerBulan[$bulan]++;
    }
    ?>

    <div class="dashboard">
        <h2>Dashboard Owner</h2>
        <a href="#" onclick="confirmLogout()" style="text-decoration:none; color:inherit;">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <p>Total Pendapatan</p>
            <h3>Rp<?= number_format($totalPendapatan, 0, ',', '.') ?></h3> <!-- format rupiah -->
        </div>
        <div class="stat-card">
            <p>Total Transaksi</p>
            <h3><?= $totalTransaksi ?></h3>
        </div>
        <div class="stat-card">
            <p>Transaksi Selesai</p>
            <h3><?= $selesai ?></h3>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <h4>Pendapatan per Bulan</h4>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-card">
            <h4>Transaksi per Bulan</h4>
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    <div class="table-card">

        <div class="export-wrapper">
            <button class="btn-export">Export ▾</button> <!-- tombol export data -->
            <div class="export-dropdown">
                <button id="btnExcel">Export Excel</button>
                <button id="btnPdf">Export PDF</button>
            </div>
        </div>

        <table id="rekapTransaksi" class="display">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Card ID</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Durasi</th>
                    <th>Tarif</th>
                </tr>
            </thead>
            <tbody>
                <!-- loop data transaksi -->
                <?php $no = 1; foreach ($rekap as $row): 
                    // hitung durasi dari detik ke jam:menit:detik
                    $durasiDetik = $row['duration'] ?? 0;
                    $jam = floor($durasiDetik / 3600);
                    $menit = floor(($durasiDetik % 3600) / 60);
                    $detik = $durasiDetik % 60;

                    // format durasi
                    $durasiFormat = sprintf("%02d:%02d:%02d", $jam, $menit, $detik);
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['card_id'] ?></td>
                        <td><?= $row['checkin_time'] ?></td>
                        <td><?= $row['checkout_time'] ?? '-' ?></td>
                        <td><?= $durasiFormat ?></td>
                        <td>Rp<?= number_format($row['fee'] ?? 0, 0, ',', '.') ?></td> <!-- format rupiah -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- SCRIPT -->
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- datatables -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <!-- export -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <!-- library export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <!-- sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- datatable -->
    <script>
        $(document).ready(function () {

            // inisialisasi datatable, export
            const table = $('#rekapTransaksi').DataTable({
                dom: 'frtip',
                buttons: [
                    { extend: 'excelHtml5', title: 'Rekap_Transaksi' },
                    { extend: 'pdfHtml5', title: 'Rekap_Transaksi' }
                ]
            });
            // toggle dropdown export
            $('.btn-export').click(function () {
                $('.export-dropdown').toggle();
            });
            // tombol export excel
            $('#btnExcel').click(function () {
                table.button('.buttons-excel').trigger();
            });
            // tombol export pdf
            $('#btnPdf').click(function () {
                table.button('.buttons-pdf').trigger();
            });

        });
    </script>

    <!-- alert login berhasil -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'login') { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: 'Selamat datang di Dashboard Owner',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // hapus parameter msg dari url tnpa reload
                window.history.replaceState({}, document.title, window.location.pathname + "?url=AuthController/dashboard");
            });
        </script>
    <?php } ?>

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

    <!-- chart -->
    <script>
        // bar chart (pendapatan per bulan)
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($pendapatanPerBulan)) ?>,
                datasets: [{ label: 'Pendapatan', data: <?= json_encode(array_values($pendapatanPerBulan)) ?> }]
            }
        });

        // linechart (transaksi per bulan)
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_keys($transaksiPerBulan)) ?>,
                datasets: [{ label: 'Transaksi', data: <?= json_encode(array_values($transaksiPerBulan)) ?> }]
            }
        });
    </script>

</body>

</html>