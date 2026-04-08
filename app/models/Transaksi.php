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

    public function rfidMasuk($card_id)
    {
        $cek = $this->db->prepare("
            SELECT id FROM transaksi 
            WHERE card_id = :card AND status = 'IN'
        ");
        $cek->execute(['card' => $card_id]);

        if ($cek->rowCount() > 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO transaksi (card_id, checkin_time, status)
            VALUES (:card, NOW(), 'IN')
        ");

        return $stmt->execute(['card' => $card_id]);
    }

    public function rfidKeluar($card_id)
    {
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

    public function konfirmasiKeluar($id)
    {
        $stmt = $this->db->prepare("
        UPDATE transaksi
        SET status = 'DONE'
        WHERE id = :id
        AND status = 'OUT'
    ");

        $stmt->execute(['id' => $id]);

        return $stmt->rowCount();
    }

    public function getMasuk()
    {
        return $this->db->query("
            SELECT * FROM transaksi 
            WHERE status='IN'
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKeluar()
    {
        return $this->db->query("
        SELECT *
        FROM transaksi
        WHERE status='OUT'
        ORDER BY checkout_time ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistory()
    {
        return $this->db->query("
            SELECT * FROM transaksi 
            WHERE status='DONE'
            ORDER BY checkout_time DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function statistik()
    {
        return [
            'masuk' => $this->db->query("
                SELECT COUNT(*) FROM transaksi WHERE status='IN'
            ")->fetchColumn(),

            'keluar' => $this->db->query("
                SELECT COUNT(*) FROM transaksi WHERE status='OUT'
            ")->fetchColumn(),

            'selesai' => $this->db->query("
                SELECT COUNT(*) FROM transaksi 
                WHERE status='DONE'
                AND DATE(checkout_time)=CURDATE()
            ")->fetchColumn(),
        ];
    }

    public function getRekapBulanan()
    {
        $bulan = date('m');
        $tahun = date('Y');

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

    public function getTotalPendapatanBulanan()
    {
        $bulan = date('m');
        $tahun = date('Y');

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

    public function getStatistikBulanan()
    {
        $bulan = date('m');
        $tahun = date('Y');

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