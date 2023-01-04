-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:8889
-- Üretim Zamanı: 04 Oca 2023, 13:37:29
-- Sunucu sürümü: 5.7.34
-- PHP Sürümü: 7.4.21

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
  `type` enum('list','create','edit','delete','show') COLLATE utf8_turkish_ci DEFAULT NULL,
  `table_name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `hide_column` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL,
  `auth_group` int(11) DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `auths_group`
--

CREATE TABLE `auths_group` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `default_emails`
--

CREATE TABLE `default_emails` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `title` varchar(500) COLLATE utf8_turkish_ci DEFAULT NULL,
  `content` text COLLATE utf8_turkish_ci,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `display` text COLLATE utf8_turkish_ci COMMENT ' örn:{tr:"İsim",en:"Name"}',
  `type` enum('sort_text','long_text','number','bool','file','image','phone','email','datetime','date','pass','array','json') COLLATE utf8_turkish_ci NOT NULL,
  `enums` text COLLATE utf8_turkish_ci,
  `min_length` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `max_length` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `min_value` double DEFAULT NULL,
  `max_value` double DEFAULT NULL,
  `parent_table` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `relation_ids` varchar(11) COLLATE utf8_turkish_ci DEFAULT NULL,
  `relation_columns` text COLLATE utf8_turkish_ci,
  `mask` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `regex` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `lang_support` tinyint(1) DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `own_id` int(11) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `fields`
--

INSERT INTO `fields` (`id`, `name`, `display`, `type`, `enums`, `min_length`, `max_length`, `min_value`, `max_value`, `parent_table`, `relation_ids`, `relation_columns`, `mask`, `regex`, `lang_support`, `state`, `description`, `created_at`, `updated_at`, `own_id`, `user_id`) VALUES
(1, 'id', '{\r\ntr:\"ID\",\r\n}', 'number', NULL, '1', '11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 15:55:15', '2023-01-04 15:55:15', 1, 1),
(2, 'name', '{\r\ntr:\"İsim\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 15:56:35', '2023-01-04 15:56:35', 1, 1),
(3, 'display', '{\r\ntr:\"Görüntüleme\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '', '2023-01-04 15:56:59', '2023-01-04 15:56:59', 1, 1),
(4, 'fields', '{\r\ntr:\"Kolonlar\",\r\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, 'fields', '[\'id\']', '[\'name\',\'display\']', NULL, NULL, 0, 1, '', '2023-01-04 15:58:06', '2023-01-04 16:12:14', 1, 1),
(5, 'before_codes', '{\r\ntr:\"Önce çalışacaklar\",\r\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 15:59:01', '2023-01-04 15:59:01', 1, 1),
(6, 'after_codes', '{\r\ntr:\"Sonra Çalışacaklar\",\r\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 15:59:29', '2023-01-04 15:59:29', 1, 1),
(7, 'type', '{\r\ntr:\"Kolon Tipleri\",\r\n}', 'array', '[\'sort_text\',\'long_text\',\'number\',\'bool\',\'file\',\'image\',\'phone\',\'email\',\'datetime\',\'date\',\'pass\']', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:01:59', '2023-01-04 16:01:59', 1, 1),
(8, 'min_length', '{\r\ntr:\"En az uzunluk\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:02:51', '2023-01-04 16:02:51', 1, 1),
(9, 'max_length', '{\r\ntr:\"En fazla uzunluk\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:03:33', '2023-01-04 16:03:33', 1, 1),
(10, 'min', '{\r\ntr:\"En küçük değer\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:03:33', '2023-01-04 16:03:33', 1, 1),
(11, 'max', '{\r\ntr:\"En büyük değer\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:04:29', '2023-01-04 16:04:29', 1, 1),
(12, 'relation_table', '{\r\ntr:\"Bağlı tablo\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:04:29', '2023-01-04 16:06:17', 1, 1),
(13, 'relation_id', '{\r\ntr:\"Bağlı ID\",\r\n}', 'number', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:05:41', '2023-01-04 16:05:41', 1, 1),
(14, 'relation_fields', '{\r\ntr:\"Bağlı kolonlar\",\r\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, 'fields', NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:05:41', '2023-01-04 16:05:41', 1, 1),
(17, 'mask', '{\r\ntr:\"Maske\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:06:53', '2023-01-04 16:06:53', 1, 1),
(18, 'regex', '{\r\ntr:\"Regex\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:06:53', '2023-01-04 16:06:53', 1, 1),
(19, 'lang_support', '{\r\ntr:\"Dil Desteği\",\r\n}', 'bool', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:08:23', '2023-01-04 16:08:23', 1, 1),
(20, 'auths_type', '{\r\ntr:\"Yetki Tipleri\",\r\n}', 'array', '[\'list\',\'show\',\'create\',\'edit\',\'delete\']', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:08:23', '2023-01-04 16:08:23', 1, 1),
(21, 'table_name', '{\r\ntr:\"Tablo adı\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, 'lists', NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:09:54', '2023-01-04 16:09:54', 1, 1),
(22, 'hide_fields', '{\r\ntr:\"Gizli kolonlar\",\r\n}', 'array', NULL, NULL, NULL, NULL, NULL, 'fields', NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:09:54', '2023-01-04 16:09:54', 1, 1),
(23, 'auth_group', '{\r\ntr:\"Yetki grubu\",\r\n}', 'number', NULL, NULL, NULL, NULL, NULL, 'auths', '[\'id\']', '[\'name\']', NULL, NULL, 0, 1, '', '2023-01-04 16:12:00', '2023-01-04 16:12:00', 1, 1),
(24, 'surnam', '{\r\ntr:\"Soyad\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:13:26', '2023-01-04 16:13:26', 1, 1),
(25, 'email', '{\r\ntr:\"E-posta\",\r\n}', 'email', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:13:26', '2023-01-04 16:13:26', 1, 1),
(26, 'phone', '{\r\ntr:\"Telefon\",\r\n}', 'phone', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:13:58', '2023-01-04 16:13:58', 1, 1),
(27, 'password', '{\r\ntr:\"Şifre\",\r\n}', 'pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:13:58', '2023-01-04 16:13:58', 1, 1),
(28, 'settings', '{\r\ntr:\"Ayarlar\",\r\n}', 'json', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:14:39', '2023-01-04 16:14:39', 1, 1),
(29, 'token', '{\r\ntr:\"Token\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:14:39', '2023-01-04 16:14:39', 1, 1),
(30, 'site_name', '{\r\ntr:\"Site ismi\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:15:34', '2023-01-04 16:15:34', 1, 1),
(31, 'logo', '{\r\ntr:\"Logo\",\r\n}', 'image', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:15:34', '2023-01-04 16:15:34', 1, 1),
(32, 'origins', '{\r\ntr:\"Origins(izin verilen siteler)\",\r\n}', 'array', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:16:39', '2023-01-04 16:16:39', 1, 1),
(33, 'smtp_email', '{\r\ntr:\"SMTP E-posta\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:16:39', '2023-01-04 16:16:39', 1, 1),
(34, 'smtp_password', '{\r\ntr:\"SMTP Şifre\",\r\n}', 'pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:17:28', '2023-01-04 16:17:28', 1, 1),
(35, 'smtp_name', '{\r\ntr:\"SMTP İsim\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:17:28', '2023-01-04 16:17:28', 1, 1),
(36, 'smtp_host', '{\r\ntr:\"SMTP Host\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:18:17', '2023-01-04 16:18:17', 1, 1),
(37, 'smtp_port', '{\r\ntr:\"SMTP Port\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:18:17', '2023-01-04 16:18:17', 1, 1),
(38, 'method_name', '{\r\ntr:\"Method ismi\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:19:06', '2023-01-04 16:19:06', 1, 1),
(39, 'url', '{\r\ntr:\"Link\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:19:53', '2023-01-04 16:19:53', 1, 1),
(40, 'user_ip', '{\r\ntr:\"Kullanıcı IP\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:19:53', '2023-01-04 16:19:53', 1, 1),
(41, 'title', '{\r\ntr:\"Başlık\",\r\n}', 'sort_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:20:30', '2023-01-04 16:20:30', 1, 1),
(42, 'content', '{\r\ntr:\"İçerik\",\r\n}', 'long_text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '', '2023-01-04 16:20:30', '2023-01-04 16:20:30', 1, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `lists`
--

CREATE TABLE `lists` (
  `id` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `display` text COLLATE utf8_turkish_ci,
  `fields` text COLLATE utf8_turkish_ci,
  `before_codes` text COLLATE utf8_turkish_ci,
  `after_codes` text COLLATE utf8_turkish_ci,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `lists`
--

INSERT INTO `lists` (`id`, `name`, `display`, `fields`, `before_codes`, `after_codes`, `state`, `description`, `created_at`, `updated_at`, `own_id`, `user_id`) VALUES
(1, 'auths', '{\r\ntr:\"Yetkiler\",\r\n}', '[\"id\",\"name\",\"type\",\"table_name\",\"hide_column\"	\"auth_group\",\"state\",\"description\",\"created_at\",\"updated_at\",\"own_id\",\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:22:27', '2023-01-04 16:22:27', 1, 1),
(2, 'auths_group', '{\r\ntr:\"Yetki grupları\"\r\n}', '[\"id\",\"name\",\"state\",\"description\",\"created_at\",\"updated_at\",\"own_id\",\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:23:40', '2023-01-04 16:23:40', 1, 1),
(3, 'fields', '{\r\ntr:\"Kolonlar\"\r\n}', '[\r\n\"id\",\r\n\"name\",\r\n\"display\",\r\n\"type\",\r\n\"enums\",\r\n\"min_length\",\r\n\"max_length\",	\r\n\"min_value\",	\r\n\"max_value\",	\r\n\"parent_table\",\r\n\"relation_ids\",	\r\n\"relation_columns\",	\r\n\"mask\",\r\n\"regex\",	\r\n\"lang_support\",\r\n\"state\",\r\n\"description\",\r\n\"created_at\",	\r\n\"updated_at\",	\r\n\"own_id\",\r\n\"user_id\",', NULL, NULL, 1, '', '2023-01-04 16:26:34', '2023-01-04 16:26:34', 1, 1),
(4, 'lists', '{tr:\"Tablolar\"}', '[\n\"id\",\n\"name\",	\n\"display\",	\n\"fields\",\n\"before_codes\",	\n\"after_codes\",\n\"state\",\n\"description\",	\n\"created_at\",	\n\"updated_at\",	\n\"own_id\",\n\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:28:39', '2023-01-04 16:29:21', 1, 1),
(5, 'logs', '{tr:\"Loglar\"}', '[\r\n\"id\",\r\n\"method_name\",	\r\n\"url\",	\r\n\"user_ip\",\r\n\"state\",\r\n\"description\",	\r\n\"created_at\",	\r\n\"updated_at\",	\r\n\"own_id\",\r\n\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:29:43', '2023-01-04 16:29:43', 1, 1),
(6, 'settings', '{tr:\"Ayarlar\"}', '[\n\"id\",\n\"site_name\",	\n\"logo\",\"origins\",\"smpt_email\",\"smpt_name\",\"smpt_password\",\"smtp_host\",\"smtp_port\",\n\"state\",\n\"description\",	\n\"created_at\",	\n\"updated_at\",	\n\"own_id\",\n\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:30:44', '2023-01-04 16:31:43', 1, 1),
(7, 'users', '{tr:\"Kullanıcılar\"}', '[\r\n\"id\",\r\n\"name\",	\r\n\"surname\",\"email\",\"password\",\"phone\",\"settings\",\"auths_group\",\"token\",\r\n\"state\",\r\n\"description\",	\r\n\"created_at\",	\r\n\"updated_at\",	\r\n\"own_id\",\r\n\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:30:44', '2023-01-04 16:31:43', 1, 1),
(8, 'default_emails', '{tr:\"Varsayılan Epostalar\"}', '[\r\n\"id\",\r\n\"name\",	\r\n\"title\",\"content\",\r\n\"state\",\r\n\"description\",	\r\n\"created_at\",	\r\n\"updated_at\",	\r\n\"own_id\",\r\n\"user_id\"]', NULL, NULL, 1, '', '2023-01-04 16:30:44', '2023-01-04 16:33:43', 1, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `method_name` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `url` varchar(500) COLLATE utf8_turkish_ci DEFAULT NULL,
  `user_ip` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `origins` text COLLATE utf8_turkish_ci COMMENT 'localhost,erdoganyesil.com.tr',
  `smpt_email` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `smpt_name` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `smtp_password` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `smtp_host` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `smtp_port` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `settings` text COLLATE utf8_turkish_ci,
  `auths_group` int(11) DEFAULT NULL,
  `token` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(500) COLLATE utf8_turkish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `own_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

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
-- Tablo için indeksler `default_emails`
--
ALTER TABLE `default_emails`
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
-- Tablo için indeksler `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
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
-- Tablo için AUTO_INCREMENT değeri `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Tablo için AUTO_INCREMENT değeri `lists`
--
ALTER TABLE `lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
