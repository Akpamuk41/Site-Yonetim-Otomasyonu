-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3307
-- Üretim Zamanı: 24 May 2026, 16:02:21
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `site_yonetim`
--

DELIMITER $$
--
-- Yordamlar
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_add_expense` (IN `p_expense_type` VARCHAR(100), IN `p_amount` DECIMAL(10,2), IN `p_expense_date` DATE, IN `p_description` TEXT)   BEGIN
    START TRANSACTION;
    INSERT INTO expenses (expense_type, amount, expense_date, description)
    VALUES (p_expense_type, p_amount, p_expense_date, p_description);
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_borclu_daireler` ()   BEGIN
    SELECT 
        a.apartment_id,
        b.block_name,
        a.apartment_no,
        d.amount,
        d.status
    FROM dues d
    JOIN apartments a ON d.apartment_id = a.apartment_id
    JOIN blocks b ON a.block_id = b.block_id
    WHERE d.status = 'odenmedi';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_monthly_income` (IN `p_year` INT, IN `p_month` VARCHAR(20))   BEGIN
    SELECT p_year AS year, p_month AS month,
        COUNT(*) AS payment_count,
        SUM(p.paid_amount) AS total_income,
        SUM(CASE WHEN p.payment_method = 'nakit' THEN p.paid_amount ELSE 0 END) AS cash_income,
        SUM(CASE WHEN p.payment_method = 'kart' THEN p.paid_amount ELSE 0 END) AS card_income,
        SUM(CASE WHEN p.payment_method = 'havale' THEN p.paid_amount ELSE 0 END) AS transfer_income
    FROM payments p
    INNER JOIN dues d ON p.dues_id = d.dues_id
    WHERE d.year = p_year AND d.month = p_month AND p.status = 'onaylandi';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_unpaid_dues` (IN `p_block_id` INT, IN `p_year` INT)   BEGIN
    SELECT d.dues_id, d.year, d.month, d.amount, d.status,
           b.block_name, a.apartment_no, a.floor_no, r.name, r.surname, r.phone
    FROM dues d
    INNER JOIN apartments a ON d.apartment_id = a.apartment_id
    INNER JOIN blocks b ON a.block_id = b.block_id
    LEFT JOIN residents r ON a.apartment_id = r.apartment_id
    WHERE d.status = 'odenmedi'
      AND (p_block_id IS NULL OR b.block_id = p_block_id)
      AND (p_year IS NULL OR d.year = p_year)
    ORDER BY d.year DESC, d.month DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_complaint_status` (IN `p_complaint_id` INT, IN `p_new_status` VARCHAR(20))   BEGIN
    IF p_new_status IN ('acik', 'cozuldu') THEN
        UPDATE complaints SET status = p_new_status WHERE complaint_id = p_complaint_id;
        SELECT ROW_COUNT() AS affected_rows;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Gecersiz durum degeri.';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `apartments`
--

CREATE TABLE `apartments` (
  `apartment_id` int(11) NOT NULL,
  `block_id` int(11) DEFAULT NULL,
  `floor_no` int(11) NOT NULL,
  `apartment_no` int(11) NOT NULL,
  `status` enum('dolu','bos') DEFAULT 'bos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `apartments`
--

INSERT INTO `apartments` (`apartment_id`, `block_id`, `floor_no`, `apartment_no`, `status`) VALUES
(1, 1, 1, 1, 'dolu'),
(2, 1, 1, 2, 'bos'),
(3, 2, 2, 1, 'dolu'),
(4, 2, 2, 2, 'dolu'),
(5, 3, 3, 1, 'bos'),
(6, 3, 3, 2, 'dolu'),
(7, 4, 1, 1, 'dolu'),
(8, 4, 1, 2, 'bos'),
(9, 5, 2, 1, 'dolu'),
(10, 5, 2, 2, 'bos');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blocks`
--

