-- AutoShop – cơ sở dữ liệu MySQL 8 (schema + dữ liệu mẫu)
-- Tạo bởi database/autoshop.sql. Import bằng HeidiSQL/phpMyAdmin hoặc: mysql -u root < database/autoshop.sql
-- Tài khoản demo: admin@autoshop.test / password, khach@autoshop.test / password

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
CREATE DATABASE IF NOT EXISTS `autoshop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `autoshop`;

DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `attributes`;
DROP TABLE IF EXISTS `cars`;
DROP TABLE IF EXISTS `car_attribute_values`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `payment_transactions`;
DROP TABLE IF EXISTS `test_drives`;
DROP TABLE IF EXISTS `wishlists`;
DROP TABLE IF EXISTS `migrations`;

-- Migration: 0001_01_01_000000_create_users_table
create table `users` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(191) not null, `email` varchar(191) not null, `phone` varchar(20) null, `role` varchar(20) not null default 'customer', `email_verified_at` timestamp null, `password` varchar(191) not null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `users` add unique `users_email_unique`(`email`);
create table `password_reset_tokens` (`email` varchar(191) not null, `token` varchar(191) not null, `created_at` timestamp null, primary key (`email`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `sessions` (`id` varchar(191) not null, `user_id` bigint unsigned null, `ip_address` varchar(45) null, `user_agent` text null, `payload` longtext not null, `last_activity` int not null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `sessions` add index `sessions_user_id_index`(`user_id`);
alter table `sessions` add index `sessions_last_activity_index`(`last_activity`);
-- Migration: 0001_01_01_000001_create_cache_table
create table `cache` (`key` varchar(191) not null, `value` mediumtext not null, `expiration` bigint not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `cache` add index `cache_expiration_index`(`expiration`);
create table `cache_locks` (`key` varchar(191) not null, `owner` varchar(191) not null, `expiration` bigint not null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `cache_locks` add index `cache_locks_expiration_index`(`expiration`);
-- Migration: 0001_01_01_000002_create_jobs_table
create table `jobs` (`id` bigint unsigned not null auto_increment primary key, `queue` varchar(191) not null, `payload` longtext not null, `attempts` smallint unsigned not null, `reserved_at` int unsigned null, `available_at` int unsigned not null, `created_at` int unsigned not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `jobs` add index `jobs_queue_index`(`queue`);
create table `job_batches` (`id` varchar(191) not null, `name` varchar(191) not null, `total_jobs` int not null, `pending_jobs` int not null, `failed_jobs` int not null, `failed_job_ids` longtext not null, `options` mediumtext null, `cancelled_at` int null, `created_at` int not null, `finished_at` int null, primary key (`id`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
create table `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(191) not null, `connection` varchar(191) not null, `queue` varchar(191) not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `failed_jobs` add index `failed_jobs_connection_queue_failed_at_index`(`connection`, `queue`, `failed_at`);
alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`);
-- Migration: 2026_10_18_000001_create_catalog_tables
create table `categories` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(191) not null, `slug` varchar(191) not null, `description` varchar(191) null, `sort_order` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `categories` add unique `categories_slug_unique`(`slug`);
create table `brands` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(191) not null, `slug` varchar(191) not null, `country` varchar(60) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `brands` add unique `brands_slug_unique`(`slug`);
create table `attributes` (`id` bigint unsigned not null auto_increment primary key, `category_id` bigint unsigned not null, `code` varchar(60) not null, `name` varchar(191) not null, `type` varchar(20) not null default 'text', `unit` varchar(20) null, `options` json null, `is_required` tinyint(1) not null default '0', `is_filterable` tinyint(1) not null default '0', `sort_order` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `attributes` add constraint `attributes_category_id_foreign` foreign key (`category_id`) references `categories` (`id`) on delete cascade;
alter table `attributes` add unique `attributes_category_id_code_unique`(`category_id`, `code`);
create table `cars` (`id` bigint unsigned not null auto_increment primary key, `category_id` bigint unsigned not null, `brand_id` bigint unsigned null, `sku` varchar(40) not null, `name` varchar(191) not null, `slug` varchar(191) not null, `year` smallint unsigned not null, `condition` varchar(10) not null default 'new', `mileage` int unsigned not null default '0', `color` varchar(40) null, `color_hex` varchar(7) not null default '#1f6feb', `price` bigint unsigned not null, `sale_price` bigint unsigned null, `stock` int unsigned not null default '0', `status` varchar(20) not null default 'draft', `completeness` tinyint unsigned not null default '0', `is_featured` tinyint(1) not null default '0', `image` varchar(191) null, `gallery` json null, `short_description` varchar(255) null, `description` text null, `views` int unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null, `deleted_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `cars` add constraint `cars_category_id_foreign` foreign key (`category_id`) references `categories` (`id`) on delete restrict;
alter table `cars` add constraint `cars_brand_id_foreign` foreign key (`brand_id`) references `brands` (`id`) on delete set null;
alter table `cars` add index `cars_status_category_id_index`(`status`, `category_id`);
alter table `cars` add unique `cars_sku_unique`(`sku`);
alter table `cars` add unique `cars_slug_unique`(`slug`);
create table `car_attribute_values` (`id` bigint unsigned not null auto_increment primary key, `car_id` bigint unsigned not null, `attribute_id` bigint unsigned not null, `value` varchar(191) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `car_attribute_values` add constraint `car_attribute_values_car_id_foreign` foreign key (`car_id`) references `cars` (`id`) on delete cascade;
alter table `car_attribute_values` add constraint `car_attribute_values_attribute_id_foreign` foreign key (`attribute_id`) references `attributes` (`id`) on delete cascade;
alter table `car_attribute_values` add unique `car_attribute_values_car_id_attribute_id_unique`(`car_id`, `attribute_id`);
alter table `car_attribute_values` add index `car_attribute_values_attribute_id_value_index`(`attribute_id`, `value`);
-- Migration: 2026_10_18_000002_create_order_tables
create table `orders` (`id` bigint unsigned not null auto_increment primary key, `code` varchar(20) not null, `user_id` bigint unsigned null, `customer_name` varchar(191) not null, `customer_phone` varchar(20) not null, `customer_email` varchar(191) null, `customer_id_number` varchar(20) null, `address` varchar(191) not null, `showroom` varchar(100) not null, `note` text null, `subtotal` bigint unsigned not null, `registration_fee` bigint unsigned not null, `total` bigint unsigned not null, `deposit_amount` bigint unsigned not null, `payment_method` varchar(20) not null, `payment_status` varchar(20) not null default 'unpaid', `status` varchar(20) not null default 'pending', `cancelled_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `orders` add constraint `orders_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;
alter table `orders` add index `orders_status_payment_status_index`(`status`, `payment_status`);
alter table `orders` add unique `orders_code_unique`(`code`);
create table `order_items` (`id` bigint unsigned not null auto_increment primary key, `order_id` bigint unsigned not null, `car_id` bigint unsigned null, `name` varchar(191) not null, `sku` varchar(40) not null, `color` varchar(40) null, `year` smallint unsigned not null, `price` bigint unsigned not null, `quantity` int unsigned not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `order_items` add constraint `order_items_order_id_foreign` foreign key (`order_id`) references `orders` (`id`) on delete cascade;
alter table `order_items` add constraint `order_items_car_id_foreign` foreign key (`car_id`) references `cars` (`id`) on delete set null;
create table `payment_transactions` (`id` bigint unsigned not null auto_increment primary key, `order_id` bigint unsigned not null, `gateway` varchar(20) not null, `transaction_code` varchar(40) not null, `amount` bigint unsigned not null, `status` varchar(20) not null default 'pending', `gateway_transaction_no` varchar(191) null, `raw_response` json null, `paid_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `payment_transactions` add constraint `payment_transactions_order_id_foreign` foreign key (`order_id`) references `orders` (`id`) on delete cascade;
alter table `payment_transactions` add unique `payment_transactions_transaction_code_unique`(`transaction_code`);
create table `test_drives` (`id` bigint unsigned not null auto_increment primary key, `car_id` bigint unsigned not null, `user_id` bigint unsigned null, `name` varchar(191) not null, `phone` varchar(20) not null, `email` varchar(191) null, `showroom` varchar(100) not null, `preferred_date` date not null, `preferred_time` varchar(5) not null, `note` text null, `status` varchar(20) not null default 'new', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `test_drives` add constraint `test_drives_car_id_foreign` foreign key (`car_id`) references `cars` (`id`) on delete cascade;
alter table `test_drives` add constraint `test_drives_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;
create table `wishlists` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned not null, `car_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `wishlists` add constraint `wishlists_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `wishlists` add constraint `wishlists_car_id_foreign` foreign key (`car_id`) references `cars` (`id`) on delete cascade;
alter table `wishlists` add unique `wishlists_user_id_car_id_unique`(`user_id`, `car_id`);

create table `migrations` (`id` int unsigned not null auto_increment primary key, `migration` varchar(255) not null, `batch` int not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;

-- Dữ liệu bảng users
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`email_verified_at`,`password`,`remember_token`,`created_at`,`updated_at`) VALUES
(1,'Quản trị AutoShop','admin@autoshop.test','0909000001','admin',NULL,'$2y$12$gTwrZz24ZDv5CvjMvdPmJe63AigDLIkvm61dZgKDFICbb.FT.r.6K',NULL,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(2,'Nguyễn Văn Khách','khach@autoshop.test','0909000002','customer',NULL,'$2y$12$QqpcUuzBt3etLqncNetBdOqdF38sBBsmiiYUYY1Wz.IeqFZcIyyxa',NULL,'2026-10-18 16:15:08','2026-10-18 16:15:08');

-- Dữ liệu bảng categories
INSERT INTO `categories` (`id`,`name`,`slug`,`description`,`sort_order`,`created_at`,`updated_at`) VALUES
(1,'Sedan','sedan','Xe 4 cửa, 5 chỗ, cốp tách rời – êm ái, tiết kiệm.',0,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(2,'SUV / Crossover','suv-crossover','Gầm cao, rộng rãi, phù hợp gia đình và đi đường xa.',1,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(3,'Bán tải','ban-tai','Thùng hàng phía sau, khỏe khoắn, vừa đi làm vừa chở hàng.',2,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(4,'Hatchback','hatchback','Nhỏ gọn, dễ xoay xở trong phố, chi phí thấp.',3,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(5,'Xe điện','xe-dien','Không phát thải, vận hành êm, chi phí sạc thấp.',4,'2026-10-18 16:15:08','2026-10-18 16:15:08');

-- Dữ liệu bảng brands
INSERT INTO `brands` (`id`,`name`,`slug`,`country`,`created_at`,`updated_at`) VALUES
(1,'Toyota','toyota','Nhật Bản','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(2,'Honda','honda','Nhật Bản','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(3,'Mazda','mazda','Nhật Bản','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(4,'Mitsubishi','mitsubishi','Nhật Bản','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(5,'Hyundai','hyundai','Hàn Quốc','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(6,'Kia','kia','Hàn Quốc','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(7,'Ford','ford','Mỹ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(8,'VinFast','vinfast','Việt Nam','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(9,'Mercedes-Benz','mercedes-benz','Đức','2026-10-18 16:15:08','2026-10-18 16:15:08');

-- Dữ liệu bảng attributes
INSERT INTO `attributes` (`id`,`category_id`,`code`,`name`,`type`,`unit`,`options`,`is_required`,`is_filterable`,`sort_order`,`created_at`,`updated_at`) VALUES
(1,1,'nhien_lieu','Nhiên liệu','select',NULL,'["X\u0103ng","D\u1ea7u (Diesel)","Hybrid (X\u0103ng \u2013 \u0110i\u1ec7n)"]',1,1,0,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(2,1,'hop_so','Hộp số','select',NULL,'["S\u1ed1 t\u1ef1 \u0111\u1ed9ng","S\u1ed1 t\u1ef1 \u0111\u1ed9ng v\u00f4 c\u1ea5p CVT","S\u1ed1 s\u00e0n"]',1,1,1,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(3,1,'dong_co','Động cơ','text',NULL,NULL,1,0,2,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(4,1,'cong_suat','Công suất','number','mã lực',NULL,0,0,3,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(5,1,'tieu_hao','Mức tiêu hao','number','L/100km',NULL,0,0,4,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(6,2,'so_cho','Số chỗ ngồi','select',NULL,'["5 ch\u1ed7","7 ch\u1ed7"]',1,1,0,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(7,2,'nhien_lieu','Nhiên liệu','select',NULL,'["X\u0103ng","D\u1ea7u (Diesel)","Hybrid (X\u0103ng \u2013 \u0110i\u1ec7n)"]',1,1,1,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(8,2,'hop_so','Hộp số','select',NULL,'["S\u1ed1 t\u1ef1 \u0111\u1ed9ng","S\u1ed1 t\u1ef1 \u0111\u1ed9ng v\u00f4 c\u1ea5p CVT","S\u1ed1 s\u00e0n"]',1,0,2,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(9,2,'dan_dong','Hệ dẫn động','select',NULL,'["C\u1ea7u tr\u01b0\u1edbc (FWD)","Hai c\u1ea7u to\u00e0n th\u1eddi gian (AWD)","Hai c\u1ea7u 4x4"]',1,1,3,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(10,2,'dong_co','Động cơ','text',NULL,NULL,1,0,4,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(11,2,'cong_suat','Công suất','number','mã lực',NULL,0,0,5,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(12,3,'nhien_lieu','Nhiên liệu','select',NULL,'["X\u0103ng","D\u1ea7u (Diesel)","Hybrid (X\u0103ng \u2013 \u0110i\u1ec7n)"]',1,0,0,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(13,3,'hop_so','Hộp số','select',NULL,'["S\u1ed1 t\u1ef1 \u0111\u1ed9ng","S\u1ed1 t\u1ef1 \u0111\u1ed9ng v\u00f4 c\u1ea5p CVT","S\u1ed1 s\u00e0n"]',1,1,1,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(14,3,'dan_dong','Hệ dẫn động','select',NULL,'["M\u1ed9t c\u1ea7u 4x2","Hai c\u1ea7u 4x4"]',1,1,2,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(15,3,'dong_co','Động cơ','text',NULL,NULL,1,0,3,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(16,3,'tai_trong','Tải trọng thùng','number','kg',NULL,0,0,4,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(17,4,'nhien_lieu','Nhiên liệu','select',NULL,'["X\u0103ng","D\u1ea7u (Diesel)","Hybrid (X\u0103ng \u2013 \u0110i\u1ec7n)"]',1,0,0,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(18,4,'hop_so','Hộp số','select',NULL,'["S\u1ed1 t\u1ef1 \u0111\u1ed9ng","S\u1ed1 t\u1ef1 \u0111\u1ed9ng v\u00f4 c\u1ea5p CVT","S\u1ed1 s\u00e0n"]',1,1,1,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(19,4,'dong_co','Động cơ','text',NULL,NULL,1,0,2,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(20,4,'tui_khi','Số túi khí','number',NULL,NULL,0,1,3,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(21,5,'so_cho','Số chỗ ngồi','select',NULL,'["4 ch\u1ed7","5 ch\u1ed7","7 ch\u1ed7"]',1,1,0,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(22,5,'dung_luong_pin','Dung lượng pin','number','kWh',NULL,1,0,1,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(23,5,'quang_duong','Quãng đường mỗi lần sạc','number','km',NULL,1,1,2,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(24,5,'sac_nhanh','Sạc nhanh','text',NULL,NULL,0,0,3,'2026-10-18 16:15:08','2026-10-18 16:15:08'),
(25,5,'cong_suat','Công suất','number','mã lực',NULL,0,0,4,'2026-10-18 16:15:08','2026-10-18 16:15:08');

-- Dữ liệu bảng cars
INSERT INTO `cars` (`id`,`category_id`,`brand_id`,`sku`,`name`,`slug`,`year`,`condition`,`mileage`,`color`,`color_hex`,`price`,`sale_price`,`stock`,`status`,`completeness`,`is_featured`,`image`,`short_description`,`description`,`views`,`created_at`,`updated_at`,`deleted_at`) VALUES
(1,1,1,'XE1001','Toyota Vios 1.5G CVT','toyota-vios-15g-cvt-2025',2025,'new',0,'Trắng ngọc trai','#eef1f4',545000000,520000000,7,'published',100,1,NULL,'Toyota Vios 1.5G CVT – sedan thanh lịch, vận hành êm ái. Xe mới 100%, bảo hành chính hãng.','Toyota Vios 1.5G CVT phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',191,'2026-10-18 16:15:08','2026-10-18 16:15:09',NULL),
(2,1,2,'XE1002','Honda City RS','honda-city-rs-2025',2025,'new',0,'Đỏ cá tính','#c8102e',569000000,NULL,6,'published',100,0,NULL,'Honda City RS – sedan thanh lịch, vận hành êm ái. Xe mới 100%, bảo hành chính hãng.','Honda City RS phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',297,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(3,1,1,'XE1003','Toyota Camry 2.5HEV Top','toyota-camry-25hev-top-2025',2025,'new',0,'Đen ánh kim','#1b1d22',1530000000,NULL,2,'published',100,1,NULL,'Toyota Camry 2.5HEV Top – sedan thanh lịch, vận hành êm ái. Xe mới 100%, bảo hành chính hãng.','Toyota Camry 2.5HEV Top phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',233,'2026-10-18 16:15:08','2026-10-18 16:15:09',NULL),
(4,1,3,'XE1004','Mazda 3 1.5 Luxury','mazda-3-15-luxury-2025',2025,'new',0,'Xám xi măng','#8a8f94',699000000,669000000,5,'published',100,0,NULL,'Mazda 3 1.5 Luxury – sedan thanh lịch, vận hành êm ái. Xe mới 100%, bảo hành chính hãng.','Mazda 3 1.5 Luxury phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',541,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(5,1,6,'XE1005','Kia K3 2.0 Premium','kia-k3-20-premium-2025',2025,'new',0,'Xanh dương','#1d4e89',659000000,NULL,4,'published',100,0,NULL,'Kia K3 2.0 Premium – sedan thanh lịch, vận hành êm ái. Xe mới 100%, bảo hành chính hãng.','Kia K3 2.0 Premium phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',420,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(6,1,9,'XE1006','Mercedes-Benz C 300 AMG','mercedes-benz-c-300-amg-2024',2024,'new',0,'Bạc Iridium','#c0c4c8',2099000000,1999000000,2,'published',100,1,NULL,'Mercedes-Benz C 300 AMG – sedan thanh lịch, vận hành êm ái. Xe mới 100%, bảo hành chính hãng.','Mercedes-Benz C 300 AMG phiên bản 2024 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',176,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(7,1,5,'XE1007','Hyundai Accent 1.5 AT','hyundai-accent-15-at-2022',2022,'used',38000,'Trắng','#f3f3f3',449000000,429000000,1,'published',100,0,NULL,'Hyundai Accent 1.5 AT – sedan thanh lịch, vận hành êm ái. Xe đã qua sử dụng, kiểm định 176 hạng mục.','Hyundai Accent 1.5 AT đời 2022, đã đi 38.000 km, một chủ từ đầu, lịch sử bảo dưỡng đầy đủ tại hãng. Xe đã qua kiểm định chất lượng của AutoShop và được bảo hành 12 tháng hoặc 20.000 km.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',74,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(8,2,1,'XE1008','Toyota Corolla Cross 1.8HEV','toyota-corolla-cross-18hev-2025',2025,'new',0,'Xanh rêu','#4b5d3f',905000000,NULL,5,'published',100,1,NULL,'Toyota Corolla Cross 1.8HEV – gầm cao rộng rãi cho cả gia đình. Xe mới 100%, bảo hành chính hãng.','Toyota Corolla Cross 1.8HEV phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',365,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(9,2,2,'XE1009','Honda CR-V e:HEV RS','honda-cr-v-ehev-rs-2025',2025,'new',0,'Xanh đêm','#1c2b4a',1259000000,NULL,3,'published',100,1,NULL,'Honda CR-V e:HEV RS – gầm cao rộng rãi cho cả gia đình. Xe mới 100%, bảo hành chính hãng.','Honda CR-V e:HEV RS phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',212,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(10,2,3,'XE1010','Mazda CX-5 2.0 Premium','mazda-cx-5-20-premium-2025',2025,'new',0,'Đỏ pha lê','#9e1b32',829000000,799000000,6,'published',100,1,NULL,'Mazda CX-5 2.0 Premium – gầm cao rộng rãi cho cả gia đình. Xe mới 100%, bảo hành chính hãng.','Mazda CX-5 2.0 Premium phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',148,'2026-10-18 16:15:08','2026-10-18 16:15:09',NULL),
(11,2,5,'XE1011','Hyundai Santa Fe 2.5 Turbo Calligraphy','hyundai-santa-fe-25-turbo-calligraphy-2025',2025,'new',0,'Nâu đồng','#6b4f3a',1365000000,NULL,2,'published',100,0,NULL,'Hyundai Santa Fe 2.5 Turbo Calligraphy – gầm cao rộng rãi cho cả gia đình. Xe mới 100%, bảo hành chính hãng.','Hyundai Santa Fe 2.5 Turbo Calligraphy phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',185,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(12,2,6,'XE1012','Kia Seltos 1.5 Premium','kia-seltos-15-premium-2025',2025,'new',0,'Cam hoàng hôn','#d9652b',699000000,NULL,6,'published',100,0,NULL,'Kia Seltos 1.5 Premium – gầm cao rộng rãi cho cả gia đình. Xe mới 100%, bảo hành chính hãng.','Kia Seltos 1.5 Premium phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',459,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(13,2,7,'XE1013','Ford Everest Titanium+ 4x4','ford-everest-titanium-4x4-2025',2025,'new',0,'Xám Meteor','#5a5f66',1545000000,NULL,1,'published',100,1,NULL,'Ford Everest Titanium+ 4x4 – gầm cao rộng rãi cho cả gia đình. Xe mới 100%, bảo hành chính hãng.','Ford Everest Titanium+ 4x4 phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',30,'2026-10-18 16:15:08','2026-10-18 16:15:09',NULL),
(14,2,4,'XE1014','Mitsubishi Xpander AT Premium','mitsubishi-xpander-at-premium-2025',2025,'new',0,'Bạc','#b8bcc0',658000000,638000000,9,'published',100,0,NULL,'Mitsubishi Xpander AT Premium – gầm cao rộng rãi cho cả gia đình. Xe mới 100%, bảo hành chính hãng.','Mitsubishi Xpander AT Premium phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',336,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(15,2,1,'XE1015','Toyota Fortuner 2.4 AT 4x2','toyota-fortuner-24-at-4x2-2021',2021,'used',62000,'Đen','#202124',899000000,NULL,1,'published',100,0,NULL,'Toyota Fortuner 2.4 AT 4x2 – gầm cao rộng rãi cho cả gia đình. Xe đã qua sử dụng, kiểm định 176 hạng mục.','Toyota Fortuner 2.4 AT 4x2 đời 2021, đã đi 62.000 km, một chủ từ đầu, lịch sử bảo dưỡng đầy đủ tại hãng. Xe đã qua kiểm định chất lượng của AutoShop và được bảo hành 12 tháng hoặc 20.000 km.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',188,'2026-10-18 16:15:08','2026-10-18 16:15:08',NULL),
(16,3,7,'XE1016','Ford Ranger Wildtrak 2.0 4x4','ford-ranger-wildtrak-20-4x4-2025',2025,'new',0,'Cam Luxe','#d46a1f',979000000,NULL,3,'published',100,1,NULL,'Ford Ranger Wildtrak 2.0 4x4 – mạnh mẽ, đa dụng, sẵn sàng mọi cung đường. Xe mới 100%, bảo hành chính hãng.','Ford Ranger Wildtrak 2.0 4x4 phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',149,'2026-10-18 16:15:08','2026-10-18 16:15:09',NULL),
(17,3,1,'XE1017','Toyota Hilux 2.8 Adventure 4x4','toyota-hilux-28-adventure-4x4-2025',2025,'new',0,'Trắng','#f1f1f1',1005000000,NULL,3,'published',100,0,NULL,'Toyota Hilux 2.8 Adventure 4x4 – mạnh mẽ, đa dụng, sẵn sàng mọi cung đường. Xe mới 100%, bảo hành chính hãng.','Toyota Hilux 2.8 Adventure 4x4 phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',396,'2026-10-18 16:15:08','2026-10-18 16:15:09',NULL),
(18,3,4,'XE1018','Mitsubishi Triton Athlete 4x4','mitsubishi-triton-athlete-4x4-2025',2025,'new',0,'Xám đậm','#3d4146',924000000,899000000,5,'published',100,0,NULL,'Mitsubishi Triton Athlete 4x4 – mạnh mẽ, đa dụng, sẵn sàng mọi cung đường. Xe mới 100%, bảo hành chính hãng.','Mitsubishi Triton Athlete 4x4 phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',377,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(19,4,1,'XE1019','Toyota Wigo 1.2 AT','toyota-wigo-12-at-2025',2025,'new',0,'Vàng chanh','#e4c22b',405000000,NULL,9,'published',100,0,NULL,'Toyota Wigo 1.2 AT – nhỏ gọn, linh hoạt trong đô thị. Xe mới 100%, bảo hành chính hãng.','Toyota Wigo 1.2 AT phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',443,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(20,4,5,'XE1020','Hyundai Grand i10 1.2 AT','hyundai-grand-i10-12-at-2025',2025,'new',0,'Xanh ngọc','#2a9d8f',435000000,415000000,8,'published',100,0,NULL,'Hyundai Grand i10 1.2 AT – nhỏ gọn, linh hoạt trong đô thị. Xe mới 100%, bảo hành chính hãng.','Hyundai Grand i10 1.2 AT phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',121,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(21,4,6,'XE1021','Kia Morning GT-Line','kia-morning-gt-line-2025',2025,'new',0,'Đỏ','#b3202a',424000000,NULL,6,'published',100,0,NULL,'Kia Morning GT-Line – nhỏ gọn, linh hoạt trong đô thị. Xe mới 100%, bảo hành chính hãng.','Kia Morning GT-Line phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',506,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(22,4,3,'XE1022','Mazda 2 Sport 1.5 Luxury','mazda-2-sport-15-luxury-2023',2023,'used',21500,'Xanh khói','#5c7c8a',455000000,NULL,1,'published',100,0,NULL,'Mazda 2 Sport 1.5 Luxury – nhỏ gọn, linh hoạt trong đô thị. Xe đã qua sử dụng, kiểm định 176 hạng mục.','Mazda 2 Sport 1.5 Luxury đời 2023, đã đi 21.500 km, một chủ từ đầu, lịch sử bảo dưỡng đầy đủ tại hãng. Xe đã qua kiểm định chất lượng của AutoShop và được bảo hành 12 tháng hoặc 20.000 km.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',378,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(23,5,8,'XE1023','VinFast VF 3','vinfast-vf-3-2025',2025,'new',0,'Xanh lá Summer','#7cc16b',299000000,NULL,12,'published',100,1,NULL,'VinFast VF 3 – xe điện thông minh, không phát thải. Xe mới 100%, bảo hành chính hãng.','VinFast VF 3 phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',558,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(24,5,8,'XE1024','VinFast VF 5 Plus','vinfast-vf-5-plus-2025',2025,'new',0,'Vàng Cam','#f0a830',529000000,499000000,8,'published',100,0,NULL,'VinFast VF 5 Plus – xe điện thông minh, không phát thải. Xe mới 100%, bảo hành chính hãng.','VinFast VF 5 Plus phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',591,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(25,5,8,'XE1025','VinFast VF 6 Plus','vinfast-vf-6-plus-2025',2025,'new',0,'Xanh Dương Đậm','#16407a',765000000,NULL,6,'published',100,1,NULL,'VinFast VF 6 Plus – xe điện thông minh, không phát thải. Xe mới 100%, bảo hành chính hãng.','VinFast VF 6 Plus phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',95,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(26,5,8,'XE1026','VinFast VF 8 Plus','vinfast-vf-8-plus-2025',2025,'new',0,'Đỏ Crimson','#9b1c31',1199000000,1149000000,3,'published',100,0,NULL,'VinFast VF 8 Plus – xe điện thông minh, không phát thải. Xe mới 100%, bảo hành chính hãng.','VinFast VF 8 Plus phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',541,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(27,5,5,'XE1027','Hyundai Ioniq 5 Prestige','hyundai-ioniq-5-prestige-2024',2024,'new',0,'Bạc Gravity','#a7abaf',1300000000,NULL,2,'published',100,0,NULL,'Hyundai Ioniq 5 Prestige – xe điện thông minh, không phát thải. Xe mới 100%, bảo hành chính hãng.','Hyundai Ioniq 5 Prestige phiên bản 2024 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',243,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(28,2,6,'XE1028','Kia Sorento 2.2D Signature','kia-sorento-22d-signature-2025',2025,'new',0,NULL,'#2e3a46',1219000000,NULL,2,'review',83,0,NULL,'Kia Sorento 2.2D Signature – gầm cao rộng rãi cho cả gia đình. Xe mới 100%, bảo hành chính hãng.','Kia Sorento 2.2D Signature phiên bản 2025 được phân phối chính hãng, bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước). Giá đã bao gồm VAT.

Trang bị nổi bật: màn hình giải trí cảm ứng kết nối Apple CarPlay/Android Auto, camera lùi, cảm biến hỗ trợ đỗ xe, cân bằng điện tử, hỗ trợ khởi hành ngang dốc và hệ thống túi khí an toàn.

Khi đặt cọc tại AutoShop, quý khách được hỗ trợ trọn gói thủ tục đăng ký – đăng kiểm, tư vấn trả góp đến 80% giá trị xe và giao xe tận nhà trong nội thành.',80,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL),
(29,5,8,'XE1029','VinFast VF 9 Plus','vinfast-vf-9-plus-2025',2025,'new',0,'Trắng Brahminy','#f2f2f2',1699000000,NULL,0,'draft',60,0,NULL,NULL,NULL,104,'2026-10-18 16:15:09','2026-10-18 16:15:09',NULL);

-- Dữ liệu bảng car_attribute_values
INSERT INTO `car_attribute_values` (`id`,`car_id`,`attribute_id`,`value`,`created_at`,`updated_at`) VALUES
(1,1,1,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(2,1,2,'Số tự động vô cấp CVT','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(3,1,3,'1.5L 4 xi-lanh','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(4,1,4,'106','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(5,1,5,'5.7','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(6,2,1,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(7,2,2,'Số tự động vô cấp CVT','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(8,2,3,'1.5L i-VTEC','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(9,2,4,'119','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(10,2,5,'5.6','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(11,3,1,'Hybrid (Xăng – Điện)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(12,3,2,'Số tự động vô cấp CVT','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(13,3,3,'2.5L Hybrid','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(14,3,4,'227','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(15,3,5,'4.4','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(16,4,1,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(17,4,2,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(18,4,3,'1.5L SkyActiv-G','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(19,4,4,'110','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(20,4,5,'6.2','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(21,5,1,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(22,5,2,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(23,5,3,'2.0L MPI','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(24,5,4,'150','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(25,5,5,'6.8','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(26,6,1,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(27,6,2,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(28,6,3,'2.0L Turbo + EQ Boost','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(29,6,4,'258','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(30,6,5,'7.1','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(31,7,1,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(32,7,2,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(33,7,3,'1.5L Smartstream','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(34,7,4,'113','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(35,7,5,'5.9','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(36,8,6,'5 chỗ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(37,8,7,'Hybrid (Xăng – Điện)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(38,8,8,'Số tự động vô cấp CVT','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(39,8,9,'Cầu trước (FWD)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(40,8,10,'1.8L Hybrid','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(41,8,11,'120','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(42,9,6,'7 chỗ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(43,9,7,'Hybrid (Xăng – Điện)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(44,9,8,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(45,9,9,'Cầu trước (FWD)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(46,9,10,'2.0L e:HEV','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(47,9,11,'204','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(48,10,6,'5 chỗ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(49,10,7,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(50,10,8,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08');
INSERT INTO `car_attribute_values` (`id`,`car_id`,`attribute_id`,`value`,`created_at`,`updated_at`) VALUES
(51,10,9,'Cầu trước (FWD)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(52,10,10,'2.0L SkyActiv-G','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(53,10,11,'154','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(54,11,6,'7 chỗ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(55,11,7,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(56,11,8,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(57,11,9,'Hai cầu toàn thời gian (AWD)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(58,11,10,'2.5L Turbo','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(59,11,11,'281','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(60,12,6,'5 chỗ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(61,12,7,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(62,12,8,'Số tự động vô cấp CVT','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(63,12,9,'Cầu trước (FWD)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(64,12,10,'1.5L Smartstream','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(65,12,11,'115','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(66,13,6,'7 chỗ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(67,13,7,'Dầu (Diesel)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(68,13,8,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(69,13,9,'Hai cầu 4x4','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(70,13,10,'2.0L Bi-Turbo','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(71,13,11,'210','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(72,14,6,'7 chỗ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(73,14,7,'Xăng','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(74,14,8,'Số tự động vô cấp CVT','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(75,14,9,'Cầu trước (FWD)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(76,14,10,'1.5L MIVEC','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(77,14,11,'105','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(78,15,6,'7 chỗ','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(79,15,7,'Dầu (Diesel)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(80,15,8,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(81,15,9,'Cầu trước (FWD)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(82,15,10,'2.4L Diesel','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(83,15,11,'148','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(84,16,12,'Dầu (Diesel)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(85,16,13,'Số tự động','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(86,16,14,'Hai cầu 4x4','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(87,16,15,'2.0L Bi-Turbo','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(88,16,16,'650','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(89,17,12,'Dầu (Diesel)','2026-10-18 16:15:08','2026-10-18 16:15:08'),
(90,17,13,'Số tự động','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(91,17,14,'Hai cầu 4x4','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(92,17,15,'2.8L Diesel','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(93,17,16,'600','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(94,18,12,'Dầu (Diesel)','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(95,18,13,'Số tự động','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(96,18,14,'Hai cầu 4x4','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(97,18,15,'2.4L Bi-Turbo','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(98,18,16,'630','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(99,19,17,'Xăng','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(100,19,18,'Số tự động vô cấp CVT','2026-10-18 16:15:09','2026-10-18 16:15:09');
INSERT INTO `car_attribute_values` (`id`,`car_id`,`attribute_id`,`value`,`created_at`,`updated_at`) VALUES
(101,19,19,'1.2L','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(102,19,20,'2','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(103,20,17,'Xăng','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(104,20,18,'Số tự động','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(105,20,19,'1.2L Kappa','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(106,20,20,'2','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(107,21,17,'Xăng','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(108,21,18,'Số tự động','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(109,21,19,'1.25L Kappa','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(110,21,20,'6','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(111,22,17,'Xăng','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(112,22,18,'Số tự động','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(113,22,19,'1.5L SkyActiv-G','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(114,22,20,'6','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(115,23,21,'4 chỗ','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(116,23,22,'18.64','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(117,23,23,'215','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(118,23,24,'DC 30 phút (10–70%)','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(119,23,25,'43','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(120,24,21,'5 chỗ','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(121,24,22,'37.23','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(122,24,23,'326','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(123,24,24,'DC 30 phút (10–70%)','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(124,24,25,'134','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(125,25,21,'5 chỗ','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(126,25,22,'59.6','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(127,25,23,'460','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(128,25,24,'DC 25 phút (10–70%)','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(129,25,25,'201','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(130,26,21,'5 chỗ','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(131,26,22,'87.7','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(132,26,23,'471','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(133,26,24,'DC 31 phút (10–70%)','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(134,26,25,'402','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(135,27,21,'5 chỗ','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(136,27,22,'72.6','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(137,27,23,'451','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(138,27,24,'800V – 18 phút (10–80%)','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(139,27,25,'214','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(140,28,6,'7 chỗ','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(141,28,7,'Dầu (Diesel)','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(142,28,8,'Số tự động','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(143,28,10,'2.2L Smartstream D','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(144,28,11,'198','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(145,29,21,'7 chỗ','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(146,29,25,'402','2026-10-18 16:15:09','2026-10-18 16:15:09');

-- Dữ liệu bảng orders
INSERT INTO `orders` (`id`,`code`,`user_id`,`customer_name`,`customer_phone`,`customer_email`,`customer_id_number`,`address`,`showroom`,`note`,`subtotal`,`registration_fee`,`total`,`deposit_amount`,`payment_method`,`payment_status`,`status`,`cancelled_at`,`created_at`,`updated_at`) VALUES
(1,'AS260521YIDOG',2,'Nguyễn Văn Khách','0909000002','khach@autoshop.test','079200001234','25 Nguyễn Huệ, Q.1, TP.HCM','AutoShop Quận 1 – 12 Lê Duẩn, Q.1, TP.HCM',NULL,499000000,69900000,568900000,56890000,'vnpay','paid','completed',NULL,'2026-05-21 16:15:09','2026-05-21 16:15:09'),
(2,'AS260620QHGUG',2,'Nguyễn Văn Khách','0909000002','khach@autoshop.test','079200001234','25 Nguyễn Huệ, Q.1, TP.HCM','AutoShop Thủ Đức – 220 Võ Văn Ngân, TP. Thủ Đức',NULL,405000000,60500000,465500000,46550000,'showroom','paid','completed',NULL,'2026-06-20 16:15:09','2026-06-20 16:15:09'),
(3,'AS26071544EIK',2,'Nguyễn Văn Khách','0909000002','khach@autoshop.test','079200001234','25 Nguyễn Huệ, Q.1, TP.HCM','AutoShop Tân Phú – 140 Lê Trọng Tấn, Q. Tân Phú',NULL,1545000000,174500000,1719500000,171950000,'vnpay','paid','completed',NULL,'2026-07-15 16:15:09','2026-07-15 16:15:09'),
(4,'AS260817O91N2',2,'Nguyễn Văn Khách','0909000002','khach@autoshop.test','079200001234','25 Nguyễn Huệ, Q.1, TP.HCM','AutoShop Hà Nội – 88 Phạm Văn Đồng, Q. Bắc Từ Liêm',NULL,1530000000,173000000,1703000000,170300000,'showroom','paid','completed',NULL,'2026-08-17 16:15:09','2026-08-17 16:15:09'),
(5,'AS260918BBORU',2,'Nguyễn Văn Khách','0909000002','khach@autoshop.test','079200001234','25 Nguyễn Huệ, Q.1, TP.HCM','AutoShop Quận 1 – 12 Lê Duẩn, Q.1, TP.HCM',NULL,799000000,99900000,898900000,89890000,'vnpay','deposited','delivering',NULL,'2026-09-18 16:15:09','2026-09-18 16:15:09'),
(6,'AS261013BG03Y',2,'Nguyễn Văn Khách','0909000002','khach@autoshop.test','079200001234','25 Nguyễn Huệ, Q.1, TP.HCM','AutoShop Thủ Đức – 220 Võ Văn Ngân, TP. Thủ Đức',NULL,520000000,72000000,592000000,59200000,'vnpay','deposited','confirmed',NULL,'2026-10-13 16:15:09','2026-10-13 16:15:09'),
(7,'AS261017TUZ7A',2,'Nguyễn Văn Khách','0909000002','khach@autoshop.test','079200001234','25 Nguyễn Huệ, Q.1, TP.HCM','AutoShop Tân Phú – 140 Lê Trọng Tấn, Q. Tân Phú',NULL,979000000,117900000,1096900000,109690000,'showroom','unpaid','pending',NULL,'2026-10-17 16:15:09','2026-10-17 16:15:09');

-- Dữ liệu bảng order_items
INSERT INTO `order_items` (`id`,`order_id`,`car_id`,`name`,`sku`,`color`,`year`,`price`,`quantity`,`created_at`,`updated_at`) VALUES
(1,1,24,'VinFast VF 5 Plus','XE1024','Vàng Cam',2025,499000000,1,'2026-10-18 16:15:09','2026-10-18 16:15:09'),
(2,2,19,'Toyota Wigo 1.2 AT','XE1019','Vàng chanh',2025,405000000,1,'2026-10-18 16:15:09','2026-10-18 16:15:09'),
(3,3,13,'Ford Everest Titanium+ 4x4','XE1013','Xám Meteor',2025,1545000000,1,'2026-10-18 16:15:09','2026-10-18 16:15:09'),
(4,4,3,'Toyota Camry 2.5HEV Top','XE1003','Đen ánh kim',2025,1530000000,1,'2026-10-18 16:15:09','2026-10-18 16:15:09'),
(5,5,10,'Mazda CX-5 2.0 Premium','XE1010','Đỏ pha lê',2025,799000000,1,'2026-10-18 16:15:09','2026-10-18 16:15:09'),
(6,6,1,'Toyota Vios 1.5G CVT','XE1001','Trắng ngọc trai',2025,520000000,1,'2026-10-18 16:15:09','2026-10-18 16:15:09'),
(7,7,16,'Ford Ranger Wildtrak 2.0 4x4','XE1016','Cam Luxe',2025,979000000,1,'2026-10-18 16:15:09','2026-10-18 16:15:09');

-- Dữ liệu bảng payment_transactions
INSERT INTO `payment_transactions` (`id`,`order_id`,`gateway`,`transaction_code`,`amount`,`status`,`gateway_transaction_no`,`raw_response`,`paid_at`,`created_at`,`updated_at`) VALUES
(1,1,'vnpay','AS260521YIDOG01',568900000,'success','14608114',NULL,'2026-05-21 16:15:09','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(2,2,'showroom','AS260620QHGUG01',465500000,'success',NULL,NULL,'2026-06-20 16:15:09','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(3,3,'vnpay','AS26071544EIK01',1719500000,'success','14809448',NULL,'2026-07-15 16:15:09','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(4,4,'showroom','AS260817O91N201',1703000000,'success',NULL,NULL,'2026-08-17 16:15:09','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(5,5,'vnpay','AS260918BBORU01',89890000,'success','14916109',NULL,'2026-09-18 16:15:09','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(6,6,'vnpay','AS261013BG03Y01',59200000,'success','14739235',NULL,'2026-10-13 16:15:09','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(7,7,'showroom','AS261017TUZ7A01',109690000,'pending',NULL,NULL,NULL,'2026-10-18 16:15:09','2026-10-18 16:15:09');

-- Dữ liệu bảng test_drives
INSERT INTO `test_drives` (`id`,`car_id`,`user_id`,`name`,`phone`,`email`,`showroom`,`preferred_date`,`preferred_time`,`note`,`status`,`created_at`,`updated_at`) VALUES
(1,9,2,'Nguyễn Văn Khách','0909000002','khach@autoshop.test','AutoShop Quận 1 – 12 Lê Duẩn, Q.1, TP.HCM','2026-10-20 00:00:00','09:30','Muốn chạy thử cung đường cao tốc.','new','2026-10-18 16:15:09','2026-10-18 16:15:09'),
(2,23,NULL,'Trần Thị Bình','0912345678',NULL,'AutoShop Thủ Đức – 220 Võ Văn Ngân, TP. Thủ Đức','2026-10-22 00:00:00','14:00',NULL,'confirmed','2026-10-18 16:15:09','2026-10-18 16:15:09');

-- Dữ liệu bảng migrations
INSERT INTO `migrations` (`id`,`migration`,`batch`) VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_10_18_000001_create_catalog_tables',1),
(5,'2026_10_18_000002_create_order_tables',1);

SET FOREIGN_KEY_CHECKS = 1;
