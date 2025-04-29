-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 06:22 PM
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
-- Database: `car_pooling`
--

-- --------------------------------------------------------

--
-- Table structure for table `applicazioni`
--

CREATE TABLE `applicazioni` (
  `id` int(11) NOT NULL,
  `id_viaggio` int(11) NOT NULL,
  `id_utente` int(11) NOT NULL,
  `n_passeggeri` int(11) NOT NULL,
  `stato` varchar(20) DEFAULT 'in_attesa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicazioni`
--

INSERT INTO `applicazioni` (`id`, `id_viaggio`, `id_utente`, `n_passeggeri`, `stato`) VALUES
(1, 4, 4, 1, 'accepted'),
(2, 4, 2, 2, 'denied'),
(3, 1, 4, 1, 'in_attesa'),
(4, 4, 4, 1, 'denied'),
(5, 4, 4, 1, 'denied');

-- --------------------------------------------------------

--
-- Table structure for table `autisti`
--

CREATE TABLE `autisti` (
  `id_autista` int(11) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `numero_patente` varchar(20) NOT NULL,
  `scadenza_patente` date NOT NULL,
  `recapito_telefonico` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fotografia` varchar(255) NOT NULL,
  `nome_utente` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_marca` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `autisti`
--

INSERT INTO `autisti` (`id_autista`, `cognome`, `nome`, `numero_patente`, `scadenza_patente`, `recapito_telefonico`, `email`, `fotografia`, `nome_utente`, `password`, `id_marca`) VALUES
(1, 'Taylor', 'Michael', 'AB12345', '2026-05-15', '555-654-3210', 'michael.taylor@email.com', 'driver1.jpg', 'michael', 'password123', 1),
(2, 'Davis', 'Emily', 'CD98765', '2024-12-01', '555-765-4321', 'emily.davis@email.com', 'driver2.jpg', 'emilyd', 'password456', 2),
(3, 'Garcia', 'Luis', 'EF56789', '2025-08-20', '555-876-5432', 'luis.garcia@email.com', 'driver3.jpg', 'luisg', 'password789', 3),
(5, 'd', 'c', '12', '0000-00-00', '2', 'c@gmail.com', '', 'c', '$2b$10$uiWUD2hv1Yzc92Dk5Ob95uDdLtmBrQaYxMYZPkphGmYKkWQ98h9j2', 1);

-- --------------------------------------------------------

--
-- Table structure for table `auto`
--

CREATE TABLE `auto` (
  `id_auto` int(11) NOT NULL,
  `id_autista` int(11) DEFAULT NULL,
  `marca` varchar(50) NOT NULL,
  `modello` varchar(50) NOT NULL,
  `targa` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auto`
--

INSERT INTO `auto` (`id_auto`, `id_autista`, `marca`, `modello`, `targa`) VALUES
(1, 1, 'Toyota', 'Corolla', 'XYZ1234'),
(2, 2, 'Ford', 'Focus', 'ABC5678'),
(3, 3, 'BMW', '3 Series', 'LMN91011');

-- --------------------------------------------------------

--
-- Table structure for table `citta`
--

CREATE TABLE `citta` (
  `id_citta` int(11) NOT NULL,
  `nome_citta` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `citta`
--

INSERT INTO `citta` (`id_citta`, `nome_citta`) VALUES
(3, 'Chicago'),
(2, 'Los Angeles'),
(5, 'Miami'),
(1, 'New York'),
(4, 'San Francisco');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_autisti_utenti`
--

CREATE TABLE `feedback_autisti_utenti` (
  `id_feedback` int(11) NOT NULL,
  `id_autista` int(11) DEFAULT NULL,
  `id_utente` int(11) DEFAULT NULL,
  `voto` int(11) DEFAULT NULL CHECK (`voto` between 1 and 5),
  `giudizio` text DEFAULT NULL,
  `data_feedback` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_autisti_utenti`
--

INSERT INTO `feedback_autisti_utenti` (`id_feedback`, `id_autista`, `id_utente`, `voto`, `giudizio`, `data_feedback`) VALUES
(1, 1, 1, 5, 'Great ride! Very smooth and timely.', '2025-04-01 08:00:00'),
(2, 2, 2, 4, 'Good experience, but the car could have been cleaner.', '2025-04-01 09:00:00'),
(3, 3, 3, 3, 'The trip was okay, but the driver was late.', '2025-04-01 10:00:00'),
(4, 1, 4, 3, 'hes ok', '2025-04-26 15:03:05');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_utenti_autisti`
--

CREATE TABLE `feedback_utenti_autisti` (
  `id_feedback` int(11) NOT NULL,
  `id_utente` int(11) DEFAULT NULL,
  `id_autista` int(11) DEFAULT NULL,
  `giudizio` text DEFAULT NULL,
  `data_feedback` timestamp NOT NULL DEFAULT current_timestamp(),
  `voto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_utenti_autisti`
--

INSERT INTO `feedback_utenti_autisti` (`id_feedback`, `id_utente`, `id_autista`, `giudizio`, `data_feedback`, `voto`) VALUES
(1, 1, 1, 'Excellent user, very polite.', '2025-04-01 11:00:00', 5),
(2, 2, 2, 'Polite, but could be more communicative.', '2025-04-01 12:00:00', 3),
(3, 3, 3, 'User was fine, but had some issues with punctuality.', '2025-04-01 13:00:00', 2),
(5, 1, 5, 'he smells bad', '2025-04-26 15:12:46', 2);

-- --------------------------------------------------------

--
-- Table structure for table `marca_auto`
--

CREATE TABLE `marca_auto` (
  `id_marca` int(11) NOT NULL,
  `nome_marca` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marca_auto`
--

INSERT INTO `marca_auto` (`id_marca`, `nome_marca`) VALUES
(3, 'BMW'),
(2, 'Ford'),
(4, 'Mercedes'),
(1, 'Toyota'),
(5, 'Volkswagen');

-- --------------------------------------------------------

--
-- Table structure for table `notifiche`
--

CREATE TABLE `notifiche` (
  `id_notifica` int(11) NOT NULL,
  `id_utente` int(11) DEFAULT NULL,
  `messaggio` varchar(255) DEFAULT NULL,
  `stato` varchar(10) DEFAULT NULL,
  `data_notifica` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifiche`
--

INSERT INTO `notifiche` (`id_notifica`, `id_utente`, `messaggio`, `stato`, `data_notifica`) VALUES
(2, 4, 'Your application for the trip from Chicago to Chicago has been accepted', 'unread', '2025-04-27 12:29:17'),
(6, 4, 'Your application for the trip from Chicago to Chicago for 1 for has been accepted', 'unread', '2025-04-27 12:34:01'),
(7, 2, 'Your application for the trip from Chicago to Chicago for 2 for has been accepted', 'unread', '2025-04-27 12:59:53'),
(8, 4, 'Your application for the trip from Chicago to Chicago for 1 for has been accepted', 'unread', '2025-04-27 13:02:04'),
(9, 4, 'Your application for the trip from Chicago to Chicago for 1 for has been accepted', 'unread', '2025-04-27 13:02:40'),
(10, 4, 'Your application for the trip from Chicago to Chicago for 1 for has been accepted', 'unread', '2025-04-27 13:04:15'),
(11, 4, 'Your application for the trip from Chicago to Chicago for 1 for on Sat Apr 12 2025 00:00:00 GMT+0200 (Central European Summer Time) at 13:10:00 has been accepted', 'unread', '2025-04-27 14:02:38'),
(12, 4, 'Your application for the trip from Chicago to Chicago for 1 for on 2025-04-12 at 13:10:00 has been accepted', 'unread', '2025-04-27 14:13:59'),
(13, 2, 'Your application for the trip from Chicago to Chicago for 2 for on 2025-04-12 at 13:10:00 has been denied', 'unread', '2025-04-27 14:35:03'),
(14, 2, 'Your application for the trip from Chicago to Chicago for 2 for on 2025-04-12 at 13:10:00 has been accepted', 'unread', '2025-04-27 14:35:56'),
(15, 2, 'Your application for the trip from Chicago to Chicago for 2 for on 2025-04-12 at 13:10:00 has been denied', 'unread', '2025-04-27 14:35:59'),
(16, 2, 'Your application for the trip from Chicago to Chicago for 2 for on 2025-04-12 at 13:10:00 has been denied', 'unread', '2025-04-27 14:36:02'),
(17, 4, 'Your application for the trip from Chicago to Chicago for 1 for on 2025-04-12 at 13:10:00 has been accepted', 'unread', '2025-04-27 14:37:25'),
(18, 2, 'Your application for the trip from Chicago to Chicago for 2 for on 2025-04-12 at 13:10:00 has been denied', 'unread', '2025-04-27 14:46:53'),
(19, 4, 'Your application for the trip from Chicago to Chicago for 1 for on 2025-04-12 at 13:10:00 has been accepted', 'unread', '2025-04-27 14:48:12'),
(20, 4, 'Your application for the trip from Chicago to Chicago for 1 for on 2025-04-12 at 13:10:00 has been denied', 'unread', '2025-04-27 14:52:18'),
(21, 4, 'Your application for the trip from Chicago to Chicago for 1 on 2025-04-12 at 13:10:00 has been denied', 'unread', '2025-04-27 14:57:34');

-- --------------------------------------------------------

--
-- Table structure for table `stops`
--

CREATE TABLE `stops` (
  `id` int(11) NOT NULL,
  `nome` varchar(99) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stops`
--

INSERT INTO `stops` (`id`, `nome`) VALUES
(1, 'Fermata servizio 1'),
(2, 'Fermata servizio 2');

-- --------------------------------------------------------

--
-- Table structure for table `stops-viaggi`
--

CREATE TABLE `stops-viaggi` (
  `id` int(11) NOT NULL,
  `id_stop` int(11) NOT NULL,
  `id_viaggio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stops-viaggi`
--

INSERT INTO `stops-viaggi` (`id`, `id_stop`, `id_viaggio`) VALUES
(1, 1, 15),
(2, 2, 15);

-- --------------------------------------------------------

--
-- Table structure for table `tipo_animale`
--

CREATE TABLE `tipo_animale` (
  `id_animale` int(11) NOT NULL,
  `tipo_animale` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tipo_animale`
--

INSERT INTO `tipo_animale` (`id_animale`, `tipo_animale`) VALUES
(3, 'Bird'),
(2, 'Cat'),
(1, 'Dog'),
(4, 'Rabbit');

-- --------------------------------------------------------

--
-- Table structure for table `utenti`
--

CREATE TABLE `utenti` (
  `id_utente` int(11) NOT NULL,
  `cognome` varchar(100) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `documento_identita` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nome_utente` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utenti`
--

INSERT INTO `utenti` (`id_utente`, `cognome`, `nome`, `documento_identita`, `telefono`, `email`, `nome_utente`, `password`) VALUES
(1, 'Smith', 'John', 'A12345678', '123-456-7890', 'john.smith@email.com', 'johnny', 'password123'),
(2, 'Johnson', 'Sarah', 'B98765432', '987-654-3210', 'sarah.johnson@email.com', 'sarah123', 'password456'),
(3, 'Williams', 'David', 'C11223344', '555-123-4567', 'david.williams@email.com', 'davidw', 'password789'),
(4, 'b', 'a', '', '1', 'a@gmail.com', 'a', '$2b$10$sglRQBkLUojG8hA5SaXtZedB4b6mdhpqLz6rGY0diVwV2YcuTqwCC');

-- --------------------------------------------------------

--
-- Table structure for table `viaggi`
--

CREATE TABLE `viaggi` (
  `id_viaggio` int(11) NOT NULL,
  `id_autista` int(11) DEFAULT NULL,
  `data_partenza` date NOT NULL,
  `ora_partenza` time NOT NULL,
  `contributo_economico` decimal(10,2) NOT NULL,
  `tempo_percorrenza` int(11) NOT NULL,
  `posti_disponibili` int(11) NOT NULL,
  `animali` tinyint(1) NOT NULL DEFAULT 0,
  `id_citta_partenza` int(11) DEFAULT NULL,
  `id_citta_destinazione` int(11) DEFAULT NULL,
  `posti_occupati` int(11) NOT NULL DEFAULT 0,
  `applicazione_aperte` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `viaggi`
--

INSERT INTO `viaggi` (`id_viaggio`, `id_autista`, `data_partenza`, `ora_partenza`, `contributo_economico`, `tempo_percorrenza`, `posti_disponibili`, `animali`, `id_citta_partenza`, `id_citta_destinazione`, `posti_occupati`, `applicazione_aperte`) VALUES
(1, 1, '2025-04-10', '08:00:00', 50.00, 300, 4, 0, 1, 2, 0, 1),
(2, 2, '2025-04-15', '10:00:00', 60.00, 250, 3, 0, 3, 4, 0, 1),
(3, 3, '2025-04-20', '14:00:00', 40.00, 200, 2, 0, 5, 1, 0, 1),
(4, 5, '2025-04-12', '13:10:00', 33.00, 23, 4, 0, 3, 3, 4, 0),
(5, 5, '2025-04-25', '08:00:00', 1.00, 1, 1, 0, 2, 2, 0, 1),
(6, 5, '2025-04-17', '03:05:00', 2.00, 2, 2, 0, 5, 5, 0, 1),
(7, 5, '2025-04-27', '12:06:00', 3.00, 3, 3, 0, 1, 1, 0, 1),
(8, 5, '2025-04-02', '05:04:00', 30.00, 32, 5, 0, 2, 2, 0, 1),
(9, 5, '2025-04-27', '04:04:00', 23.00, 23, 3, 0, 2, 2, 0, 1),
(11, 5, '2025-04-03', '03:33:00', 3.00, 3, 3, 1, 5, 5, 0, 1),
(12, 5, '0003-03-03', '03:33:00', 3.00, 3, 3, 1, 5, 5, 0, 1),
(13, 5, '2025-04-04', '03:33:00', 3.00, 3, 3, 1, 5, 5, 0, 1),
(14, 5, '0003-03-31', '03:03:00', 3.00, 3, 3, 1, 5, 5, 0, 1),
(15, 5, '0003-03-31', '03:03:00', 3.00, 3, 3, 1, 5, 5, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applicazioni`
--
ALTER TABLE `applicazioni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applicazione-viaggio` (`id_viaggio`),
  ADD KEY `applicazione-utente` (`id_utente`);

--
-- Indexes for table `autisti`
--
ALTER TABLE `autisti`
  ADD PRIMARY KEY (`id_autista`),
  ADD UNIQUE KEY `nome_utente` (`nome_utente`),
  ADD KEY `id_marca` (`id_marca`);

--
-- Indexes for table `auto`
--
ALTER TABLE `auto`
  ADD PRIMARY KEY (`id_auto`),
  ADD KEY `id_autista` (`id_autista`);

--
-- Indexes for table `citta`
--
ALTER TABLE `citta`
  ADD PRIMARY KEY (`id_citta`),
  ADD UNIQUE KEY `nome_citta` (`nome_citta`);

--
-- Indexes for table `feedback_autisti_utenti`
--
ALTER TABLE `feedback_autisti_utenti`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `id_autista` (`id_autista`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indexes for table `feedback_utenti_autisti`
--
ALTER TABLE `feedback_utenti_autisti`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `id_utente` (`id_utente`),
  ADD KEY `id_autista` (`id_autista`),
  ADD KEY `fk_feedback_tipo` (`voto`);

--
-- Indexes for table `marca_auto`
--
ALTER TABLE `marca_auto`
  ADD PRIMARY KEY (`id_marca`),
  ADD UNIQUE KEY `nome_marca` (`nome_marca`);

--
-- Indexes for table `notifiche`
--
ALTER TABLE `notifiche`
  ADD PRIMARY KEY (`id_notifica`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indexes for table `stops`
--
ALTER TABLE `stops`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stops-viaggi`
--
ALTER TABLE `stops-viaggi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stop-stops-viaggi` (`id_stop`),
  ADD KEY `viaggio-stops-viaggi` (`id_viaggio`);

--
-- Indexes for table `tipo_animale`
--
ALTER TABLE `tipo_animale`
  ADD PRIMARY KEY (`id_animale`),
  ADD UNIQUE KEY `tipo_animale` (`tipo_animale`);

--
-- Indexes for table `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id_utente`),
  ADD UNIQUE KEY `nome_utente` (`nome_utente`);

--
-- Indexes for table `viaggi`
--
ALTER TABLE `viaggi`
  ADD PRIMARY KEY (`id_viaggio`),
  ADD KEY `id_autista` (`id_autista`),
  ADD KEY `id_citta_partenza` (`id_citta_partenza`),
  ADD KEY `id_citta_destinazione` (`id_citta_destinazione`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applicazioni`
--
ALTER TABLE `applicazioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `autisti`
--
ALTER TABLE `autisti`
  MODIFY `id_autista` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `auto`
--
ALTER TABLE `auto`
  MODIFY `id_auto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `citta`
--
ALTER TABLE `citta`
  MODIFY `id_citta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `feedback_autisti_utenti`
--
ALTER TABLE `feedback_autisti_utenti`
  MODIFY `id_feedback` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback_utenti_autisti`
--
ALTER TABLE `feedback_utenti_autisti`
  MODIFY `id_feedback` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `marca_auto`
--
ALTER TABLE `marca_auto`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifiche`
--
ALTER TABLE `notifiche`
  MODIFY `id_notifica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `stops`
--
ALTER TABLE `stops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stops-viaggi`
--
ALTER TABLE `stops-viaggi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tipo_animale`
--
ALTER TABLE `tipo_animale`
  MODIFY `id_animale` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id_utente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `viaggi`
--
ALTER TABLE `viaggi`
  MODIFY `id_viaggio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applicazioni`
--
ALTER TABLE `applicazioni`
  ADD CONSTRAINT `applicazione-utente` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id_utente`),
  ADD CONSTRAINT `applicazione-viaggio` FOREIGN KEY (`id_viaggio`) REFERENCES `viaggi` (`id_viaggio`);

--
-- Constraints for table `autisti`
--
ALTER TABLE `autisti`
  ADD CONSTRAINT `autisti_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marca_auto` (`id_marca`) ON DELETE SET NULL;

--
-- Constraints for table `auto`
--
ALTER TABLE `auto`
  ADD CONSTRAINT `auto_ibfk_1` FOREIGN KEY (`id_autista`) REFERENCES `autisti` (`id_autista`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_autisti_utenti`
--
ALTER TABLE `feedback_autisti_utenti`
  ADD CONSTRAINT `feedback_autisti_utenti_ibfk_1` FOREIGN KEY (`id_autista`) REFERENCES `autisti` (`id_autista`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_autisti_utenti_ibfk_2` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id_utente`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_utenti_autisti`
--
ALTER TABLE `feedback_utenti_autisti`
  ADD CONSTRAINT `feedback_utenti_autisti_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id_utente`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_utenti_autisti_ibfk_2` FOREIGN KEY (`id_autista`) REFERENCES `autisti` (`id_autista`) ON DELETE CASCADE;

--
-- Constraints for table `notifiche`
--
ALTER TABLE `notifiche`
  ADD CONSTRAINT `notifiche_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id_utente`);

--
-- Constraints for table `stops-viaggi`
--
ALTER TABLE `stops-viaggi`
  ADD CONSTRAINT `stop-stops-viaggi` FOREIGN KEY (`id_stop`) REFERENCES `stops` (`id`),
  ADD CONSTRAINT `viaggio-stops-viaggi` FOREIGN KEY (`id_viaggio`) REFERENCES `viaggi` (`id_viaggio`);

--
-- Constraints for table `viaggi`
--
ALTER TABLE `viaggi`
  ADD CONSTRAINT `viaggi_ibfk_1` FOREIGN KEY (`id_autista`) REFERENCES `autisti` (`id_autista`) ON DELETE CASCADE,
  ADD CONSTRAINT `viaggi_ibfk_2` FOREIGN KEY (`id_citta_partenza`) REFERENCES `citta` (`id_citta`) ON DELETE CASCADE,
  ADD CONSTRAINT `viaggi_ibfk_3` FOREIGN KEY (`id_citta_destinazione`) REFERENCES `citta` (`id_citta`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
