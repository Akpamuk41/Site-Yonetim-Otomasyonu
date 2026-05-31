-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 31 May 2026, 21:58:08
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.0.30

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
-- Tablo için tablo yapısı `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('normal','onemli','acil') NOT NULL DEFAULT 'normal',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Tablo döküm verisi `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `content`, `priority`, `is_active`, `created_at`) VALUES
(1, 'Test Duyuru', 'Bu bir test duyurusudur.', 'normal', 1, '2026-05-31 19:11:38'),
(2, 'Elektrik Kesintisi', '01.06.2026 Tarihinde elektrik kesintisi olacaktır.', 'onemli', 1, '2026-05-31 19:38:43');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `apartments`
--

CREATE TABLE `apartments` (
  `apartment_id` int(11) NOT NULL,
  `block_id` int(11) NOT NULL,
  `floor_no` int(11) NOT NULL,
  `apartment_no` varchar(10) NOT NULL,
  `status` enum('bos','dolu') NOT NULL DEFAULT 'bos',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Tablo döküm verisi `apartments`
--

INSERT INTO `apartments` (`apartment_id`, `block_id`, `floor_no`, `apartment_no`, `status`, `created_at`) VALUES
(1, 1, 0, '1', 'dolu', '2026-05-07 18:49:59'),
(2, 1, 0, '2', 'dolu', '2026-05-07 18:49:59'),
(3, 1, 1, '3', 'dolu', '2026-05-07 18:49:59'),
(4, 1, 1, '4', 'dolu', '2026-05-07 18:49:59'),
(5, 1, 2, '5', 'bos', '2026-05-07 18:49:59'),
(6, 2, 0, '1', 'dolu', '2026-05-07 18:49:59'),
(7, 2, 0, '2', 'dolu', '2026-05-07 18:49:59'),
(8, 2, 1, '3', 'dolu', '2026-05-07 18:49:59'),
(9, 2, 1, '4', 'bos', '2026-05-07 18:49:59'),
(10, 2, 2, '5', 'dolu', '2026-05-07 18:49:59'),
(11, 3, 0, '1', 'dolu', '2026-05-07 18:49:59'),
(12, 3, 0, '2', 'bos', '2026-05-07 18:49:59'),
(13, 3, 1, '3', 'dolu', '2026-05-07 18:49:59'),
(14, 3, 1, '4', 'dolu', '2026-05-07 18:49:59'),
(15, 3, 2, '5', 'bos', '2026-05-07 18:49:59'),
(16, 4, 0, '1', 'dolu', '2026-05-07 18:49:59'),
(17, 4, 0, '2', 'dolu', '2026-05-07 18:49:59'),
(18, 4, 1, '3', 'bos', '2026-05-07 18:49:59'),
(19, 4, 1, '4', 'dolu', '2026-05-07 18:49:59'),
(20, 4, 2, '5', 'dolu', '2026-05-07 18:49:59'),
(21, 5, 0, '1', 'dolu', '2026-05-07 18:49:59'),
(22, 5, 0, '2', 'dolu', '2026-05-07 18:49:59'),
(23, 5, 1, '3', 'bos', '2026-05-07 18:49:59'),
(24, 5, 2, '5', 'bos', '2026-05-07 18:49:59'),
(25, 13, 1, '5', 'dolu', '2026-05-31 19:32:10');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blocks`
--

CREATE TABLE `blocks` (
  `block_id` int(11) NOT NULL,
  `block_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `blocks`
--

INSERT INTO `blocks` (`block_id`, `block_name`, `created_at`) VALUES
(1, 'A Blok', '2026-05-07 18:49:59'),
(2, 'B Blok', '2026-05-07 18:49:59'),
(3, 'C Blok', '2026-05-07 18:49:59'),
(4, 'D Blok', '2026-05-07 18:49:59'),
(5, 'E Blok', '2026-05-07 18:49:59'),
(6, 'F Blok', '2026-05-07 18:49:59'),
(7, 'G Blok', '2026-05-07 18:49:59'),
(8, 'H Blok', '2026-05-07 18:49:59'),
(9, 'I Blok', '2026-05-07 18:49:59'),
(10, 'J Blok', '2026-05-07 18:49:59'),
(11, 'K Blok', '2026-05-07 18:49:59'),
(12, 'L Blok', '2026-05-07 18:49:59'),
(13, 'X Blok', '2026-05-31 19:31:57');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `status` enum('acik','cozuldu') NOT NULL DEFAULT 'acik',
  `complaint_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Tablo döküm verisi `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `resident_id`, `title`, `description`, `status`, `complaint_date`, `created_at`) VALUES
