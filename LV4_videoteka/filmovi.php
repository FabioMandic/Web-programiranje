<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$host = '127.0.0.1';
$db   = 'videoteka';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["error" => "Greška pri spajanju na bazu: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

function getKorisnikId($conn) {
    if (!isset($_SESSION['korisnicko_ime'])) return null;
    $k_ime = $_SESSION['korisnicko_ime'];
    $res = $conn->query("SELECT id FROM korisnici WHERE korisnicko_ime='$k_ime'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        return $row['id'];
    }
    return null;
}

$akcija = isset($_GET['akcija']) ? $_GET['akcija'] : 'dohvati';

// --------------------------- ACTION: DOHVATI, FILTRIRAJ I SORTIRAJ ---------------------------
if ($akcija === 'dohvati') {
    $sql = "SELECT * FROM filmovi WHERE 1=1";
    
    if (isset($_GET['zanr']) && $_GET['zanr'] !== '-- Svi žanrovi --' && !empty($_GET['zanr'])) {
        $zanr = $conn->real_escape_string($_GET['zanr']);
        $sql .= " AND zanr LIKE '%$zanr%'";
    }
    if (isset($_GET['godinaOd']) && !empty($_GET['godinaOd'])) {
        $godinaOd = (int)$_GET['godinaOd'];
        $sql .= " AND godina >= $godinaOd";
    }
    if (isset($_GET['godinaDo']) && !empty($_GET['godinaDo'])) {
        $godinaDo = (int)$_GET['godinaDo'];
        $sql .= " AND godina <= $godinaDo";
    }
    if (isset($_GET['drzava']) && !empty($_GET['drzava'])) {
        $drzava = $conn->real_escape_string($_GET['drzava']);
        $sql .= " AND drzava LIKE '%$drzava%'";
    }
    if (isset($_GET['minOcjena']) && !empty($_GET['minOcjena'])) {
        $minOcjena = (float)$_GET['minOcjena'];
        $sql .= " AND ocjena >= $minOcjena";
    }

    // DODANO SQL SORTIRANJE NA SERVERSKOJ STRANI
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'naslov';
    if ($sort === 'godina') {
        $sql .= " ORDER BY godina ASC";
    } elseif ($sort === 'ocjena') {
        $sql .= " ORDER BY ocjena DESC";
    } else {
        $sql .= " ORDER BY naslov ASC";
    }

    $result = $conn->query($sql);
    $filmovi = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { $filmovi[] = $row; }
    }
    echo json_encode($filmovi);
}

// --------------------------- ACTION: UNOS I UREĐIVANJE FILMA (ADMIN) ---------------------------
if ($akcija === 'dodaj_film' || $akcija === 'uredi_film') {
    if (!isset($_SESSION['uloga']) || $_SESSION['uloga'] !== 'admin') {
        die(json_encode(["error" => "Nemate ovlasti za ovu radnju."]));
    }

    $naslov = trim($_POST['naslov'] ?? '');
    $godina = (int)($_POST['godina'] ?? 0);
    $zanr = trim($_POST['zanr'] ?? '');
    $trajanje = (int)($_POST['trajanje'] ?? 0);
    $drzava = trim($_POST['drzava'] ?? '');
    $ocjena = (float)($_POST['ocjena'] ?? 0.0);

    if (empty($naslov) || empty($zanr) || empty($drzava)) {
        die(json_encode(["error" => "Sva polja moraju biti ispunjena!"]));
    }
    if ($godina < 1888 || $godina > 2026) {
        die(json_encode(["error" => "Godina mora biti u rasponu od 1888 do 2026!"]));
    }
    if ($trajanje <= 0 || $trajanje > 500) {
        die(json_encode(["error" => "Trajanje filma mora biti između 1 i 500 minuta!"]));
    }
    if ($ocjena < 1.0 || $ocjena > 10.0) {
        die(json_encode(["error" => "Ocjena mora biti u rasponu od 1.0 do 10.0!"]));
    }

    $naslov = $conn->real_escape_string($naslov);
    $zanr = $conn->real_escape_string($zanr);
    $drzava = $conn->real_escape_string($drzava);

    if ($akcija === 'dodaj_film') {
        $sql = "INSERT INTO filmovi (naslov, godina, zanr, trajanje, drzava, ocjena) VALUES ('$naslov', $godina, '$zanr', $trajanje, '$drzava', $ocjena)";
    } else {
        // DODANA LOGIKA ZA SQL UPDATE (UREĐIVANJE)
        $id = (int)$_POST['film_id'];
        $sql = "UPDATE filmovi SET naslov='$naslov', godina=$godina, zanr='$zanr', trajanje=$trajanje, drzava='$drzava', ocjena=$ocjena WHERE id=$id";
    }
    
    if ($conn->query($sql)) {
        echo json_encode(["success" => "Podaci su uspješno spremljeni!"]);
    } else {
        echo json_encode(["error" => "Greška pri radu s bazom: " . $conn->error]);
    }
}

