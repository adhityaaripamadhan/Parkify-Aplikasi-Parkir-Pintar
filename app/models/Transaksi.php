<?php
require_once dirname(__DIR__, 2) . '/config/database.php';

class Transaksi
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // rfid masuk
    public function rfidMasuk($card_id)
    {
        // cek apakah kartu masih berstatus IN (sudah masuk)
        $cek = $this->db->prepare("
            SELECT id FROM transaksi 
            WHERE card_id = :card AND status = 'IN'
        ");
        $cek->execute(['card' => $card_id]);

        // jika masih berstatus IN (sudah masuk) maka tolak
        if ($cek->rowCount() > 0) {
            return false;
        }

        // insert kendaraan masuk
        $stmt = $this->db->prepare("
            INSERT INTO transaksi (card_id, checkin_time, status)
            VALUES (:card, NOW(), 'IN')
        ");

        return $stmt->execute(['card' => $card_id]);
    }

    // rfid keluar
    public function rfidKeluar($card_id)
    {
        // update data kendaraan keluar, hitung durasi & biaya
        $stmt = $this->db->prepare("
            UPDATE transaksi SET
                checkout_time = NOW(),
                duration = TIMESTAMPDIFF(SECOND, checkin_time, NOW()),
                fee = CEIL(TIMESTAMPDIFF(SECOND, checkin_time, NOW()) / 3600) * 2000,
                status = 'OUT'
            WHERE card_id = :card
            AND status = 'IN'
        ");

        return $stmt->execute(['card' => $card_id]);
    }

    // konfirmasi keluar
    public function konfirmasiKeluar($id)
    {
        // ubah status dari OUT ke DONE (sudah bayar)
        $stmt = $this->db->prepare("
        UPDATE transaksi
        SET status = 'DONE'
        WHERE id = :id
        AND status = 'OUT'
    ");

        $stmt->execute(['id' => $id]);
        
        return $stmt->rowCount(); // return jumlah row yg berubah (0 atau 1)
    }

    // data yang masih parkir
    public function getMasuk()
    {
        // ambil semua yang masih parkir atau berstatus IN
        return $this->db->query("
            SELECT * FROM transaksi 
            WHERE status='IN'
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    // data sudah keluar (belum konfirmasi)
    public function getKeluar()
    {
        // data yang sudah berstatus OUT atau keluar tapi belum bayar
        return $this->db->query("
        SELECT *
        FROM transaksi
        WHERE status='OUT'
        ORDER BY checkout_time ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistory()
    {
        // data kendaraan berstatus DONE atau sudah selesai
        return $this->db->query("
            SELECT * FROM transaksi 
            WHERE status='DONE'
            ORDER BY checkout_time DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    // statistik dashboard
    public function statistik()
    {
        return [
            // jumlah kendaraan masih parkir
            'masuk' => $this->db->query("
                SELECT COUNT(*) FROM transaksi WHERE status='IN'
            ")->fetchColumn(),

            // jumlah kendaraan keluar (belum bayar)
            'keluar' => $this->db->query("
                SELECT COUNT(*) FROM transaksi WHERE status='OUT'
            ")->fetchColumn(),

            // jumlah transaksi selesai hari ini
            'selesai' => $this->db->query("
                SELECT COUNT(*) FROM transaksi 
                WHERE status='DONE'
                AND DATE(checkout_time)=CURDATE()
            ")->fetchColumn(),
        ];
    }

    // rekap bulanan 
    public function getRekapBulanan()
    {
        $bulan = date('m');
        $tahun = date('Y');

        // ambil semua transaksi bulan ini yang sudah selesai
        $query = "
        SELECT * FROM transaksi
        WHERE MONTH(checkin_time) = ?
        AND YEAR(checkin_time) = ?
        AND status = 'DONE'
        ORDER BY checkin_time DESC
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$bulan, $tahun]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // total pendapatan bulanan 
    public function getTotalPendapatanBulanan()
    {
        $bulan = date('m');
        $tahun = date('Y');

        // hitung total fee bulan ini
        $query = "
        SELECT SUM(fee) as total
        FROM transaksi
        WHERE MONTH(checkin_time) = ?
        AND YEAR(checkin_time) = ?
        AND status = 'DONE'
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$bulan, $tahun]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }

    // statistik harian dalam bulan
    public function getStatistikBulanan()
    {
        $bulan = date('m');
        $tahun = date('Y');

        // data per hari (jumlah transaksi + pendapatan)
        $query = "
        SELECT 
            DATE(checkin_time) as tanggal,
            COUNT(*) as total_transaksi,
            SUM(fee) as total_pendapatan
        FROM transaksi
        WHERE MONTH(checkin_time) = ?
        AND YEAR(checkin_time) = ?
        AND status = 'DONE'
        GROUP BY DATE(checkin_time)
        ORDER BY tanggal ASC
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$bulan, $tahun]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ambil biaya berdasarkan id 
    public function getFeeById($id)
    {
        $stmt = $this->db->prepare("
        SELECT fee FROM transaksi
        WHERE id = ?
    ");
        $stmt->execute([$id]);

        return $stmt->fetchColumn();
    }
}
