<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Pastikan autoload PHPMailer disertakan

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari borang
    $emel = $_POST['emel'] ?? '';
    $nama_pelajar = $_POST['nama_pelajar'] ?? '';
    $aduan = $_POST['aduan'] ?? '';
    $dorm = $_POST['dorm'] ?? '';
    $tarikh = $_POST['tarikh'] ?? '';
    $masa = $_POST['masa'] ?? '';

    // Semak jika semua medan diisi
    if (empty($email) || empty($nama_pelajar) || empty($aduan) || empty($dorm) || empty($tarikh) || empty($masa)) {
        echo "Sila isi semua medan.";
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tpsakvsepang@gmail.com'; // Gantikan dengan alamat e-mel baru anda
        $mail->Password = 'uhfv cqai sngt iyel'; // Gantikan dengan app password yang telah dijana
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($email, $nama_pelajar);
        $mail->addAddress("tpsakvsepang@gmail.com", "TPSA");

        // Susun isi email
        $mail->Subject = 'Laporan Aduan Dewan Makan';
        $mail->Body = "Kepada TPSA,\n\n" .
            "Saya, $nama_pelajar, dari dorm $dorm ingin mengemukakan aduan mengenai:\n" .
            "1. Aduan: $aduan\n" .
            "2. Tarikh: $tarikh\n" .
            "3. Masa: $masa\n\n" .
            "Saya berharap agar aduan ini dapat diambil perhatian yang sewajarnya.\n\n" .
            "Sekian, terima kasih.";

        // Hantar email
        $mail->send();

        // Tambah alert JavaScript
        echo "<script>
        alert('Email telah dihantar kepada TPSA dengan jayanya!');
        window.location.href = 'dashboard.php'; // Arahkan ke halaman sent.html
      </script>";
        exit;

        // Arahkan ke halaman sent.html jika email berjaya dihantar
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        echo "Status: Email Tidak Dihantar<br>";
        echo "Ralat: {$mail->ErrorInfo}";
    }
} else {
    echo "Borang tidak dihantar.";
}