// --------------------------- ACTION: BRISANJE FILMA (ADMIN) ---------------------------
if ($akcija === 'obrisi_film') {
    if (!isset($_SESSION['uloga']) || $_SESSION['uloga'] !== 'admin') {
        die(json_encode(["error" => "Nemate ovlasti za ovu radnju."]));
    }
    $id = (int)$_GET['id'];
    if ($conn->query("DELETE FROM filmovi WHERE id = $id")) {
        echo json_encode(["success" => "Film uspješno obrisan!"]);
    } else {
        echo json_encode(["error" => "Greška pri brisanju: " . $conn->error]);
    }
}

// --------------------------- ACTION: DODAJ U KOŠARICU (TRAJNO) ---------------------------
if ($akcija === 'kosarica_dodaj') {
    $korisnik_id = getKorisnikId($conn);
    $film_id = (int)$_GET['film_id'];

    if (!$korisnik_id || !$film_id) {
        die(json_encode(["error" => "Nevažeći zahtjev."]));
    }

    $filmRes = $conn->query("SELECT ocjena FROM filmovi WHERE id = $film_id");
    $film = $filmRes->fetch_assoc();
    $niska_ocjena = ($film['ocjena'] < 5.0) ? true : false;

    $potvrđeno = isset($_GET['potvrda']) && $_GET['potvrda'] === 'da';
    
    if ($niska_ocjena && !$potvrđeno) {
        die(json_encode(["warning" => "Ovaj film ima nisku ocjenu ({$film['ocjena']}) – jeste li sigurni da ga želite dodati?"]));
    }

    $sql = "INSERT IGNORE INTO zeljeni_filmovi (korisnik_id, film_id) VALUES ($korisnik_id, $film_id)";
    if ($conn->query($sql)) {
        echo json_encode(["success" => "Film dodan u osobnu videoteku!"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}

// --------------------------- ACTION: DOHVATI KOŠARICU ---------------------------
if ($akcija === 'kosarica_dohvati') {
    $korisnik_id = getKorisnikId($conn);
    if (!$korisnik_id) die(json_encode([]));

    $sql = "SELECT f.* FROM filmovi f 
            JOIN zeljeni_filmovi zf ON f.id = zf.film_id 
            WHERE zf.korisnik_id = $korisnik_id";
    $result = $conn->query($sql);
    $kosarica = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { $kosarica[] = $row; }
    }
    echo json_encode($kosarica);
}

// --------------------------- ACTION: UKLONI IZ KOŠARICE ---------------------------
if ($akcija === 'kosarica_ukloni') {
    $korisnik_id = getKorisnikId($conn);
    $film_id = (int)$_GET['film_id'];

    if ($conn->query("DELETE FROM zeljeni_filmovi WHERE korisnik_id = $korisnik_id AND film_id = $film_id")) {
        echo json_encode(["success" => "Film uklonjen iz košarice!"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}

$conn->close();
?>