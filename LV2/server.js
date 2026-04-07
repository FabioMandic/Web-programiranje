const express = require('express');
const fs = require('fs');
const path = require('path');
const app = express();

const PORT = process.env.PORT || 3000;

app.set('view engine', 'ejs');
app.use(express.static('public'));

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// ZADATAK 3: Ruta za dinamičku galeriju
app.get('/slike', (req, res) => {
    const dataPath = path.join(__dirname, 'image.json');
    const rawData = fs.readFileSync(dataPath);
    const imagesData = JSON.parse(rawData);
    
    res.render('slike', { slike: imagesData });
});

app.listen(PORT, () => {
    console.log(`Server pokrenut! Provjeri: http://localhost:${PORT}/slike`);
});
