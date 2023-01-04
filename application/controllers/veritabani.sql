-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 04 Oca 2023, 22:59:33
-- Sunucu sürümü: 10.4.25-MariaDB
-- PHP Sürümü: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `veritabani`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `auths`
--

CREATE TABLE `auths` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `auths_type` enum('list','create','edit','delete','show') COLLATE utf8_turkish_ci DEFAULT NULL,
  `table_name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `hide_column` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL,
  `auths_group` int(11) DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `auths`
--

INSERT INTO `auths` (`id`, `name`, `auths_type`, `table_name`, `hide_column`, `auths_group`, `state`, `description`, `created_at`, `updated_at`, `own_id`, `user_id`) VALUES
(1, 'test', 'list', 'auths', NULL, 1, 1, '', '2023-01-05 00:56:20', '2023-01-05 00:56:20', 1, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `auths_group`
--

CREATE TABLE `auths_group` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `display` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `auths_group`
--

INSERT INTO `auths_group` (`id`, `name`, `display`, `state`, `description`, `created_at`, `updated_at`, `own_id`, `user_id`) VALUES
(1, 'test', '{\"tr\":\"Test Yetki\",\"en\":\"Test Auths\"}', 1, '', '2023-01-05 00:26:43', '2023-01-05 00:31:34', 1, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `default_emails`
--

CREATE TABLE `default_emails` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `title` varchar(500) COLLATE utf8_turkish_ci DEFAULT NULL,
  `content` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `fields`
--

CREATE TABLE `fields` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `display` text COLLATE utf8_turkish_ci DEFAULT NULL COMMENT ' örn:{tr:"İsim",en:"Name"}',
  `type` enum('sort_text','long_text','number','bool','file','image','phone','email','datetime','date','pass','array','json') COLLATE utf8_turkish_ci NOT NULL,
  `enums` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `min_length` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `max_length` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `min_value` double DEFAULT NULL,
  `max_value` double DEFAULT NULL,
  `relation_table` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `relation_id` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `relation_columns` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `mask` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `regex` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `lang_support` tinyint(1) DEFAULT 0,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `own_id` int(11) NOT NULL DEFAULT 1,
  `user_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `fields`
--

