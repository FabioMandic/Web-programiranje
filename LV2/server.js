const express = require('express');
const app = express();

// Postavljanje porta za lokalni rad i Railway [cite: 584, 627]
const PORT = process.env.PORT || 3000; 

// Posluživanje statičkih datoteka iz mape 'public' [cite: 237, 543]
app.use(express.static('public')); 

app.listen(PORT, () => {
    // Ispis klikabilnog linka u terminalu
    console.log(`Server pokrenut! Klikni ovdje: http://localhost:${PORT}`);
});