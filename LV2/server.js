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
    const dataPath = path.join(__dirname, 'images.json');
    const data = JSON.parse(fs.readFileSync(dataPath, 'utf8'));
    res.render('slike', { slike: data });
});

app.listen(PORT, () => {
    console.log(`Server radi! Galerija je na: http://localhost:${PORT}/slike`);
});
