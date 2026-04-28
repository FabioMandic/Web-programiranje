let sviFilmovi = [];
let kosarica = [];

document.addEventListener('DOMContentLoaded', () => {
    // Inicijalno učitavanje podataka 
    ucitajPodatke();

    // Povezivanje gumba za filtriranje 
    document.getElementById('btn-filtriraj').addEventListener('click', primijeniFiltre);

    // Dinamički prikaz vrijednosti slidera 
    document.getElementById('filter-rating').addEventListener('input', (e) => {
        document.getElementById('rating-value').textContent = e.target.value;
    });

    // Potvrda košarice 
    document.getElementById('potvrdi-kosaricu').addEventListener('click', potvrdiPosudbu);
});

function ucitajPodatke() {
    fetch('movies.csv')
        .then(res => res.text())
        .then(csvText => {
            const rezultat = Papa.parse(csvText, {
                header: true,
                skipEmptyLines: true
            });

            // Mapiranje prema tvojim stupcima: Naslov, Godina, Zanr, Trajanje_min, Ocjena, Zemlja_porijekla
            sviFilmovi = rezultat.data.map(film => ({
                title: film.Naslov,
                year: parseInt(film.Godina), 
                genre: film.Zanr || "",
                duration: film.Trajanje_min,
                // Zemlje u tvom CSV-u su odvojene kosom crtom /
                countries: film.Zemlja_porijekla ? film.Zemlja_porijekla.split('/').map(c => c.trim()) : [],
                rating: parseFloat(film.Ocjena) 
            }));

            prikaziTablicu(sviFilmovi.slice(0, 15)); 
        })
        .catch(err => console.error("Greška:", err));
}

function prikaziTablicu(podatci) {
    const tbody = document.getElementById('filmovi-tablica-tbody');
    tbody.innerHTML = ''; 

    if (podatci.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">Nema rezultata za odabrane filtre.</td></tr>'; 
        return;
    }

    podatci.forEach(film => {
        const row = document.createElement('tr'); 
        row.innerHTML = `
            <td>${film.title}</td>
            <td>${film.year}</td>
            <td>${film.genre}</td>
            <td>${film.duration} min</td>
            <td>${film.countries.join(', ')}</td>
            <td>${film.rating}</td>
            <td><button class="btn-dodaj" onclick="dodajUKosaricu('${film.title.replace(/'/g, "\\'")}')">Dodaj</button></td>
        `;
        tbody.appendChild(row); 
    });
}

function primijeniFiltre() {
    const zanr = document.getElementById('filter-genre').value.toLowerCase();
    const godinaOd = parseInt(document.getElementById('filter-year-from').value) || 0;
    const godinaDo = parseInt(document.getElementById('filter-year-to').value) || 2026;
    const drzava = document.getElementById('filter-country').value.trim().toLowerCase();
    const minOcjena = parseFloat(document.getElementById('filter-rating').value) || 0;

    const filtrirani = sviFilmovi.filter(film => {
        const matchZanr = !zanr || film.genre.toLowerCase().includes(zanr); 
        const matchGodina = film.year >= godinaOd && film.year <= godinaDo; 
        const matchDrzava = !drzava || film.countries.some(c => c.toLowerCase().includes(drzava)); 
        const matchOcjena = film.rating >= minOcjena; 

        return matchZanr && matchGodina && matchDrzava && matchOcjena;
    });

    prikaziTablicu(filtrirani); 
}



window.dodajUKosaricu = function(naslov) {
    if (!kosarica.includes(naslov)) {
        kosarica.push(naslov); 
        osvjeziKosaricu();
    } else {
        alert("Film je već u košarici!"); 
    }
};

function osvjeziKosaricu() {
    const lista = document.getElementById('lista-kosarice');
    lista.innerHTML = ''; 
    kosarica.forEach((naslov, index) => {
        const li = document.createElement('li');
        li.innerHTML = `<span>${naslov}</span> <button onclick="ukloniIzKosarice(${index})">❌</button>`;
        lista.appendChild(li); 
    });
}

window.ukloniIzKosarice = function(index) {
    kosarica.splice(index, 1); 
    osvjeziKosaricu();
};

function potvrdiPosudbu() {
    if (kosarica.length > 0) {
        alert(`Uspješno ste dodali ${kosarica.length} filma u svoju košaricu za vikend maraton!`); 
        osvjeziKosaricu();
    } else {
        alert("Košarica je prazna!"); 
    }
}