(1, 1, 'Asansor Ar??zasi', 'A Blok asansoru 2. katta takiliyor, acil bakim gerekiyor.', 'acik', '2026-03-01', '2026-05-07 18:49:59'),
(2, 2, 'Su Basinc Dusuklugu', 'B Blok 2. katta su basinc cok dusuk, sabahlari hic akmiyor.', 'cozuldu', '2026-02-15', '2026-05-07 18:49:59'),
(3, 3, 'Otopark Kotusu', 'C Blok otoparkinda yanlis park eden araclar var, guvenlik gormuyor.', 'acik', '2026-03-05', '2026-05-07 18:49:59'),
(4, 4, 'Lambalar S??nuk', 'Bahce aydinlatma lambalari 3 gundur yanmiyor.', 'cozuldu', '2026-02-20', '2026-05-07 18:49:59'),
(5, 6, 'Kapi Zili Calismiyor', 'Daire zili calismiyor, kargo geliyor haberimiz olmuyor.', 'acik', '2026-03-10', '2026-05-07 18:49:59'),
(6, 7, 'Klima Dis Unite G??r??ltusu', 'Daire dis unite sesi komsumuzu rahatsiz ediyor.', 'acik', '2026-03-12', '2026-05-07 18:49:59'),
(7, 8, 'Merdiven Temizligi', '3. kat merdivenleri 1 haftadir temizlenmedi.', 'cozuldu', '2026-02-25', '2026-05-07 18:49:59'),
(8, 11, 'Kapi Kilit Sorunu', 'Giris kapi kilidi sert calisiyor, yaglanmasi lazim.', 'acik', '2026-03-15', '2026-05-07 18:49:59'),
(9, 13, 'Sicak Su Kesintisi', 'Soguk su var ama sicak su 2 saattir gelmiyor.', 'cozuldu', '2026-03-08', '2026-05-07 18:49:59'),
(10, 14, 'Internet Altyapisi', 'Internet hizi cok dusuk, fiber altyapi guncellemesi gerekli.', 'cozuldu', '2026-03-18', '2026-05-07 18:49:59'),
(11, 16, 'Cocuk Parki Bakimi', 'Salincak kopuk, cocuklar icin tehlikeli.', 'cozuldu', '2026-03-20', '2026-05-07 18:49:59'),
(13, 20, 'İnternet Kesintisi', 'İnternet kabloların olduğu yerlere fareler girmiş ve kabloları kemirmişler internet yok.', 'cozuldu', '2026-05-07', '2026-05-07 19:10:55'),
(14, 1, 'Test Sikayet', 'Bu bir test sikayetidir.', 'acik', '2026-05-31', '2026-05-31 19:11:43'),
(15, 21, 'Elektrik Kesintisi', 'elektrik kesintisi neden var', 'cozuldu', '2026-05-31', '2026-05-31 19:39:49');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `dues`
--

