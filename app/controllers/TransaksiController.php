<?php
require __DIR__ . '/../../vendor/autoload.php';
use PhpMqtt\Client\MqttClient;

class TransaksiController extends Controller
{
    public function index()
    {
        $model = $this->model('Transaksi');

        // ambil data dari database
        $data['masuk'] = $model->getMasuk();
        $data['keluar'] = $model->getKeluar();
        $data['history'] = $model->getHistory();
        $data['stat'] = $model->statistik();

        // kirim ke view
        $this->view('dashboard/petugas_transaksi', $data);
    }

    // proses kendaraan keluar
    public function selesai()
    {
        session_start();

        if (!isset($_SESSION['user'])) {
            header("Location: login.php");
            exit;
        }

        $id = $_POST['id'];
        $model = $this->model('Transaksi');

        if ($model->konfirmasiKeluar($id) > 0) {

            try {

                $config = require __DIR__ . '/../../config/mqtt.php';

                $mqtt = new MqttClient(
                    $config['broker'],
                    $config['port'],
                    $config['client_id']
                );

                $mqtt->connect();

                // LCD
                $mqtt->publish(
                    $config['prefix'].'/'.$config['topic_lcd'],
                    'Terima Kasih|Selamat Jalan',
                    1
                );

                usleep(500000);

                // SERVO EXIT
                $mqtt->publish(
                    $config['prefix'].'/'.$config['topic_exit_servo'],
                    'OPEN',
                    1
                );

                $mqtt->disconnect();

            } catch (\Exception $e) {
                error_log($e->getMessage());
            }

            header("Location: index.php?url=TransaksiController&msg=sukses");
            exit;

        } else {
            header("Location: index.php?url=TransaksiController&msg=gagal");
            exit;
        }
    }
}