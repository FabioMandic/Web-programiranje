<?php 
session_start(); 
if (!isset($_SESSION['prijavljen']) || $_SESSION['prijavljen'] !== true) {
    header("Location: prijava.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style_slike.css">
    <title>Netflix - Galerija Slika</title>
    <style>
        .rating-stars { display: flex; gap: 5px; margin: 10px 0; justify-content: center; }
        .rating-stars span { font-size: 24px; cursor: pointer; color: #444; transition: color 0.2s; }
        .rating-stars span.active, .rating-stars span:hover { color: #f1c40f; }
        .prosjek-info { font-size: 0.85rem; color: #e50914; font-weight: bold; margin-top: 4px; text-align: center; }
        .upload-section { max-width: 600px; margin: 40px auto; background: #181818; padding: 25px; border-radius: 8px; border: 1px solid #333; text-align: left; }
        .upload-section input, .upload-section textarea { width: 100%; padding: 10px; margin: 10px 0; background: #333; border: none; color: white; border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body class="netflix-theme">
    <header>
        <div class="logo">NETFLIX <span class="sub-logo">Gallery</span></div>
        <div class="nav-container">
            <button class="menu-btn">IZBORNIK</button>
            <nav role="navigation" aria-label="Glavna navigacija" class="dropdown-nav">
                <ul class="nav-menu">
                    <li><a href="index.php">Početna</a></li>
                    <li><a href="slike.php">Galerija</a></li>
                    <li><a href="grafikon.php">Grafikon</a></li>
                    <li><a href="odjava.php" style="color: #E50914; font-weight: bold;">Odjava</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="galerija"> 
            <h1>Galerija slika i ocjenjivanje</h1>
            
            <div id="dinamicka-galerija" class="img-gallery-magnific"></div>
        </section>

        <section class="upload-section">
            <h3 style="color: #E50914; margin-top: 0;">Dodaj novu fotografiju</h3>
            <form id="upload-slika-form" enctype="multipart/form-data">
                <label>Odaberi sliku (JPEG, PNG - maksimalno 5MB):</label>
                <input type="file" name="slika_datoteka" accept="image/jpeg, image/png" required>
                
                <label>Kratak opis slike:</label>
                <textarea name="opis" rows="3" placeholder="Unesite opis fotografije..." required style="width: 100%; background: #333; color: white; border: none; border-radius: 4px; padding: 10px; box-sizing: border-box; resize: vertical; font-family: Arial, sans-serif;"></textarea>
                
                <button type="submit" class="menu-btn" style="margin-top: 10px; width: 100%;">Učitaj sliku na server</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2026. Web Programiranje - LV4</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            ucitajSlike();

            const uploadForm = document.getElementById("upload-slika-form");
            if (uploadForm) {
                uploadForm.addEventListener("submit", (e) => {
                    e.preventDefault();
                    const formData = new FormData(uploadForm);

                    fetch("slike_operacije.php?akcija=dodaj_sliku", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            alert(data.success);
                            uploadForm.reset();
                            ucitajSlike(); 
                        }
                    })
                    .catch(err => console.error("Greška pri uploadu:", err));
                });
            }
        });

        function ucitajSlike() {
            fetch("slike_operacije.php?akcija=dohvati")
                .then(res => res.json())
                .then(data => {
                    generirajGaleriju(data);
                })
                .catch(err => console.error("Greška pri dohvaćanju slika:", err));
        }

        function generirajGaleriju(slike) {
            const kontejner = document.getElementById("dinamicka-galerija");
            kontejner.innerHTML = "";

            if (slike.length === 0) {
                kontejner.innerHTML = "<p style='color: #aaa; text-align: center; width: 100%;'>Galerija je trenutno prazna.</p>";
                return;
            }

            slike.forEach(slika => {
                const figure = document.createElement("figure");
                figure.className = "galerija_slika magnific-img";

                let zvjezdiceHtml = `<div class="rating-stars" data-id="${slika.id}">`;
                for (let i = 1; i <= 5; i++) {
                    const klasa = (i <= slika.moja_ocjena) ? "active" : "";
                    zvjezdiceHtml += `<span class="${klasa}" onclick="ocjeniSliku(${slika.id}, ${i})">&#9733;</span>`;
                }
                zvjezdiceHtml += `</div>`;

                // IZMJENA: Maknut je 'slika.naziv_datoteke' (random string), opis ('slika.opis') je postavljen kao glavni naslov
                figure.innerHTML = `
                    <a href="#img-lightbox-${slika.id}">
                        <img src="${slika.putanja}" alt="${slika.opis}" loading="lazy">
                    </a>
                    <figcaption>
                        <strong>${slika.opis}</strong>
                        <div class="prosjek-info">★ Prosjek: ${parseFloat(slika.prosjecna_ocjena).toFixed(1)} (${slika.broj_glasova} glasova)</div>
                        ${zvjezdiceHtml}
                    </figcaption>
                `;

                const lightbox = document.createElement("div");
                lightbox.id = `img-lightbox-${slika.id}`;
                lightbox.className = "lightbox";
                lightbox.innerHTML = `
                    <a href="#" class="close">&times;</a>
                    <img src="${slika.putanja}" alt="${slika.opis}">
                `;

                kontejner.appendChild(figure);
                kontejner.appendChild(lightbox);
            });
        }

        function ocjeniSliku(idSlika, ocjenaVrijednost) {
            const params = new URLSearchParams();
            params.append("id_slika", idSlika);
            params.append("ocjena", ocjenaVrijednost);

            fetch("slike_operacije.php?akcija=ocjeni", {
                method: "POST",
                body: params
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    ucitajSlike(); 
                }
            })
            .catch(err => console.error("Greška pri slanju ocjene:", err));
        }
    </script>
</body>
</html>