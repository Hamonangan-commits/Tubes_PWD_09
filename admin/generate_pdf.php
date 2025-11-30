<?php
require_once '../includes/auth.php';
if (!is_admin()) exit();

require_once '../vendor/tcpdf/tcpdf.php';
require_once '../includes/db.php';

// Ambil data transaksi
$stmt = $pdo->query("
    SELECT u.nama, m.merek, m.model, t.tgl_mulai, t.tgl_selesai, t.status
    FROM transaksi t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN mobil m ON t.mobil_id = m.id
    ORDER BY t.created_at DESC
");
$transaksi = $stmt->fetchAll();

// Buat PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Laporan Transaksi - RentalMobil.id');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();
$html = '<h1>Laporan Transaksi</h1><table border="1" cellpadding="6">
<tr><th>Nama</th><th>Mobil</th><th>Mulai</th><th>Selesai</th><th>Status</th></tr>';

foreach ($transaksi as $t) {
    $mobil = ($t['merek'] && $t['model']) ? $t['merek'] . ' ' . $t['model'] : 'Mobil dihapus';
    $html .= '<tr>
        <td>' . htmlspecialchars($t['nama']) . '</td>
        <td>' . htmlspecialchars($mobil) . '</td>
        <td>' . $t['tgl_mulai'] . '</td>
        <td>' . $t['tgl_selesai'] . '</td>
        <td>' . ucfirst($t['status']) . '</td>
    </tr>';
}
$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('laporan_transaksi.pdf', 'D'); // Download
?>