INSERT INTO `fields` (`id`, `name`, `display`, `type`, `enums`, `min_length`, `max_length`, `min_value`, `max_value`, `relation_table`, `relation_id`, `relation_columns`, `mask`, `regex`, `lang_support`, `state`, `description`, `created_at`, `updated_at`, `own_id`, `user_id`) VALUES
(1, 'id', '{\n\"tr\":\"ID\"\n}', 'number', NULL, '1', '11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 15:55:15', '2023-01-04 22:02:08', 1, 1),
(2, 'name', '{\n\"tr\":\"İsim\",\n\"en\":\"Name\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 15:56:35', '2023-01-04 22:12:27', 1, 1),
(3, 'display', '{\n\"tr\":\"Görüntüleme\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '', '2023-01-04 15:56:59', '2023-01-04 22:10:06', 1, 1),
(4, 'fields', '{\n\"tr\":\"Kolonlar\"\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, 'fields', 'name', '[\"display\"]', NULL, NULL, 0, 1, '', '2023-01-04 15:58:06', '2023-01-04 23:18:08', 1, 1),
(5, 'before_codes', '{\n\"tr\":\"Önce çalışacaklar\"\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 15:59:01', '2023-01-04 22:10:13', 1, 1),
(6, 'after_codes', '{\n\"tr\":\"Sonra Çalışacaklar\"\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 15:59:29', '2023-01-04 22:10:17', 1, 1),
(7, 'type', '{\n\"tr\":\"Kolon Tipleri\"\n}', 'array', '[\'sort_text\',\'long_text\',\'number\',\'bool\',\'file\',\'image\',\'phone\',\'email\',\'datetime\',\'date\',\'pass\']', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:01:59', '2023-01-04 22:10:35', 1, 1),
(8, 'min_length', '{\n\"tr\":\"En az uzunluk\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:02:51', '2023-01-04 22:12:00', 1, 1),
(9, 'max_length', '{\n\"tr\":\"En fazla uzunluk\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:03:33', '2023-01-04 22:11:58', 1, 1),
(10, 'min', '{\n\"tr\":\"En küçük değer\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:03:33', '2023-01-04 22:11:56', 1, 1),
(11, 'max', '{\n\"tr\":\"En büyük değer\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:04:29', '2023-01-04 22:11:54', 1, 1),
(12, 'relation_table', '{\n\"tr\":\"Bağlı tablo\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:04:29', '2023-01-04 22:11:52', 1, 1),
(13, 'relation_id', '{\n\"tr\":\"Bağlı ID\"\n}', 'number', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:05:41', '2023-01-04 22:11:50', 1, 1),
(14, 'relation_fields', '{\n\"tr\":\"Bağlı kolonlar\"\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, 'fields', 'id', '[\"name\",\"display\"]', NULL, NULL, 0, 1, '', '2023-01-04 16:05:41', '2023-01-04 22:11:48', 1, 1),
(17, 'mask', '{\n\"tr\":\"Maske\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:06:53', '2023-01-04 22:11:46', 1, 1),
(18, 'regex', '{\n\"tr\":\"Regex\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:06:53', '2023-01-04 22:11:43', 1, 1),
(19, 'lang_support', '{\n\"tr\":\"Dil Desteği\"\n}', 'bool', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:08:23', '2023-01-04 22:11:41', 1, 1),
(20, 'auths_type', '{\n\"tr\":\"Yetki Tipleri\"\n}', 'array', '[\'list\',\'show\',\'create\',\'edit\',\'delete\']', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:08:23', '2023-01-04 22:11:39', 1, 1),
(21, 'table_name', '{\n\"tr\":\"Tablo adı\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, 'lists', 'id', '[\"name\",\"display\"]', NULL, NULL, 0, 1, '', '2023-01-04 16:09:54', '2023-01-04 22:11:37', 1, 1),
(22, 'hide_fields', '{\n\"tr\":\"Gizli kolonlar\"\n}', 'array', NULL, NULL, NULL, NULL, NULL, 'fields', 'id', '[\"name\",\"display\"]', NULL, NULL, 0, 1, '', '2023-01-04 16:09:54', '2023-01-04 22:11:35', 1, 1),
(23, 'auths_group', '{\n\"tr\":\"Yetki grubu\"\n}', 'number', NULL, NULL, NULL, NULL, NULL, 'auths_group', 'id', '[\"name\",\"display\"]', NULL, NULL, 0, 1, '', '2023-01-04 16:12:00', '2023-01-05 00:27:53', 1, 1),
(24, 'surnam', '{\n\"tr\":\"Soyad\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:13:26', '2023-01-04 22:11:31', 1, 1),
(25, 'email', '{\n\"tr\":\"E-posta\"\n}', 'email', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:13:26', '2023-01-04 22:11:28', 1, 1),
(26, 'phone', '{\n\"tr\":\"Telefon\"\n}', 'phone', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:13:58', '2023-01-04 22:11:26', 1, 1),
(27, 'password', '{\n\"tr\":\"Şifre\"\n}', 'pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:13:58', '2023-01-04 22:11:24', 1, 1),
(28, 'settings', '{\n\"tr\":\"Ayarlar\"\n}', 'json', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:14:39', '2023-01-04 22:11:22', 1, 1),
(29, 'token', '{\n\"tr\":\"Token\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:14:39', '2023-01-04 22:11:17', 1, 1),
(30, 'site_name', '{\n\"tr\":\"Site ismi\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:15:34', '2023-01-04 22:11:15', 1, 1),
(31, 'logo', '{\n\"tr\":\"Logo\"\n}', 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:15:34', '2023-01-04 22:11:12', 1, 1),
(32, 'origins', '{\n\"tr\":\"Origins(izin verilen siteler)\"\n}', 'array', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:16:39', '2023-01-04 22:11:10', 1, 1),
(33, 'smtp_email', '{\n\"tr\":\"SMTP E-posta\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:16:39', '2023-01-04 22:11:07', 1, 1),
(34, 'smtp_password', '{\n\"tr\":\"SMTP Şifre\"}', 'pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:17:28', '2023-01-04 22:11:05', 1, 1),
(35, 'smtp_name', '{\n\"tr\":\"SMTP İsim\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:17:28', '2023-01-04 22:11:02', 1, 1),
(36, 'smtp_host', '{\n\"tr\":\"SMTP Host\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:18:17', '2023-01-04 22:11:00', 1, 1),
(37, 'smtp_port', '{\n\"tr\":\"SMTP Port\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:18:17', '2023-01-04 22:10:58', 1, 1),
(38, 'method_name', '{\n\"tr\":\"Method ismi\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:19:06', '2023-01-04 22:10:55', 1, 1),
(39, 'url', '{\n\"tr\":\"Link\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:19:53', '2023-01-04 22:10:53', 1, 1),
(40, 'user_ip', '{\n\"tr\":\"Kullanıcı IP\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:19:53', '2023-01-04 22:10:50', 1, 1),
(41, 'title', '{\n\"tr\":\"Başlık\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:20:30', '2023-01-04 22:10:48', 1, 1),
(42, 'content', '{\n\"tr\":\"İçerik\"\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:20:30', '2023-01-04 22:10:44', 1, 1),
(43, 'own_id', '{\n\"tr\":\"Kayıt Sahibi\"\n}', 'number', NULL, NULL, NULL, NULL, NULL, 'users', 'id', '[\"name\",\"surname\"]', NULL, NULL, 0, 1, '', '2023-01-04 20:35:24', '2023-01-04 22:09:07', 1, 1),
(49, 'user_id', '{\n\"tr\":\"Kaydı Güncelleyen\"\n}', 'number', NULL, NULL, NULL, NULL, NULL, 'users', 'id', '[\"name\",\"surname\"]', NULL, NULL, 0, 1, '', '2023-01-04 20:35:24', '2023-01-04 22:09:04', 1, 1),
(50, 'state', '{\n\"tr\":\"Durum\"\n}', 'bool', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 21:27:12', '2023-01-04 22:09:01', 1, 1),
(51, 'description', '{\n\"tr\":\"Açıklama\"\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 21:27:51', '2023-01-04 22:08:59', 1, 1),
(52, 'created_at', '{\n\"tr\":\"Eklenme Zamanı\"\n}', 'datetime', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 21:28:22', '2023-01-04 22:08:55', 1, 1),
(53, 'updated_at', '{\"tr\":\"Güncellenme Zamanı\"}', 'datetime', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 21:28:50', '2023-01-04 22:08:52', 1, 1),
(54, 'surname', '{\n\"tr\":\"Soyad\"\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 21:56:03', '2023-01-04 22:08:50', 1, 1),
(55, 'enums', '{\"tr\":\"Enums\"}', 'array', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 22:40:04', '2023-01-04 22:40:04', 1, 1),
(56, 'min_value', '{\"tr\":\"En Düşük Değer\"}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 22:41:02', '2023-01-04 22:41:02', 1, 1),
(57, 'max_value', '{\"tr\":\"En Büyük Değer\"}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 22:41:02', '2023-01-04 22:41:02', 1, 1),
(58, 'relation_columns', '{\r\n\"tr\":\"Bağlanacak Kolonlar\"\r\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 22:41:55', '2023-01-04 22:41:55', 1, 1),
(60, 'hide_column', '{\"tr\":\"Gizli Kolonlar\"}', 'array', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-05 00:00:08', '2023-01-05 00:00:08', 1, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `lists`
--

CREATE TABLE `lists` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `display` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `fields` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `before_codes` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `after_codes` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `lists`
--

INSERT INTO `lists` (`id`, `name`, `display`, `fields`, `before_codes`, `after_codes`, `state`, `description`, `created_at`, `updated_at`, `own_id`, `user_id`) VALUES
(1, 'auths', '{\n\"tr\":\"Yetkiler\",\n\"en\":\"Auths\"\n}', '[\"id\",\"name\",\"type\",\"table_name\",\"hide_column\",\"auths_group\",\"state\",\"description\",\"created_at\",\"updated_at\",\"own_id\",\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:22:27', '2023-01-05 00:00:38', 1, 1),
(2, 'auths_group', '{\n\"tr\":\"Yetki grupları\"\n}', '[\"id\",\"name\",\"display\",\"state\",\"description\",\"created_at\",\"updated_at\",\"own_id\",\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:23:40', '2023-01-05 00:59:03', 1, 1),
(3, 'fields', '{\"tr\":\"Kolonlar\"}', '[\"id\",\"name\",\"display\",\"type\",\"enums\",\"min_length\",\"max_length\",\"min_value\",\"max_value\",\"parent_table\",\"relation_ids\",\"relation_columns\",\"mask\",\"regex\",\"lang_support\",\"state\",\"description\",\"created_at\",\"updated_at\",\"own_id\",\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:26:34', '2023-01-05 00:44:19', 1, 1),
(4, 'lists', '{\"tr\":\"Tablolar\"}', '[\"id\",\"name\",\"display\",\"fields\",\"before_codes\",\"after_codes\",\"state\",\"description\",\"created_at\",\"updated_at\",\"own_id\",\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:28:39', '2023-01-04 23:33:33', 1, 1),
(5, 'logs', '{\"tr\":\"Loglar\"}', '[\n\"id\",\n\"method_name\",	\n\"url\",	\n\"user_ip\",\n\"state\",\n\"description\",	\n\"created_at\",	\n\"updated_at\",	\n\"own_id\",\n\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:29:43', '2023-01-04 23:27:53', 1, 1),
(6, 'settings', '{\"tr\":\"Ayarlar\"}', '[\n\"id\",\n\"site_name\",	\n\"logo\",\"origins\",\"smpt_email\",\"smpt_name\",\"smpt_password\",\"smtp_host\",\"smtp_port\",\n\"state\",\n\"description\",	\n\"created_at\",	\n\"updated_at\",	\n\"own_id\",\n\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:30:44', '2023-01-04 23:11:52', 1, 1),
(7, 'users', '{\"tr\":\"Kullanıcılar\"}', '[\"id\",\"name\",\"surname\",\"email\",\"password\",\"phone\",\"settings\",\"auths_group\",\"token\",\"state\",\"description\",\"created_at\",\"updated_at\",\"own_id\",\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:30:44', '2023-01-04 23:29:05', 1, 1),
(8, 'default_emails', '{\"tr\":\"Varsayılan Epostalar\"}', '[\n\"id\",\n\"name\",	\n\"title\",\"content\",\n\"state\",\n\"description\",	\n\"created_at\",	\n\"updated_at\",	\n\"own_id\",\n\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:30:44', '2023-01-04 23:27:35', 1, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `method_name` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `url` varchar(500) COLLATE utf8_turkish_ci DEFAULT NULL,
  `user_ip` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `logo` varchar(500) COLLATE utf8_turkish_ci DEFAULT NULL,
  `origins` text COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'localhost,erdoganyesil.com.tr',
  `smpt_email` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `smpt_name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `smtp_password` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `smtp_host` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `smtp_port` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `surname` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `email` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `phone` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `password` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `settings` text COLLATE utf8_turkish_ci DEFAULT NULL,
  `auths_group` int(11) DEFAULT NULL,
  `token` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `surname`, `email`, `phone`, `password`, `settings`, `auths_group`, `token`, `state`, `description`, `created_at`, `updated_at`, `own_id`, `user_id`) VALUES
(1, 'Robot', 'Kullanıcı', 'robot@erdoganyesil.com.tr', '+90(000) 000 00 00', 'RobotKullanıcı', NULL, 1, NULL, 1, '', '2023-01-04 20:31:30', '2023-01-05 00:37:32', 1, 1);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `auths`
--
ALTER TABLE `auths`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `auths_group`
--
ALTER TABLE `auths_group`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `lists`
--
ALTER TABLE `lists`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `auths`
--
ALTER TABLE `auths`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `auths_group`
--
ALTER TABLE `auths_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Tablo için AUTO_INCREMENT değeri `lists`
--
ALTER TABLE `lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