CREATE TABLE `blocks` (
  `block_id` int(11) NOT NULL,
  `block_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `blocks`
--

INSERT INTO `blocks` (`block_id`, `block_name`) VALUES
(1, 'A Blok'),
(2, 'B Blok'),
(3, 'C Blok'),
(4, 'D Blok'),
(5, 'E Blok'),
(6, 'F Blok'),
(7, 'G Blok'),
(8, 'H Blok'),
(9, 'I Blok'),
(10, 'J Blok');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `complaint_date` date DEFAULT curdate(),
  `status` enum('acik','cozuldu') DEFAULT 'acik'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `resident_id`, `title`, `description`, `complaint_date`, `status`) VALUES
(1, 3, 'asansor çalışmıyor', 'ben geldıgımde çalışmıyordu', '2026-04-03', 'acik'),
(2, 1, 'ses', 'gece saatlerı seslı', '2026-04-03', 'acik'),
(3, 8, 'elekrtık', 'fshd', '2026-04-07', 'acik'),
(4, 11, 'ses', 'alt katta ses var', '2026-05-18', 'cozuldu'),
(5, 11, 'Çok ses var', 'dsdfssdf', '2026-05-24', 'cozuldu');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dues`
--

CREATE TABLE `dues` (
  `dues_id` int(11) NOT NULL,
  `apartment_id` int(11) DEFAULT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('odendi','odenmedi') DEFAULT 'odenmedi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `dues`
--

INSERT INTO `dues` (`dues_id`, `apartment_id`, `year`, `month`, `amount`, `due_date`, `status`) VALUES
(1, 1, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(2, 2, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(3, 3, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(4, 4, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(5, 5, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(6, 6, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(7, 7, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(8, 8, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(9, 9, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(10, 10, 2026, 1, 500.00, '2026-01-10', 'odendi'),
(11, 7, 2026, 0, 1000.00, NULL, 'odendi'),
(12, 1, 2026, 0, 550.00, NULL, 'odendi'),
(13, 1, 2026, 0, 2000.00, NULL, 'odendi');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `expense_type` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `expenses`
--

INSERT INTO `expenses` (`expense_id`, `expense_type`, `amount`, `expense_date`, `description`) VALUES
(1, 'Temizlik', 1200.00, '2026-11-18', 'temizlik firması'),
(2, 'Su Gideri', 1000.00, '2026-04-02', 'gider'),
(3, 'Elektrik', 1200.00, '2026-12-22', 'elektrik gideri');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `dues_id` int(11) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('nakit','kart','havale') DEFAULT NULL,
  `status` enum('onaylandi','beklemede','reddedildi') NOT NULL DEFAULT 'onaylandi',
  `is_simulation` tinyint(1) NOT NULL DEFAULT 0,
  `card_holder` varchar(100) DEFAULT NULL,
  `card_mask` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `payments`
--

INSERT INTO `payments` (`payment_id`, `dues_id`, `payment_date`, `paid_amount`, `payment_method`, `status`, `is_simulation`, `card_holder`, `card_mask`) VALUES
(1, 1, '2026-01-05', 500.00, 'kart', 'onaylandi', 0, NULL, NULL),
(2, 3, '2026-01-06', 500.00, 'nakit', 'onaylandi', 0, NULL, NULL),
(3, 5, '2026-01-07', 500.00, 'havale', 'onaylandi', 0, NULL, NULL),
(4, 7, '2026-01-08', 500.00, 'kart', 'onaylandi', 0, NULL, NULL),
(5, 9, '2026-01-09', 500.00, 'nakit', 'onaylandi', 0, NULL, NULL),
(6, 4, '2026-04-03', 250.00, 'havale', 'onaylandi', 0, NULL, NULL),
(7, 10, '2026-04-03', 500.00, 'nakit', 'onaylandi', 0, NULL, NULL),
(8, 6, '2026-04-03', 500.00, 'kart', 'onaylandi', 0, NULL, NULL),
(9, 2, '2026-04-04', 500.00, 'nakit', 'onaylandi', 0, NULL, NULL),
(10, 8, '2026-04-04', 1250.00, 'kart', 'onaylandi', 0, NULL, NULL),
(11, 11, '2026-05-18', 950.00, 'kart', 'onaylandi', 1, 'Ahmet Yılmaz', '1111'),
(12, 12, '2026-05-24', 550.00, 'kart', 'beklemede', 1, 'Orazjemal Meredova', '4565'),
(13, 13, '2026-05-24', 2000.00, 'kart', 'onaylandi', 1, 'ORAZJEMAL MEREDOVA', '2222');

--
-- Tetikleyiciler `payments`
--
DELIMITER $$
CREATE TRIGGER `trg_after_payment_insert` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    IF NEW.status = 'onaylandi' THEN
        UPDATE dues SET status = 'odendi' WHERE dues_id = NEW.dues_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_payment_update` AFTER UPDATE ON `payments` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        IF NEW.status = 'onaylandi' THEN
            UPDATE dues SET status = 'odendi' WHERE dues_id = NEW.dues_id;
        ELSEIF NEW.status = 'reddedildi' THEN
            UPDATE dues SET status = 'odenmedi' WHERE dues_id = NEW.dues_id;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_payment_after_insert` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    UPDATE dues
    SET status = 'odendi'
    WHERE dues_id = NEW.dues_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `residents`
--

CREATE TABLE `residents` (
  `resident_id` int(11) NOT NULL,
  `apartment_id` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `type` enum('ev_sahibi','kiraci') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `residents`
--

INSERT INTO `residents` (`resident_id`, `apartment_id`, `name`, `surname`, `phone`, `email`, `type`) VALUES
(1, 1, 'Ahmet', 'Yılmaz', '0500000001', 'ahmet@gmail.com', 'ev_sahibi'),
(2, 2, 'Mehmet', 'Kaya', '0500000002', 'mehmet@gmail.com', 'kiraci'),
(3, 3, 'Ayşe', 'Demir', '0500000003', 'ayse@gmail.com', 'ev_sahibi'),
(4, 4, 'Fatma', 'Çelik', '0500000004', 'fatma@gmail.com', 'kiraci'),
(5, 5, 'Ali', 'Şahin', '0500000005', 'ali@gmail.com', 'ev_sahibi'),
(6, 6, 'Veli', 'Koç', '0500000006', 'veli@gmail.com', 'kiraci'),
(7, 7, 'Zeynep', 'Aydın', '0500000007', 'zeynep@gmail.com', 'ev_sahibi'),
(8, 8, 'Hasan', 'Arslan', '0500000008', 'hasan@gmail.com', 'kiraci'),
(9, 9, 'Emine', 'Doğan', '0500000009', 'emine@gmail.com', 'ev_sahibi'),
(10, 10, 'Murat', 'Öztürk', '0500000010', 'murat@gmail.com', 'kiraci'),
(11, 1, 'ORAZJEMAL', 'MEREDOVA', '0222369875', 'pamukmeredova@gmail.com', 'kiraci'),
(12, 7, 'Barkın', 'Yılmaz', '0333 333 333 33', 'barkin@mail.com', 'kiraci'),
(13, 1, 'Ali', 'Yılmaz', '123456', 'ali@gmail.com', 'ev_sahibi');

--
-- Tetikleyiciler `residents`
--
DELIMITER $$
CREATE TRIGGER `trg_after_resident_delete` AFTER DELETE ON `residents` FOR EACH ROW BEGIN
    DECLARE resident_count INT;
    SELECT COUNT(*) INTO resident_count FROM residents WHERE apartment_id = OLD.apartment_id;
    IF resident_count = 0 THEN
        UPDATE apartments SET status = 'bos' WHERE apartment_id = OLD.apartment_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_resident_insert` AFTER INSERT ON `residents` FOR EACH ROW BEGIN
    UPDATE apartments SET status = 'dolu' WHERE apartment_id = NEW.apartment_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','resident') NOT NULL DEFAULT 'resident',
  `resident_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `resident_id`, `created_at`) VALUES
(1, 'admin', '$2y$10$opUrbPCBQ8Wo1a5oQi37Tu7/ri4hHlR7LUSvyxQWNLD8oYJ6fGDGq', 'admin', NULL, '2026-05-18 11:21:54'),
(2, 'pamuk', '$2y$10$WH4JNd5voTznTVyv2PsE1uNZR2IkMM7qaqFKXnC11X3tezm6062k6', 'resident', 11, '2026-05-18 11:26:12'),
(3, 'barkin', '$2y$10$rU3g9O4rZ3tqgBg7FdNZluGkIjSUBDICPpT/KJUyrBtfkxrmE0vDa', 'resident', 12, '2026-05-18 11:59:12');

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `vw_borclu_daireler`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `vw_borclu_daireler` (
`apartment_id` int(11)
,`block_name` varchar(50)
,`apartment_no` int(11)
,`amount` decimal(10,2)
,`status` enum('odendi','odenmedi')
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_apartment_summary`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_apartment_summary` (
`apartment_id` int(11)
,`block_name` varchar(50)
,`apartment_no` int(11)
,`floor_no` int(11)
,`status` enum('dolu','bos')
,`resident_name` varchar(50)
,`resident_surname` varchar(50)
,`resident_type` enum('ev_sahibi','kiraci')
,`total_dues` bigint(21)
,`paid_dues` decimal(22,0)
,`unpaid_dues` decimal(22,0)
,`total_debt` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_complaint_details`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_complaint_details` (
`complaint_id` int(11)
,`title` varchar(100)
,`description` text
,`complaint_status` enum('acik','cozuldu')
,`complaint_date` date
,`resident_name` varchar(50)
,`resident_surname` varchar(50)
,`resident_phone` varchar(20)
,`resident_email` varchar(100)
,`block_name` varchar(50)
,`apartment_no` int(11)
,`floor_no` int(11)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_financial_summary`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_financial_summary` (
`type` varchar(5)
,`period` varchar(7)
,`total_amount` decimal(32,2)
,`record_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_payment_details`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `v_payment_details` (
`payment_id` int(11)
,`payment_date` date
,`paid_amount` decimal(10,2)
,`payment_method` enum('nakit','kart','havale')
,`payment_status` enum('onaylandi','beklemede','reddedildi')
,`is_simulation` tinyint(1)
,`card_holder` varchar(100)
,`card_mask` varchar(20)
,`dues_id` int(11)
,`year` int(11)
,`month` int(11)
,`due_amount` decimal(10,2)
,`block_name` varchar(50)
,`apartment_no` int(11)
,`floor_no` int(11)
,`resident_name` varchar(50)
,`resident_surname` varchar(50)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı `vw_borclu_daireler`
--
DROP TABLE IF EXISTS `vw_borclu_daireler`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_borclu_daireler`  AS SELECT `a`.`apartment_id` AS `apartment_id`, `b`.`block_name` AS `block_name`, `a`.`apartment_no` AS `apartment_no`, `d`.`amount` AS `amount`, `d`.`status` AS `status` FROM ((`dues` `d` join `apartments` `a` on(`d`.`apartment_id` = `a`.`apartment_id`)) join `blocks` `b` on(`a`.`block_id` = `b`.`block_id`)) WHERE `d`.`status` = 'odenmedi' ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_apartment_summary`
--
DROP TABLE IF EXISTS `v_apartment_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_apartment_summary`  AS SELECT `a`.`apartment_id` AS `apartment_id`, `b`.`block_name` AS `block_name`, `a`.`apartment_no` AS `apartment_no`, `a`.`floor_no` AS `floor_no`, `a`.`status` AS `status`, `r`.`name` AS `resident_name`, `r`.`surname` AS `resident_surname`, `r`.`type` AS `resident_type`, count(distinct `d`.`dues_id`) AS `total_dues`, sum(case when `d`.`status` = 'odendi' then 1 else 0 end) AS `paid_dues`, sum(case when `d`.`status` = 'odenmedi' then 1 else 0 end) AS `unpaid_dues`, sum(case when `d`.`status` = 'odenmedi' then `d`.`amount` else 0 end) AS `total_debt` FROM (((`apartments` `a` join `blocks` `b` on(`a`.`block_id` = `b`.`block_id`)) left join `residents` `r` on(`a`.`apartment_id` = `r`.`apartment_id`)) left join `dues` `d` on(`a`.`apartment_id` = `d`.`apartment_id`)) GROUP BY `a`.`apartment_id`, `b`.`block_name`, `a`.`apartment_no`, `a`.`floor_no`, `a`.`status`, `r`.`name`, `r`.`surname`, `r`.`type` ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_complaint_details`
--
DROP TABLE IF EXISTS `v_complaint_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_complaint_details`  AS SELECT `c`.`complaint_id` AS `complaint_id`, `c`.`title` AS `title`, `c`.`description` AS `description`, `c`.`status` AS `complaint_status`, `c`.`complaint_date` AS `complaint_date`, `r`.`name` AS `resident_name`, `r`.`surname` AS `resident_surname`, `r`.`phone` AS `resident_phone`, `r`.`email` AS `resident_email`, `b`.`block_name` AS `block_name`, `a`.`apartment_no` AS `apartment_no`, `a`.`floor_no` AS `floor_no` FROM (((`complaints` `c` join `residents` `r` on(`c`.`resident_id` = `r`.`resident_id`)) join `apartments` `a` on(`r`.`apartment_id` = `a`.`apartment_id`)) join `blocks` `b` on(`a`.`block_id` = `b`.`block_id`)) ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_financial_summary`
--
DROP TABLE IF EXISTS `v_financial_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_financial_summary`  AS SELECT 'Gelir' AS `type`, date_format(`p`.`payment_date`,'%Y-%m') AS `period`, sum(`p`.`paid_amount`) AS `total_amount`, count(0) AS `record_count` FROM `payments` AS `p` WHERE `p`.`status` = 'onaylandi' GROUP BY date_format(`p`.`payment_date`,'%Y-%m')union all select 'Gider' AS `type`,date_format(`e`.`expense_date`,'%Y-%m') AS `period`,sum(`e`.`amount`) AS `total_amount`,count(0) AS `record_count` from `expenses` `e` group by date_format(`e`.`expense_date`,'%Y-%m') order by `period` desc,`type`  ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_payment_details`
--
DROP TABLE IF EXISTS `v_payment_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_payment_details`  AS SELECT `p`.`payment_id` AS `payment_id`, `p`.`payment_date` AS `payment_date`, `p`.`paid_amount` AS `paid_amount`, `p`.`payment_method` AS `payment_method`, `p`.`status` AS `payment_status`, `p`.`is_simulation` AS `is_simulation`, `p`.`card_holder` AS `card_holder`, `p`.`card_mask` AS `card_mask`, `d`.`dues_id` AS `dues_id`, `d`.`year` AS `year`, `d`.`month` AS `month`, `d`.`amount` AS `due_amount`, `b`.`block_name` AS `block_name`, `a`.`apartment_no` AS `apartment_no`, `a`.`floor_no` AS `floor_no`, `r`.`name` AS `resident_name`, `r`.`surname` AS `resident_surname` FROM ((((`payments` `p` join `dues` `d` on(`p`.`dues_id` = `d`.`dues_id`)) join `apartments` `a` on(`d`.`apartment_id` = `a`.`apartment_id`)) join `blocks` `b` on(`a`.`block_id` = `b`.`block_id`)) left join `residents` `r` on(`a`.`apartment_id` = `r`.`apartment_id`)) ;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `apartments`
--
ALTER TABLE `apartments`
  ADD PRIMARY KEY (`apartment_id`),
  ADD KEY `block_id` (`block_id`);

--
-- Tablo için indeksler `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`block_id`),
  ADD UNIQUE KEY `block_name` (`block_name`);

--
-- Tablo için indeksler `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `idx_resident_id` (`resident_id`);

--
-- Tablo için indeksler `dues`
--
ALTER TABLE `dues`
  ADD PRIMARY KEY (`dues_id`),
  ADD KEY `idx_apartment_id` (`apartment_id`);

--
-- Tablo için indeksler `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`);

--
-- Tablo için indeksler `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `dues_id` (`dues_id`);

--
-- Tablo için indeksler `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`resident_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `apartment_id` (`apartment_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `apartments`
--
ALTER TABLE `apartments`
  MODIFY `apartment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `blocks`
--
ALTER TABLE `blocks`
  MODIFY `block_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `dues`
--
ALTER TABLE `dues`
  MODIFY `dues_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `residents`
--
ALTER TABLE `residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `apartments`
--
ALTER TABLE `apartments`
  ADD CONSTRAINT `apartments_ibfk_1` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`block_id`);

--
-- Tablo kısıtlamaları `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`);

--
-- Tablo kısıtlamaları `dues`
--
ALTER TABLE `dues`
  ADD CONSTRAINT `dues_ibfk_1` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`apartment_id`);

--
-- Tablo kısıtlamaları `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`dues_id`) REFERENCES `dues` (`dues_id`);

--
-- Tablo kısıtlamaları `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `residents_ibfk_1` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`apartment_id`);

--
-- Tablo kısıtlamaları `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
