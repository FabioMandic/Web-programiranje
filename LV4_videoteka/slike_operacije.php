<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['prijavljen']) || $_SESSION['prijavljen'] !== true) {
    die(json_encode(["error" => "Niste prijavljeni."]));
}

$host = '127.0.0.1'; $db = 'videoteka'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["error" => "Veza s bazom nije uspjela."]));
}
$conn->set_charset("utf8mb4");

// Pomoćna funkcija za dohvat ID-a korisnika preko prepared statementa
function dohvatiKorisnikId($conn, $korisnicko_ime) {
    $stmt = $conn->prepare("SELECT id FROM korisnici WHERE korisnicko_ime = ?");
    $stmt->bind_param("s", $korisnicko_ime);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['id'];
    }
    return null;
}

$id_korisnik = dohvatiKorisnikId($conn, $_SESSION['korisnicko_ime']);
$akcija = $_GET['akcija'] ?? 'dohvati';

// --------------------------- AKCIJA: DOHVATI SLIKE I PROSJEČNE OCJENE ---------------------------
if ($akcija === 'dohvati') {
    $query = "SELECT s.*, 
                     IFNULL(ROUND(AVG(o.ocjena), 1), 0.0) AS prosjecna_ocjena,
                     COUNT(o.id) AS broj_glasova,
                     IFNULL((SELECT ocjena FROM ocjene WHERE id_slika = s.id AND id_korisnik = ?), 0) AS moja_ocjena
              FROM slike s
              LEFT JOIN ocjene o ON s.id = o.id_slika
              GROUP BY s.id";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_korisnik);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $slike = [];
    while ($row = $result->fetch_assoc()) {
        $slike[] = $row;
    }
    echo json_encode($slike);
}

// --------------------------- AKCIJA: OCJENI SLIKU (1 - 5) ---------------------------
if ($akcija === 'ocjeni') {
    $id_slika = (int)($_POST['id_slika'] ?? 0);
    $ocjena = (int)($_POST['ocjena'] ?? 0);

    if ($ocjena < 1 || $ocjena > 5 || $id_slika <= 0) {
        die(json_encode(["error" => "Neispravna ocjena ili ID slike."]));
    }

    // Unos nove ocjene ili ažuriranje postojeće ako već postoji (ON DUPLICATE KEY UPDATE)
    $stmt = $conn->prepare("INSERT INTO ocjene (id_korisnik, id_slika, ocjena) VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE ocjena = ?");
    $stmt->bind_param("iiii", $id_korisnik, $id_slika, $ocjena, $ocjena);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => "Ocjena uspješno spremljena!"]);
    } else {
        echo json_encode(["error" => "Greška pri spremanju ocjene."]);
    }
}

// --------------------------- AKCIJA: DODAJ NOVU SLIKU (UPLOAD + VALIDACIJA) ---------------------------
if ($akcija === 'dodaj_sliku') {
    $opis = trim($_POST['opis'] ?? '');
    
    if (!isset($_FILES['slika_datoteka']) || $_FILES['slika_datoteka']['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(["error" => "Greška pri prijenosu datoteke."]));
    }

    $file = $_FILES['slika_datoteka'];
    $max_size = 5 * 1024 * 1024; // 5 MB validacija (Traženo u PDF-u)
    
    // 1. Validacija veličine datoteke
    if ($file['size'] > $max_size) {
        die(json_encode(["error" => "Datoteka je prevelika! Maksimalna dopuštena veličina je 5MB."]));
    }

    // 2. Validacija formata (JPEG / PNG)
    $dozvoljeni_tipovi = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $dozvoljeni_tipovi)) {
        die(json_encode(["error" => "Nedozvoljeni format! Dopušteni formati su samo JPEG i PNG."]));
    }

    // Stvaranje uploads foldera ako ne postoji
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Generiranje jedinstvenog imena datoteke radi sigurnosti
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $novi_naziv = uniqid("img_", true) . "." . $ext;
    $target_file = $target_dir . $novi_naziv;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Upis u bazu preko prepared statementa
        $stmt = $conn->prepare("INSERT INTO slike (naziv_datoteke, opis, putanja, izvor) VALUES (?, ?, ?, 'lokalno')");
        $stmt->bind_param("sss", $file['name'], $opis, $target_file);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => "Slika uspješno dodana u galeriju!"]);
        } else {
            echo json_encode(["error" => "Greška pri spremanju u bazu podataka."]);
        }
    } else {
        echo json_encode(["error" => "Neuspješno micanje datoteke na serveru."]);
    }
}

$conn->close();
?>