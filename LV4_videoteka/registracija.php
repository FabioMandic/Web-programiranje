<?php
$poruka = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = '127.0.0.1'; $db = 'videoteka'; $user = 'root'; $pass = '';
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");

    $ime = $conn->real_escape_string(trim($_POST['ime']));
    $prezime = $conn->real_escape_string(trim($_POST['prezime']));
    $korisnicko_ime = $conn->real_escape_string(trim($_POST['korisnicko_ime']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $lozinka = $_POST['lozinka'];
    $ponovljena_lozinka = $_POST['ponovljena_lozinka'];

    // 1. Provjera podudaranja lozinki
    if ($lozinka !== $ponovljena_lozinka) {
        $poruka = "Lozinke se ne podudaraju!";
    } else {
        // 2. Provjera postoji li već korisničko ime ili email u bazi
        $provjera = $conn->query("SELECT * FROM korisnici WHERE korisnicko_ime='$korisnicko_ime' OR email='$email'");
        
        if ($provjera->num_rows > 0) {
            $poruka = "Korisničko ime ili e-mail već postoje u bazi!";
        } else {
            // 3. Hashiranje lozinke i unos u bazu
            $hash_lozinka = password_hash($lozinka, PASSWORD_DEFAULT);
            $sql = "INSERT INTO korisnici (ime, prezime, korisnicko_ime, email, lozinka) 
                    VALUES ('$ime', '$prezime', '$korisnicko_ime', '$email', '$hash_lozinka')";
            
            if ($conn->query($sql)) {
                $poruka = "Uspješna registracija! Možete se <a href='prijava.php' style='color:red;'>prijaviti ovdje</a>.";
            } else {
                $poruka = "Greška na serveru: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Netflix - Registracija</title>
    <link rel="stylesheet" href="style.css"> <style>
        .auth-container { max-width: 400px; margin: 100px auto; background: #141414; padding: 30px; border-radius: 8px; color: white; border: 1px solid #333; }
        .auth-container input { width: 100%; padding: 10px; margin: 10px 0; background: #333; border: none; color: white; border-radius: 4px; box-sizing: border-box; }
        .auth-container button { width: 100%; padding: 10px; background: red; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .auth-container button:hover { background: darkred; }
    </style>
</head>
<body style="background-color: black; font-family: Arial, sans-serif;">
    <div class="auth-container">
        <h2 style="color: red; text-align: center;">REGISTRACIJA</h2>
        <?php if($poruka != "") echo "<p style='color: yellow; text-align: center;'>$poruka</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="ime" placeholder="Ime" required>
            <input type="text" name="prezime" placeholder="Prezime" required>
            <input type="text" name="korisnicko_ime" placeholder="Korisničko ime" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="lozinka" placeholder="Lozinka" required>
            <input type="password" name="ponovljena_lozinka" placeholder="Ponovi lozinku" required>
            <button type="submit">Registriraj se</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">Imaš račun? <a href="prijava.php" style="color: red;">Prijavi se</a></p>
    </div>
</body>
</html>