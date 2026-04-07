const express = require('express');
const app = express();
const path = require('path');

// Railway dinamički dodjeljuje port 
const PORT = process.env.PORT || 3000; 

// Posluživanje statičkih datoteka iz mape 'public' [cite: 237, 543]
app.use(express.static('public')); 

app.listen(PORT, () => {
    console.log(`Server pokrenut na portu ${PORT}`);
});
