<?php
header('Content-Type: application/json'); 

// URL API BPS spesifik yang sudah dites di Postman
$url = "https://webapi.bps.go.id/v1/api/domain/type/all/prov/00000/key/52633aaa635a98a9631e14167a1a9fd4/";

// ambil data
$response = file_get_contents($url);

// cek error
if ($response === FALSE) {
    echo json_encode(["error" => "Gagal mengambil data"]);
    exit;
}

// kirim ke frontend
echo $response;
?>