CREATE TABLE `dues` (
  `dues_id` int(11) NOT NULL,
  `apartment_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `month` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('odendi','odenmedi') NOT NULL DEFAULT 'odenmedi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Tablo döküm verisi `dues`
--

INSERT INTO `dues` (`dues_id`, `apartment_id`, `year`, `month`, `amount`, `status`, `created_at`) VALUES
(1, 1, 2026, 'Ocak', 500.00, 'odendi', '2026-05-07 18:49:59'),
(2, 1, 2026, 'Subat', 500.00, 'odendi', '2026-05-07 18:49:59'),
(3, 1, 2026, 'Mart', 500.00, 'odendi', '2026-05-07 18:49:59'),
(4, 2, 2026, 'Ocak', 500.00, 'odendi', '2026-05-07 18:49:59'),
(5, 2, 2026, 'Subat', 500.00, 'odenmedi', '2026-05-07 18:49:59'),
(6, 2, 2026, 'Mart', 500.00, 'odenmedi', '2026-05-07 18:49:59'),
(7, 3, 2026, 'Ocak', 600.00, 'odendi', '2026-05-07 18:49:59'),
(8, 3, 2026, 'Subat', 600.00, 'odendi', '2026-05-07 18:49:59'),
(9, 3, 2026, 'Mart', 600.00, 'odendi', '2026-05-07 18:49:59'),
(10, 4, 2026, 'Ocak', 600.00, 'odendi', '2026-05-07 18:49:59'),
(11, 4, 2026, 'Subat', 600.00, 'odendi', '2026-05-07 18:49:59'),
(12, 4, 2026, 'Mart', 600.00, 'odenmedi', '2026-05-07 18:49:59'),
(13, 6, 2026, 'Ocak', 550.00, 'odendi', '2026-05-07 18:49:59'),
(14, 6, 2026, 'Subat', 550.00, 'odendi', '2026-05-07 18:49:59'),
(15, 6, 2026, 'Mart', 550.00, 'odendi', '2026-05-07 18:49:59'),
(16, 7, 2026, 'Ocak', 550.00, 'odendi', '2026-05-07 18:49:59'),
(17, 7, 2026, 'Subat', 550.00, 'odenmedi', '2026-05-07 18:49:59'),
(18, 7, 2026, 'Mart', 550.00, 'odenmedi', '2026-05-07 18:49:59'),
(19, 8, 2026, 'Ocak', 700.00, 'odendi', '2026-05-07 18:49:59'),
(20, 8, 2026, 'Subat', 700.00, 'odendi', '2026-05-07 18:49:59'),
(21, 8, 2026, 'Mart', 700.00, 'odendi', '2026-05-07 18:49:59'),
(22, 10, 2026, 'Ocak', 450.00, 'odendi', '2026-05-07 18:49:59'),
(23, 10, 2026, 'Subat', 450.00, 'odendi', '2026-05-07 18:49:59'),
(24, 10, 2026, 'Mart', 450.00, 'odenmedi', '2026-05-07 18:49:59'),
(25, 11, 2026, 'Ocak', 500.00, 'odendi', '2026-05-07 18:49:59'),
(26, 11, 2026, 'Subat', 500.00, 'odenmedi', '2026-05-07 18:49:59'),
(27, 19, 2026, 'Mayıs', 550.00, 'odendi', '2026-05-07 19:11:31'),
(28, 25, 2026, 'Mayıs', 500.00, 'odendi', '2026-05-31 19:35:39');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `expense_type` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Tablo döküm verisi `expenses`
--

INSERT INTO `expenses` (`expense_id`, `expense_type`, `amount`, `expense_date`, `description`, `created_at`) VALUES
(1, 'Elektrik', 3500.00, '2026-01-05', 'Ocak ayi site elektrik faturasi', '2026-05-07 18:49:59'),
(2, 'Temizlik', 2500.00, '2026-01-10', 'Ocak ayi temizlik personeli maasi', '2026-05-07 18:49:59'),
(3, 'Bakim-Onarim', 1800.00, '2026-01-15', 'Asansor rutin bakimi', '2026-05-07 18:49:59'),
(4, 'Guvenlik', 4000.00, '2026-01-20', 'Guvenlik gorevlisi maaslari', '2026-05-07 18:49:59'),
(5, 'Su Gideri', 2200.00, '2026-01-25', 'Ocak ayi su faturasi', '2026-05-07 18:49:59'),
(6, 'Elektrik', 3200.00, '2026-02-05', 'Subat ayi site elektrik faturasi', '2026-05-07 18:49:59'),
(7, 'Temizlik', 2500.00, '2026-02-10', 'Subat ayi temizlik personeli maasi', '2026-05-07 18:49:59'),
(8, 'Bakim-Onarim', 950.00, '2026-02-14', 'Bahce aydinlatma lamba degisimi', '2026-05-07 18:49:59'),
(9, 'Guvenlik', 4000.00, '2026-02-20', 'Guvenlik gorevlisi maaslari', '2026-05-07 18:49:59'),
(10, 'Su Gideri', 2100.00, '2026-02-25', 'Subat ayi su faturasi', '2026-05-07 18:49:59'),
(11, 'Elektrik', 2800.00, '2026-03-05', 'Mart ayi site elektrik faturasi', '2026-05-07 18:49:59'),
(12, 'Temizlik', 2500.00, '2026-03-10', 'Mart ayi temizlik personeli maasi', '2026-05-07 18:49:59'),
(13, 'Bakim-Onarim', 1200.00, '2026-03-12', 'Kapi kilit bakimi', '2026-05-07 18:49:59'),
(14, 'Guvenlik', 4000.00, '2026-03-20', 'Guvenlik gorevlisi maaslari', '2026-05-07 18:49:59'),
(15, 'Diger', 800.00, '2026-03-22', 'Site yonetim kurulu toplant?? masraflari', '2026-05-07 18:49:59'),
(16, 'Guvenlik', 2500.00, '2026-05-31', 'güvenlik ödemesi', '2026-05-31 19:36:21');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `dues_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('nakit','kart','havale') NOT NULL DEFAULT 'nakit',
  `status` enum('onaylandi','beklemede','reddedildi') NOT NULL DEFAULT 'onaylandi',
  `is_simulation` tinyint(1) NOT NULL DEFAULT 0,
  `card_holder` varchar(100) DEFAULT NULL,
  `card_mask` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Tablo döküm verisi `payments`
--

INSERT INTO `payments` (`payment_id`, `dues_id`, `payment_date`, `paid_amount`, `payment_method`, `status`, `is_simulation`, `card_holder`, `card_mask`, `created_at`) VALUES
(1, 1, '2026-01-15', 500.00, 'nakit', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(2, 2, '2026-02-10', 500.00, 'havale', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(3, 4, '2026-01-20', 500.00, 'kart', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(4, 7, '2026-01-12', 600.00, 'nakit', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(5, 8, '2026-02-14', 600.00, 'havale', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(6, 9, '2026-03-05', 600.00, 'kart', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(7, 10, '2026-01-18', 600.00, 'nakit', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(8, 11, '2026-02-20', 600.00, 'havale', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(9, 13, '2026-01-08', 550.00, 'kart', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(10, 14, '2026-02-11', 550.00, 'nakit', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(11, 15, '2026-03-12', 550.00, 'havale', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(12, 16, '2026-01-25', 550.00, 'kart', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(13, 19, '2026-01-30', 700.00, 'nakit', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(14, 20, '2026-02-22', 700.00, 'havale', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(15, 21, '2026-03-08', 700.00, 'kart', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(16, 22, '2026-01-14', 450.00, 'nakit', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(17, 23, '2026-02-16', 450.00, 'havale', 'onaylandi', 0, NULL, NULL, '2026-05-07 18:49:59'),
(18, 27, '2026-05-07', 550.00, 'kart', 'onaylandi', 1, 'Orazjemal Meredova', '1234', '2026-05-07 19:12:29'),
(19, 3, '2026-05-31', 500.00, 'nakit', 'onaylandi', 0, NULL, NULL, '2026-05-31 19:11:38'),
(20, 28, '2026-05-31', 500.00, 'kart', 'onaylandi', 1, 'Emre Emir', '1234', '2026-05-31 19:39:30');

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
  `apartment_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `type` enum('ev_sahibi','kiraci') NOT NULL DEFAULT 'ev_sahibi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Tablo döküm verisi `residents`
--

INSERT INTO `residents` (`resident_id`, `apartment_id`, `name`, `surname`, `phone`, `email`, `type`, `created_at`) VALUES
(1, 1, 'Ahmet', 'Yilmaz', '0555 111 22 33', 'ahmet.yilmaz@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(2, 2, 'Mehmet', 'Kaya', '0555 222 33 44', 'mehmet.kaya@email.com', 'kiraci', '2026-05-07 18:49:59'),
(3, 3, 'Ayse', 'Demir', '0555 333 44 55', 'ayse.demir@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(4, 4, 'Fatma', 'Sahin', '0555 444 55 66', 'fatma.sahin@email.com', 'kiraci', '2026-05-07 18:49:59'),
(5, 6, 'Ali', 'Celik', '0555 555 66 77', 'ali.celik@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(6, 7, 'Veli', 'Aydin', '0555 666 77 88', 'veli.aydin@email.com', 'kiraci', '2026-05-07 18:49:59'),
(7, 8, 'Hasan', 'Koc', '0555 777 88 99', 'hasan.koc@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(8, 10, 'Huseyin', 'Ozdemir', '0555 888 99 00', 'huseyin.ozdemir@email.com', 'kiraci', '2026-05-07 18:49:59'),
(9, 11, 'Emre', 'Yildiz', '0555 999 00 11', 'emre.yildiz@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(10, 13, 'Can', 'Kara', '0555 000 11 22', 'can.kara@email.com', 'kiraci', '2026-05-07 18:49:59'),
(11, 14, 'Ebru', 'Aksoy', '0555 123 45 67', 'ebru.aksoy@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(13, 16, 'Cem', 'Tas', '0555 345 67 89', 'cem.tas@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(14, 17, 'Deniz', 'Arslan', '0555 456 78 90', 'deniz.arslan@email.com', 'kiraci', '2026-05-07 18:49:59'),
(15, 18, 'Elif', 'Korkmaz', '0555 567 89 01', 'elif.korkmaz@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(16, 20, 'Furkan', 'Gunes', '0555 678 90 12', 'furkan.gunes@email.com', 'kiraci', '2026-05-07 18:49:59'),
(17, 21, 'Gizem', 'Balc??', '0555 789 01 23', 'gizem.balci@email.com', 'ev_sahibi', '2026-05-07 18:49:59'),
(20, 19, 'Orazjemal', 'Meredova', '0551 555 55 55', 'pamukmeredova@gmail.com', 'kiraci', '2026-05-07 19:09:59'),
(21, 25, 'Emre', 'Emir', '0551 555 55 55', 'info@yonetim.com', 'ev_sahibi', '2026-05-31 19:35:14');

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
) ;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `resident_id`, `created_at`) VALUES
(1, 'admin', '$2y$10$C4yQKZ.SWpsItZJiij9J1uk3qt52G8DfEOunj/z2fuhJzhu8FddA.', 'admin', NULL, '2026-05-07 18:49:59'),
(2, 'ahmet.yilmaz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 1, '2026-05-07 18:49:59'),
(3, 'mehmet.kaya', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 2, '2026-05-07 18:49:59'),
(4, 'ayse.demir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 3, '2026-05-07 18:49:59'),
(5, 'fatma.sahin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 4, '2026-05-07 18:49:59'),
(6, 'ali.celik', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 6, '2026-05-07 18:49:59'),
(7, 'veli.aydin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 7, '2026-05-07 18:49:59'),
(8, 'hasan.koc', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 8, '2026-05-07 18:49:59'),
(9, 'emre.yildiz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 11, '2026-05-07 18:49:59'),
(10, 'can.kara', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 13, '2026-05-07 18:49:59'),
(11, 'ebru.aksoy', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 14, '2026-05-07 18:49:59'),
(12, 'burak.erdem', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'resident', 15, '2026-05-07 18:49:59'),
(13, 'pamukmeredova', '$2y$10$LzqyhpKlkTuyMSMrC1CuiuWqPomJLKDHi3MDQ.O8VnjYvAUgZAGAG', 'resident', 20, '2026-05-07 19:10:14'),
(14, 'emreemir', '$2y$10$koxum9Oq0X7n9k9ri/TB0OTsaF/nsiX7uVFASjkGVFX8ZME9wCNjK', 'resident', 21, '2026-05-31 19:36:45');

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `vw_borclu_daireler`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE `vw_borclu_daireler` (
`apartment_id` int(11)
,`block_name` varchar(50)
,`apartment_no` varchar(10)
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
,`apartment_no` varchar(10)
,`floor_no` int(11)
,`status` enum('bos','dolu')
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
,`title` varchar(200)
,`description` text
,`complaint_status` enum('acik','cozuldu')
,`complaint_date` date
,`resident_name` varchar(50)
,`resident_surname` varchar(50)
,`resident_phone` varchar(20)
,`resident_email` varchar(100)
,`block_name` varchar(50)
,`apartment_no` varchar(10)
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
,`month` varchar(20)
,`due_amount` decimal(10,2)
,`block_name` varchar(50)
,`apartment_no` varchar(10)
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
-- Tablo için indeksler `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Tablo için indeksler `apartments`
--
ALTER TABLE `apartments`
  ADD PRIMARY KEY (`apartment_id`),
  ADD UNIQUE KEY `uk_block_apartment` (`block_id`,`apartment_no`),
  ADD KEY `idx_apartments_block_id` (`block_id`);

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
  ADD KEY `idx_complaints_resident_id` (`resident_id`),
  ADD KEY `idx_complaints_status` (`status`);

--
-- Tablo için indeksler `dues`
--
ALTER TABLE `dues`
  ADD PRIMARY KEY (`dues_id`),
  ADD KEY `idx_dues_apartment_id` (`apartment_id`),
  ADD KEY `idx_dues_status` (`status`),
  ADD KEY `idx_dues_apartment_status` (`apartment_id`,`status`);

--
-- Tablo için indeksler `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `idx_expenses_date` (`expense_date`);

--
-- Tablo için indeksler `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payments_dues_id` (`dues_id`),
  ADD KEY `idx_payments_status` (`status`);

--
-- Tablo için indeksler `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`resident_id`),
  ADD KEY `idx_residents_apartment_id` (`apartment_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_resident_id` (`resident_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `apartments`
--
ALTER TABLE `apartments`
  MODIFY `apartment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `blocks`
--
ALTER TABLE `blocks`
  MODIFY `block_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `dues`
--
ALTER TABLE `dues`
  MODIFY `dues_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `residents`
--
ALTER TABLE `residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `apartments`
--
ALTER TABLE `apartments`
  ADD CONSTRAINT `apartments_ibfk_1` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`block_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `dues`
--
ALTER TABLE `dues`
  ADD CONSTRAINT `dues_ibfk_1` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`apartment_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`dues_id`) REFERENCES `dues` (`dues_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `residents_ibfk_1` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`apartment_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
