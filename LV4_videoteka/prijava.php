<?php
session_start();
$poruka = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = '127.0.0.1'; 
    $db   = 'videoteka'; 
    $user = 'root'; 
    $pass = '';
    
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");

    $korisnicko_ime = $conn->real_escape_string(trim($_POST['korisnicko_ime']));
    $lozinka = $_POST['lozinka'];

    $rezultat = $conn->query("SELECT * FROM korisnici WHERE korisnicko_ime='$korisnicko_ime'");

    if ($rezultat->num_rows == 1) {
        $korisnik = $rezultat->fetch_assoc();
        
        // Provjera hasha lozinke
        if (password_verify($lozinka, $korisnik['lozinka'])) {
            $_SESSION['prijavljen'] = true;
            $_SESSION['korisnicko_ime'] = $korisnik['korisnicko_ime'];
            $_SESSION['uloga'] = $korisnik['uloga']; // OVA LINIJA JE KLJUČNA ZA ULOGE
            header("Location: index.php");
            exit();
        } else {
            $poruka = "Pogrešna lozinka!";
        }
    } else {
        $poruka = "Korisnik ne postoji!";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Netflix - Prijava</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container { max-width: 400px; margin: 100px auto; background: #141414; padding: 30px; border-radius: 8px; color: white; border: 1px solid #333; }
        .auth-container input { width: 100%; padding: 10px; margin: 10px 0; background: #333; border: none; color: white; border-radius: 4px; box-sizing: border-box; }
        .auth-container button { width: 100%; padding: 10px; background: red; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .auth-container button:hover { background: darkred; }
    </style>
</head>
<body style="background-color: black; font-family: Arial, sans-serif;">
    <div class="auth-container">
        <h2 style="color: red; text-align: center;">PRIJAVA</h2>
        <?php if($poruka != "") echo "<p style='color: yellow; text-align: center;'>$poruka</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="korisnicko_ime" placeholder="Korisničko ime" required>
            <input type="password" name="lozinka" placeholder="Lozinka" required>
            <button type="submit">Prijavi se</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">Nemaš račun? <a href="registracija.php" style="color: red;">Registriraj se</a></p>
    </div>
</body>
</html>