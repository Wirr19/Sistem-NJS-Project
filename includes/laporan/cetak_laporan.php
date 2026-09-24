<?php

/* =========================================================
   OUTPUT BUFFERING
   Mencegah output lain mengganggu header PDF FPDF
========================================================= */
ob_start();

session_start();

/* =========================================================
   LOAD FILE
========================================================= */
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/fpdf/fpdf.php';


/* =========================================================
   CEK LOGIN
========================================================= */

if (!isset($_SESSION['login'])) {

    // Bersihkan buffer sebelum redirect
    if (ob_get_length()) {
        ob_end_clean();
    }

    header("Location: ../../login.php");
    exit;
}


/* =========================================================
   AMBIL FILTER DARI LAPORAN.PHP
========================================================= */

$periode_awal  = $_GET['periode_awal'] ?? '';
$periode_akhir = $_GET['periode_akhir'] ?? '';


/* =========================================================
   VALIDASI FORMAT PERIODE
   Format yang diharapkan: YYYY-MM
========================================================= */

if ($periode_awal !== '' && !preg_match('/^\d{4}-\d{2}$/', $periode_awal)) {
    $periode_awal = '';
}

if ($periode_akhir !== '' && !preg_match('/^\d{4}-\d{2}$/', $periode_akhir)) {
    $periode_akhir = '';
}


/* =========================================================
   KONDISI FILTER
========================================================= */

$where = '';

if ($periode_awal !== '' && $periode_akhir !== '') {

    $periode_awal_sql = mysqli_real_escape_string(
        $koneksi,
        $periode_awal
    );

    $periode_akhir_sql = mysqli_real_escape_string(
        $koneksi,
        $periode_akhir
    );

    $where = "
        WHERE p.periode BETWEEN
        '$periode_awal_sql'
        AND
        '$periode_akhir_sql'
    ";
} elseif ($periode_awal !== '') {

    $periode_awal_sql = mysqli_real_escape_string(
        $koneksi,
        $periode_awal
    );

    $where = "
        WHERE p.periode >= '$periode_awal_sql'
    ";
} elseif ($periode_akhir !== '') {

    $periode_akhir_sql = mysqli_real_escape_string(
        $koneksi,
        $periode_akhir
    );

    $where = "
        WHERE p.periode <= '$periode_akhir_sql'
    ";
}


/* =========================================================
   QUERY DATA PERAMALAN
   AMBIL ID TERBARU SETIAP PERIODE
========================================================= */

$sql = "

    SELECT
        p.id_peramalan,
        p.periode,
        p.hasil_prediksi,
        p.nilai_mape,
        p.mad,
        p.mse,
        p.tanggal_proses

    FROM peramalan p

    INNER JOIN (

        SELECT
            periode,
            MAX(id_peramalan) AS id_terbaru

        FROM peramalan

        GROUP BY periode

    ) terbaru

    ON p.id_peramalan = terbaru.id_terbaru

    $where

    ORDER BY p.periode ASC

";


$query = mysqli_query($koneksi, $sql);


/* =========================================================
   CEK QUERY
========================================================= */

if (!$query) {

    // Bersihkan buffer
    if (ob_get_length()) {
        ob_end_clean();
    }

    die("Query laporan gagal: "
        . mysqli_error($koneksi));
}


/* =========================================================
   CEK APAKAH ADA OUTPUT SEBELUM PDF
   Untuk debugging
========================================================= */

if (headers_sent($output_file, $output_line)) {

    // Jangan tampilkan pesan debugging sebagai PDF
    if (ob_get_length()) {
        ob_end_clean();
    }

    die("Terjadi output sebelum PDF dikirim.<br>" .
        "File: " . htmlspecialchars($output_file) . "<br>" .
        "Baris: " . (int)$output_line);
}


/* =========================================================
   BUAT PDF
========================================================= */

$pdf = new FPDF('L', 'mm', 'A4');

$pdf->SetMargins(10, 10, 10);

$pdf->SetAutoPageBreak(true, 15);

$pdf->AddPage();


/* =========================================================
   JUDUL
========================================================= */

$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(
    0,
    10,
    'LAPORAN PERAMALAN PENJUALAN BUKET',
    0,
    1,
    'C'
);


/* =========================================================
   PERIODE
========================================================= */

$pdf->SetFont('Arial', '', 10);

if ($periode_awal !== '' && $periode_akhir !== '') {

    $periode_text =
        date(
            'F Y',
            strtotime($periode_awal . '-01')
        )
        . ' s/d '
        .
        date(
            'F Y',
            strtotime($periode_akhir . '-01')
        );
} elseif ($periode_awal !== '') {

    $periode_text =
        'Mulai ' .
        date(
            'F Y',
            strtotime($periode_awal . '-01')
        );
} elseif ($periode_akhir !== '') {

    $periode_text =
        'Sampai ' .
        date(
            'F Y',
            strtotime($periode_akhir . '-01')
        );
} else {

    $periode_text = 'Semua Periode';
}


$pdf->Cell(
    0,
    8,
    'Periode : ' . $periode_text,
    0,
    1,
    'C'
);

$pdf->Ln(5);


