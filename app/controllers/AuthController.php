<?php
class AuthController extends Controller
{
    public function index()
    {
        $this->view('auth/login');
    }

    // proses login user
    public function login()
    {
        session_start();

        // ambil input dari form
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // cek ke database
        $userModel = $this->model('User');
        $user = $userModel->login($username, $password);

        if ($user) {
            // simpan session jika login berhasil
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // redirect ke dashboard
            header("Location: index.php?url=AuthController/dashboard&msg=login");
            exit;
        } else {
            // redirect login gagal
            header("Location: index.php?url=AuthController/index&error=invalid");
            exit;
        }
    }

    // mengarahkan user sesuai role
    public function dashboard()
    {
        // cek apakah sudah login
        if (!isset($_SESSION['role'])) {
            header("Location: index.php");
            exit;
        }

        $role = $_SESSION['role'];

        // jika petugas ke dashboard petugas
        if ($role === 'petugas') {
            header("Location: index.php?url=TransaksiController/index&msg=login");
            exit;
        }

        // jika owner ke dashboard owner
        if ($role === 'owner') {

            $transaksiModel = $this->model('Transaksi');

            // ambil data rekap & statistik
            $rekap = $transaksiModel->getRekapBulanan();
            $statistik = $transaksiModel->getStatistikBulanan();
            $totalPendapatan = $transaksiModel->getTotalPendapatanBulanan();

            // tampilkan view owner
            $this->view("dashboard/owner", [
                'rekap' => $rekap,
                'statistik' => $statistik,
                'totalPendapatan' => $totalPendapatan
            ]);

            return;
        }

        // fallback jika role lain
        $this->view("dashboard/$role");
    }

    // proses logout
    public function logout()
    {
        session_start();
        session_destroy(); // hapus semua session

        header("Location: index.php"); // kembali ke login
        exit;
    }
}