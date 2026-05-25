let sviFilmovi = [];

document.addEventListener('DOMContentLoaded', () => {
    ucitajPodatke();
    osvjeziKosaricu();

    document.getElementById('btn-filtriraj').addEventListener('click', primijeniFiltre);

    document.getElementById('filter-rating').addEventListener('input', (e) => {
        document.getElementById('rating-value').textContent = e.target.value;
    });

    // DODANO: Slušanje klika na gumb za potvrdu posudbe
    const btnPotvrdi = document.getElementById('potvrdi-kosaricu');
    if (btnPotvrdi) {
        btnPotvrdi.addEventListener('click', potvrdiPosudbu);
    }

    const adminSekcija = document.querySelector('.admin-section');
    if (adminSekcija) {
        if (typeof TRENUTNA_ULOGA !== 'undefined' && TRENUTNA_ULOGA === 'admin') {
            adminSekcija.style.display = 'block';
        } else {
            adminSekcija.style.display = 'none';
        }
    }

    // Otkazivanje načina rada za uređivanje
    document.getElementById('admin-cancel-btn').addEventListener('click', ponistiUredivanje);

    const adminForm = document.getElementById('admin-form');
    if (adminForm) {
        adminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(adminForm);
            
            // Provjera radimo li običan unos ili ažuriranje
            const filmId = document.getElementById('admin-film-id').value;
            const endpoint = filmId ? 'filmovi.php?akcija=uredi_film' : 'filmovi.php?akcija=dodaj_film';

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    alert(data.success);
                    ponistiUredivanje();
                    ucitajPodatke(); 
                }
            })
            .catch(err => console.error("Greška pri spremanju filma:", err));
        });
    }
});

function ucitajPodatke(urlParametri = '') {
    fetch('filmovi.php?akcija=dohvati' + urlParametri)
        .then(res => res.json())
        .then(data => {
            sviFilmovi = data;
            prikaziTablicu(sviFilmovi); 
        })
        .catch(err => console.error("Greška pri dohvaćanju iz baze:", err));
}

function prikaziTablicu(podatci) {
    const tbody = document.getElementById('filmovi-tablica-tbody');
    tbody.innerHTML = ''; 

    if (podatci.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8">Nema rezultata za odabrane filtre.</td></tr>'; 
        return;
    }

    podatci.forEach(film => {
        const row = document.createElement('tr'); 
        
        let akcijaGumb = `<div style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
            <button class="btn-dodaj" style="width: 100%; min-width: 80px;" onclick="dodajUKosaricu(${film.id})">Dodaj</button>`;
            
        if (typeof TRENUTNA_ULOGA !== 'undefined' && TRENUTNA_ULOGA === 'admin') {
            akcijaGumb += `<button class="btn-uredi" style="background-color: #f0ad4e; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; width: 100%; min-width: 80px;" onclick="pokreniUredivanje(${film.id})">Uredi</button>`;
            akcijaGumb += `<button class="btn-obrisi" style="background-color: #d9534f; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; width: 100%; min-width: 80px;" onclick="obrisiFilmIzBaze(${film.id})">Obriši</button>`;
        }
        
        akcijaGumb += `</div>`;

        row.innerHTML = `
            <td>${film.naslov}</td>
            <td>${film.godina}</td>
            <td>${film.zanr}</td>
            <td>${film.trajanje} min</td>
            <td>${film.drzava}</td>
            <td>${film.ocjena}</td>
            <td>${akcijaGumb}</td>
        `;
        tbody.appendChild(row); 
    });
}

function primijeniFiltre() {
    const zanr = document.getElementById('filter-genre').value;
    const godinaOd = document.getElementById('filter-year-from').value;
    const godinaDo = document.getElementById('filter-year-to').value;
    const drzava = document.getElementById('filter-country').value.trim();
    const minOcjena = document.getElementById('filter-rating').value;
    const sortiraj = document.getElementById('filter-sort').value; 

    let urlParametri = '';
    if (zanr && zanr !== '-- Svi žanrovi --') urlParametri += `&zanr=${encodeURIComponent(zanr)}`;
    if (godinaOd) urlParametri += `&godinaOd=${godinaOd}`;
    if (godinaDo) urlParametri += `&godinaDo=${godinaDo}`;
    if (drzava) urlParametri += `&drzava=${encodeURIComponent(drzava)}`;
    if (minOcjena) urlParametri += `&minOcjena=${minOcjena}`;
    if (sortiraj) urlParametri += `&sort=${sortiraj}`; 

    ucitajPodatke(urlParametri);
}