/* =========================================================
   HEADER TABEL
========================================================= */

$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell(
    12,
    8,
    'No',
    1,
    0,
    'C'
);

$pdf->Cell(
    38,
    8,
    'Periode',
    1,
    0,
    'C'
);

$pdf->Cell(
    42,
    8,
    'Hasil Prediksi',
    1,
    0,
    'C'
);

$pdf->Cell(
    38,
    8,
    'MAD',
    1,
    0,
    'C'
);

$pdf->Cell(
    38,
    8,
    'MSE',
    1,
    0,
    'C'
);

$pdf->Cell(
    35,
    8,
    'MAPE',
    1,
    0,
    'C'
);

$pdf->Cell(
    45,
    8,
    'Status',
    1,
    1,
    'C'
);


/* =========================================================
   DATA
========================================================= */

$pdf->SetFont('Arial', '', 9);

$no = 1;

$total_prediksi = 0;

$jumlah_data = 0;


while ($row = mysqli_fetch_assoc($query)) {

    /* =====================================================
       AMBIL NILAI
    ===================================================== */

    $mape = (float) $row['nilai_mape'];

    $prediksi = (float) $row['hasil_prediksi'];

    $mad = (float) $row['mad'];

    $mse = (float) $row['mse'];


    /* =====================================================
       TOTAL PREDIKSI
    ===================================================== */

    $total_prediksi += $prediksi;

    $jumlah_data++;


    /* =====================================================
       STATUS MAPE
    ===================================================== */

    if ($mape <= 10) {

        $status = 'Sangat Baik';
    } elseif ($mape <= 20) {

        $status = 'Baik';
    } elseif ($mape <= 50) {

        $status = 'Cukup';
    } else {

        $status = 'Kurang';
    }


    /* =====================================================
       FORMAT PERIODE
    ===================================================== */

    $periode_database = $row['periode'];

    $timestamp = strtotime(
        $periode_database . '-01'
    );

    if ($timestamp !== false) {

        $periode = date(
            'F Y',
            $timestamp
        );
    } else {

        $periode = $periode_database;
    }


    /* =====================================================
       CETAK NO
    ===================================================== */

    $pdf->Cell(
        12,
        8,
        $no,
        1,
        0,
        'C'
    );

    $no++;


    /* =====================================================
       CETAK PERIODE
    ===================================================== */

    $pdf->Cell(
        38,
        8,
        $periode,
        1,
        0,
        'C'
    );


    /* =====================================================
       CETAK HASIL PREDIKSI
    ===================================================== */

    $pdf->Cell(
        42,
        8,
        number_format(
            $prediksi,
            2,
            ',',
            '.'
        ) . ' Buket',
        1,
        0,
        'C'
    );


    /* =====================================================
       CETAK MAD
    ===================================================== */

    $pdf->Cell(
        38,
        8,
        number_format(
            $mad,
            2,
            ',',
            '.'
        ),
        1,
        0,
        'C'
    );


    /* =====================================================
       CETAK MSE
    ===================================================== */

    $pdf->Cell(
        38,
        8,
        number_format(
            $mse,
            2,
            ',',
            '.'
        ),
        1,
        0,
        'C'
    );


    /* =====================================================
       CETAK MAPE
    ===================================================== */

    $pdf->Cell(
        35,
        8,
        number_format(
            $mape,
            2,
            ',',
            '.'
        ) . '%',
        1,
        0,
        'C'
    );


    /* =====================================================
       CETAK STATUS
    ===================================================== */

    $pdf->Cell(
        45,
        8,
        $status,
        1,
        1,
        'C'
    );
}


/* =========================================================
   JIKA TIDAK ADA DATA
========================================================= */

if ($jumlah_data == 0) {

    $pdf->SetFont('Arial', '', 9);

    $pdf->Cell(
        248,
        10,
        'Tidak ada data peramalan pada periode yang dipilih.',
        1,
        1,
        'C'
    );
} else {

    /* =====================================================
       TOTAL PREDIKSI
    ===================================================== */

    $pdf->SetFont('Arial', 'B', 9);

    $pdf->Cell(
        50,
        8,
        'TOTAL PREDIKSI',
        1,
        0,
        'C'
    );

    $pdf->Cell(
        42,
        8,
        number_format(
            $total_prediksi,
            2,
            ',',
            '.'
        ) . ' Buket',
        1,
        0,
        'C'
    );

    $pdf->Cell(
        156,
        8,
        '',
        1,
        1
    );
}


/* =========================================================
   TANGGAL CETAK
========================================================= */

$pdf->Ln(8);

$pdf->SetFont('Arial', '', 9);

$pdf->Cell(
    0,
    6,
    'Dicetak pada : ' . date('d-m-Y H:i'),
    0,
    1,
    'R'
);


/* =========================================================
   BERSIHKAN OUTPUT BUFFER
   WAJIB SEBELUM FPDF OUTPUT
========================================================= */

if (ob_get_length()) {
    ob_end_clean();
}


/* =========================================================
   OUTPUT PDF
========================================================= */

$pdf->Output(
    'I',
    'Laporan-Peramalan.pdf'
);

exit;
