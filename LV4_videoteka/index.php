<?php
session_start();
if (!isset($_SESSION['prijavljen']) || $_SESSION['prijavljen'] !== true) {
    header("Location: prijava.php");
    exit();
}
// Spremamo ulogu u JS varijablu na stranici
echo "<script>const TRENUTNA_ULOGA = '" . $_SESSION['uloga'] . "';</script>";
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <title>Netflix Videoteka - LV4</title>
</head>
<body class="netflix-theme">
    <header>
        <div class="logo">NETFLIX <span class="sub-logo">Videoteka</span></div>
        <div class="nav-container">
            <button class="menu-btn">IZBORNIK</button>
            <nav class="dropdown-nav">
                <ul class="nav-menu">
                    <li><a href="index.php">Početna</a></li>
                    <li><a href="slike.php">Galerija</a></li>
                    <li><a href="grafikon.php">Grafikon</a></li>
                    <li><a href="odjava.php" style="color: white; margin-left: 15px; text-decoration: none;">Odjava</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="content">
        <section id="videoteka-sekcija">
            <h2>Popis filmova za posudbu</h2>
            
            <div id="filteri" class="filter-container">
                <div class="filter-group">
                    <label>Žanr:</label>
                    <select id="filter-genre">
                        <option value="">-- Svi žanrovi --</option>
                        <option value="Action">Action</option>
                        <option value="Comedy">Comedy</option>
                        <option value="Drama">Drama</option>
                        <option value="Crime">Crime</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Godina (od-do):</label>
                    <div class="input-row">
                        <input type="number" id="filter-year-from" placeholder="Od">
                        <input type="number" id="filter-year-to" placeholder="Do">
                    </div>
                </div>

                <div class="filter-group">
                    <label>Država:</label>
                    <input type="text" id="filter-country" placeholder="npr. USA">
                </div>

                <div class="filter-group">
                    <label>Min. ocjena: <span id="rating-value">0</span></label>
                    <input type="range" id="filter-rating" min="0" max="10" step="0.1" value="0">
                </div>

                <div class="filter-group">
                    <label>Sortiraj po:</label>
                    <select id="filter-sort">
                        <option value="naslov">Naslov (A-Z)</option>
                        <option value="godina">Godina (Uzlazno)</option>
                        <option value="ocjena">Ocjena (Najviša prva)</option>
                    </select>
                </div>
                
                <button id="btn-filtriraj" class="menu-btn">Filtriraj</button>
            </div>

            <table id="filmovi-tablica" class="netflix-table">
                <thead>
                    <tr>
                        <th>Naslov</th>
                        <th>Godina</th>
                        <th>Žanr</th>
                        <th>Trajanje</th>
                        <th>Država</th>
                        <th>Ocjena</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody id="filmovi-tablica-tbody"></tbody>
            </table>

            <section class="admin-section" style="display: none; margin-top: 40px; background: #141414; padding: 20px; border-radius: 8px; border: 1px solid #333;">
                <h3 id="admin-form-title" style="color: red; margin-bottom: 15px;">Dodaj novi film (Administracija)</h3>
                <form id="admin-form" style="display: flex; flex-direction: column; gap: 12px;">
                    <input type="hidden" name="film_id" id="admin-film-id" value="">
                    
                    <input type="text" name="naslov" id="admin-naslov" placeholder="Naslov filma" required style="padding: 10px; background: #333; color: white; border: none; border-radius: 4px; box-sizing: border-box;">
                    <input type="number" name="godina" id="admin-godina" placeholder="Godina proizvodnje (1888 - 2026)" required style="padding: 10px; background: #333; color: white; border: none; border-radius: 4px; box-sizing: border-box;">
                    <input type="text" name="zanr" id="admin-zanr" placeholder="Žanr (npr. Action, Drama)" required style="padding: 10px; background: #333; color: white; border: none; border-radius: 4px; box-sizing: border-box;">
                    <input type="number" name="trajanje" id="admin-trajanje" placeholder="Trajanje u minutama" required style="padding: 10px; background: #333; color: white; border: none; border-radius: 4px; box-sizing: border-box;">
                    <input type="text" name="drzava" id="admin-drzava" placeholder="Država (npr. USA, UK)" required style="padding: 10px; background: #333; color: white; border: none; border-radius: 4px; box-sizing: border-box;">
                    <input type="number" name="ocjena" id="admin-ocjena" placeholder="Ocjena (1.0 - 10.0)" min="1.0" max="10.0" step="0.1" required style="padding: 10px; background: #333; color: white; border: none; border-radius: 4px; box-sizing: border-box;">
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" id="admin-submit-btn" class="menu-btn" style="background: red; width: auto; padding: 10px 20px;">Dodaj film</button>
                        <button type="button" id="admin-cancel-btn" class="menu-btn" style="background: #555; display: none; width: auto; padding: 10px 20px;">Odustani</button>
                    </div>
                </form>
            </section>
        </section>

        <aside id="kosarica" class="sidebar-info">
            <div id="upozorenje-ocjena" style="display:none; background-color: #f8d7da; color: #721c24; padding: 12px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; font-weight: bold; box-sizing: border-box;"></div>
            
            <h3>Moja košarica</h3>
            <ul id="lista-kosarice"></ul>
            <button id="potvrdi-kosaricu" class="menu-btn" style="width: 100%; margin-top: 20px;">Potvrdi posudbu</button>
        </aside>
    </main>

    <script src="script.js"></script>
</body>
</html>