window.pokreniUredivanje = function(filmId) {
    const film = sviFilmovi.find(f => f.id == filmId);
    if (!film) return;

    document.getElementById('admin-film-id').value = film.id;
    document.getElementById('admin-naslov').value = film.naslov;
    document.getElementById('admin-godina').value = film.godina;
    document.getElementById('admin-zanr').value = film.zanr;
    document.getElementById('admin-trajanje').value = film.trajanje;
    document.getElementById('admin-drzava').value = film.drzava;
    document.getElementById('admin-ocjena').value = film.ocjena;

    document.getElementById('admin-form-title').textContent = "Uredi film (Administracija)";
    document.getElementById('admin-submit-btn').textContent = "Spremi promjene";
    document.getElementById('admin-cancel-btn').style.display = "inline-block";
    
    document.getElementById('admin-form').scrollIntoView({ behavior: 'smooth' });
};

function ponistiUredivanje() {
    document.getElementById('admin-form').reset();
    document.getElementById('admin-film-id').value = "";
    document.getElementById('admin-form-title').textContent = "Dodaj novi film (Administracija)";
    document.getElementById('admin-submit-btn').textContent = "Dodaj film";
    document.getElementById('admin-cancel-btn').style.display = "none";
}

window.obrisiFilmIzBaze = function(filmId) {
    if (confirm("Jeste li sigurni da želite trajno obrisati ovaj film iz videoteke?")) {
        fetch(`filmovi.php?akcija=obrisi_film&id=${filmId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    ucitajPodatke();
                    osvjeziKosaricu();
                } else {
                    alert(data.error);
                }
            });
    }
};

window.dodajUKosaricu = function(filmId, potvrdaNiskeOcjene = 'ne') {
    const upzDiv = document.getElementById('upozorenje-ocjena');
    if (upzDiv) upzDiv.style.display = 'none';

    fetch(`filmovi.php?akcija=kosarica_dodaj&film_id=${filmId}&potvrda=${potvrdaNiskeOcjene}`)
        .then(res => res.json())
        .then(data => {
            if (data.warning) {
                if (upzDiv) {
                    upzDiv.innerHTML = `${data.warning} <br><button onclick="dodajUKosaricu(${filmId}, 'da')" style="background: red; color: white; border: none; padding: 5px 10px; margin-top: 5px; border-radius: 4px; cursor: pointer; font-weight: bold;">Da, dodaj ga unatoč ocjeni</button>`;
                    upzDiv.style.display = 'block';
                } else {
                    if (confirm(data.warning)) {
                        dodajUKosaricu(filmId, 'da');
                    }
                }
            } else if (data.error) {
                alert("Film je već u vašoj osobnoj videoteci!");
            } else {
                alert(data.success);
                osvjeziKosaricu();
            }
        });
};

function osvjeziKosaricu() {
    fetch('filmovi.php?akcija=kosarica_dohvati')
        .then(res => res.json())
        .then(data => {
            const lista = document.getElementById('lista-kosarice');
            if (!lista) return;
            lista.innerHTML = ''; 

            if (data.length === 0) {
                lista.innerHTML = '<li style="color: #aaa; list-style: none; padding: 10px;">Vaša košarica je prazna.</li>';
                return;
            }

            data.forEach(film => {
                const li = document.createElement('li');
                li.innerHTML = `<span>${film.naslov} (${film.ocjena})</span> <button onclick="ukloniIzKosarice(${film.id})">❌</button>`;
                lista.appendChild(li); 
            });
        });
}

window.ukloniIzKosarice = function(filmId) {
    fetch(`filmovi.php?akcija=kosarica_ukloni&film_id=${filmId}`)
        .then(res => res.json())
        .then(data => {
            osvjeziKosaricu();
        });
};

// DODANO: Funkcija za završnu potvrdu posudbe iz košarice
function potvrdiPosudbu() {
    const lista = document.getElementById('lista-kosarice');
    const stavke = lista.getElementsByTagName('li');
    
    if (stavke.length === 0 || lista.textContent.includes("Vaša košarica je prazna")) {
        alert("Vaša košarica je prazna! Dodajte filmove prije potvrde posudbe.");
    } else {
        alert(`Uspješno ste potvrdili posudbu za ${stavke.length} filma! Vaš vikend maraton može početi`);
    }
}