-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 25, 2026 at 02:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `videoteka`
--

-- --------------------------------------------------------

--
-- Table structure for table `filmovi`
--

CREATE TABLE `filmovi` (
  `id` int(11) NOT NULL,
  `naslov` varchar(255) NOT NULL,
  `godina` int(11) NOT NULL,
  `zanr` varchar(255) NOT NULL,
  `trajanje` int(11) NOT NULL,
  `drzava` varchar(100) NOT NULL,
  `ocjena` decimal(3,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `filmovi`
--

INSERT INTO `filmovi` (`id`, `naslov`, `godina`, `zanr`, `trajanje`, `drzava`, `ocjena`) VALUES
(1, 'The Shawshank Redemption', 1994, 'Drama', 142, 'USA', 9.9),
(2, 'The Godfather', 1972, 'Crime, Drama', 175, 'USA', 9.2),
(3, 'The Dark Knight', 2008, 'Action, Crime', 152, 'UK, USA', 9.0),
(4, 'Schindler\'s List', 1993, 'Biography, Drama', 195, 'USA', 9.0),
(5, '12 Angry Men', 1957, 'Crime, Drama', 96, 'USA', 9.0),
(6, 'Pulp Fiction', 1994, 'Crime, Drama', 154, 'USA', 8.9),
(7, 'The Lord of the Rings: The Return of the King', 2003, 'Action, Adventure', 201, 'NZ, USA', 9.0),
(8, 'Il Buono, il Brutto, il Cattivo', 1966, 'Western', 161, 'Italy', 8.8),
(9, 'Fight Club', 1999, 'Drama', 139, 'USA, Germany', 8.8),
(10, 'The Lord of the Rings: The Fellowship of the Ring', 2001, 'Action, Adventure', 178, 'NZ, USA', 8.8),
(11, 'Disaster Movie', 2008, 'Comedy', 87, 'USA', 1.9),
(13, 'ggg', 1900, 'Ac', 12, 'ss', 2.0);

-- --------------------------------------------------------

--
-- Table structure for table `korisnici`
--

CREATE TABLE `korisnici` (
  `id` int(11) NOT NULL,
  `ime` varchar(50) NOT NULL,
  `prezime` varchar(50) NOT NULL,
  `korisnicko_ime` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `lozinka` varchar(255) NOT NULL,
  `uloga` varchar(20) DEFAULT 'korisnik'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `korisnici`
--

INSERT INTO `korisnici` (`id`, `ime`, `prezime`, `korisnicko_ime`, `email`, `lozinka`, `uloga`) VALUES
(1, 'Fabio', 'Mandić', 'Fabio', 'fabio.mandic@gmail.com', '$2y$10$I5sJunVIRwDOL381olv0eOVhT2vAm6jiocYOkl9vueek3VSt5RAG6', 'korisnik'),
(2, 'admin', 'admin', 'admin', 'admin@gmail.com', '$2y$10$Hos5gNmhgZ.PG80rOHaxSeaOXK6nA9YDbn5XUGhEr4J3gSyFt8m7O', 'admin'),
(3, 'ilija', 'mandic', 'ilija', 'ilija@gmail.com', '$2y$10$btQZD61UgOBndqmE9uBUi.Xqu4o69QKzB9oKW9p/6K5JyYSwESlY.', 'korisnik'),
(4, 'fabio123', 'fabio123', 'fabio123', 'fabio123@gmail.com', '$2y$10$CtYheNhEWn5/hsX/d3f0jutuo877s0KW4Uc2Jq7nx/ea3BKPpoeQu', 'korisnik');

-- --------------------------------------------------------

--
-- Table structure for table `ocjene`
--

CREATE TABLE `ocjene` (
  `id` int(11) NOT NULL,
  `id_korisnik` int(11) NOT NULL,
  `id_slika` int(11) NOT NULL,
  `ocjena` int(11) NOT NULL CHECK (`ocjena` between 1 and 5),
  `vrijeme_ocjene` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ocjene`
--

INSERT INTO `ocjene` (`id`, `id_korisnik`, `id_slika`, `ocjena`, `vrijeme_ocjene`) VALUES
(1, 2, 1, 1, '2026-05-17 08:54:55'),
(4, 2, 2, 2, '2026-05-25 09:06:30'),
(5, 2, 4, 5, '2026-05-17 08:54:43'),
(6, 2, 3, 2, '2026-05-24 18:50:36'),
(14, 2, 5, 4, '2026-05-17 08:55:44'),
(15, 1, 4, 5, '2026-05-17 08:55:56'),
(16, 1, 5, 3, '2026-05-17 08:55:58'),
(17, 1, 2, 5, '2026-05-17 08:58:31'),
(22, 3, 4, 1, '2026-05-17 09:02:11'),
(23, 3, 2, 3, '2026-05-17 09:02:13'),
(24, 3, 3, 5, '2026-05-17 09:02:14'),
(25, 3, 1, 4, '2026-05-17 09:02:15'),
(33, 4, 1, 2, '2026-05-25 09:08:02'),
(34, 4, 2, 4, '2026-05-25 09:08:03'),
(35, 4, 4, 2, '2026-05-25 09:08:12');

-- --------------------------------------------------------

--
-- Table structure for table `slike`
--

CREATE TABLE `slike` (
  `id` int(11) NOT NULL,
  `naziv_datoteke` varchar(255) NOT NULL,
  `opis` text DEFAULT NULL,
  `putanja` varchar(255) NOT NULL,
  `izvor` varchar(50) DEFAULT 'lokalno'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `slike`
--

INSERT INTO `slike` (`id`, `naziv_datoteke`, `opis`, `putanja`, `izvor`) VALUES
(1, 'Stranger Things Poster', 'SF horor serija - sezona 1', 'https://images.unsplash.com/photo-1618336753974-aae8e04506aa?w=500', 'API'),
(2, 'The Crown Backdrop', 'Povijesna drama o kraljevskoj obitelji', 'https://images.unsplash.com/photo-1513151233558-d860c5398176?w=500', 'API'),
(3, 'Cinema Popcorn', 'Atmosfera iz virtualnog kina', 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=500', 'API'),
(4, '77101ec4-282c-4d37-8c8a-34c1dd48ed89.jpeg', 'gg', 'uploads/img_6a098223edbd42.48891952.jpeg', 'lokalno'),
(5, 'f.png', 'ffff', 'uploads/img_6a09828dcb6ec7.01348866.png', 'lokalno');

-- --------------------------------------------------------

--
-- Table structure for table `zeljeni_filmovi`
--

CREATE TABLE `zeljeni_filmovi` (
  `id` int(11) NOT NULL,
  `korisnik_id` int(11) NOT NULL,
  `film_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zeljeni_filmovi`
--

INSERT INTO `zeljeni_filmovi` (`id`, `korisnik_id`, `film_id`) VALUES
(4, 1, 1),
(5, 1, 2),
(17, 1, 3),
(16, 1, 4),
(30, 3, 4),
(27, 3, 5),
(28, 3, 11),
(32, 4, 5),
(33, 4, 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `filmovi`
--
ALTER TABLE `filmovi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `korisnici`
--
ALTER TABLE `korisnici`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `korisnicko_ime` (`korisnicko_ime`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ocjene`
--
ALTER TABLE `ocjene`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unikatna_ocjena` (`id_korisnik`,`id_slika`),
  ADD KEY `id_slika` (`id_slika`);

--
-- Indexes for table `slike`
--
ALTER TABLE `slike`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zeljeni_filmovi`
--
ALTER TABLE `zeljeni_filmovi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `korisnik_id` (`korisnik_id`,`film_id`),
  ADD KEY `film_id` (`film_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `filmovi`
--
ALTER TABLE `filmovi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `korisnici`
--
ALTER TABLE `korisnici`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ocjene`
--
ALTER TABLE `ocjene`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `slike`
--
ALTER TABLE `slike`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zeljeni_filmovi`
--
ALTER TABLE `zeljeni_filmovi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ocjene`
--
ALTER TABLE `ocjene`
  ADD CONSTRAINT `ocjene_ibfk_1` FOREIGN KEY (`id_korisnik`) REFERENCES `korisnici` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ocjene_ibfk_2` FOREIGN KEY (`id_slika`) REFERENCES `slike` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `zeljeni_filmovi`
--
ALTER TABLE `zeljeni_filmovi`
  ADD CONSTRAINT `zeljeni_filmovi_ibfk_1` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zeljeni_filmovi_ibfk_2` FOREIGN KEY (`film_id`) REFERENCES `filmovi` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
