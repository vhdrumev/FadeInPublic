-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2025 at 11:43 AM
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
-- Database: `fadein`
--

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

CREATE TABLE `comment_likes` (
                                 `id` int(11) NOT NULL,
                                 `comment_id` int(11) NOT NULL,
                                 `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
                           `id` int(11) NOT NULL,
                           `sender_id` int(11) NOT NULL,
                           `receiver_id` int(11) NOT NULL,
                           `type` char(1) NOT NULL DEFAULT 'f'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hashtags`
--

CREATE TABLE `hashtags` (
                            `id` int(11) NOT NULL,
                            `post_id` int(11) NOT NULL,
                            `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hashtags`
--

INSERT INTO `hashtags` (`id`, `post_id`, `name`) VALUES
                                                     (9, 34, '#life'),
                                                     (10, 34, '#motivation'),
                                                     (11, 34, '#inspiration'),
                                                     (12, 34, '#success'),
                                                     (13, 34, '#dreambig'),
                                                     (14, 34, '#growth'),
                                                     (15, 34, '#hustle'),
                                                     (16, 34, '#mindset'),
                                                     (17, 34, '#positivity'),
                                                     (18, 34, '#selfgrowth'),
                                                     (19, 35, '#success'),
                                                     (20, 35, '#hardwork'),
                                                     (21, 35, '#motivation'),
                                                     (22, 35, '#focus'),
                                                     (23, 35, '#determination'),
                                                     (24, 35, '#achievement'),
                                                     (25, 35, '#grind'),
                                                     (26, 35, '#neverstop'),
                                                     (27, 35, '#goals'),
                                                     (28, 35, '#mindset'),
                                                     (29, 36, '#future'),
                                                     (30, 36, '#dreams'),
                                                     (31, 36, '#create'),
                                                     (32, 36, '#innovation'),
                                                     (33, 36, '#vision'),
                                                     (34, 36, '#opportunity'),
                                                     (35, 36, '#success'),
                                                     (36, 36, '#life'),
                                                     (37, 36, '#entrepreneur'),
                                                     (38, 36, '#growth'),
                                                     (39, 37, '#selfbelief'),
                                                     (40, 37, '#confidence'),
                                                     (41, 37, '#motivation'),
                                                     (42, 37, '#positivity'),
                                                     (43, 37, '#nevergiveup'),
                                                     (44, 37, '#growth'),
                                                     (45, 37, '#mindset'),
                                                     (46, 37, '#success'),
                                                     (47, 37, '#mentalstrength'),
                                                     (48, 37, '#strength'),
                                                     (49, 38, '#opportunity'),
                                                     (50, 38, '#growth'),
                                                     (51, 38, '#focus'),
                                                     (52, 38, '#success'),
                                                     (53, 38, '#hustle'),
                                                     (54, 38, '#keepgoing'),
                                                     (55, 38, '#positivity'),
                                                     (56, 38, '#dreams'),
                                                     (57, 38, '#nevergiveup'),
                                                     (58, 38, '#determination'),
                                                     (59, 39, '#growth'),
                                                     (60, 39, '#smallsteps'),
                                                     (61, 39, '#success'),
                                                     (62, 39, '#change'),
                                                     (63, 39, '#mindset'),
                                                     (64, 39, '#motivation'),
                                                     (65, 39, '#journey'),
                                                     (66, 39, '#hustle'),
                                                     (67, 39, '#focus'),
                                                     (68, 39, '#achieve'),
                                                     (69, 40, '#action'),
                                                     (70, 40, '#success'),
                                                     (71, 40, '#hustle'),
                                                     (72, 40, '#growth'),
                                                     (73, 40, '#motivation'),
                                                     (74, 40, '#determination'),
                                                     (75, 40, '#nevergiveup'),
                                                     (76, 40, '#takeaction'),
                                                     (77, 40, '#focus'),
                                                     (78, 40, '#mindset'),
                                                     (79, 41, '#positivevibes'),
                                                     (80, 41, '#growth'),
                                                     (81, 41, '#surroundyourself'),
                                                     (82, 41, '#positivity'),
                                                     (83, 41, '#friendship'),
                                                     (84, 41, '#support'),
                                                     (85, 41, '#success'),
                                                     (86, 41, '#motivation'),
                                                     (87, 41, '#love'),
                                                     (88, 41, '#relationships'),
                                                     (89, 42, '#consistency'),
                                                     (90, 42, '#success'),
                                                     (91, 42, '#goals'),
                                                     (92, 42, '#motivation'),
                                                     (93, 42, '#hustle'),
                                                     (94, 42, '#growth'),
                                                     (95, 42, '#focus'),
                                                     (96, 42, '#achievement'),
                                                     (97, 42, '#determination'),
                                                     (98, 42, '#neverquit'),
                                                     (99, 43, '#success'),
                                                     (100, 43, '#persistence'),
                                                     (101, 43, '#motivation'),
                                                     (102, 43, '#hustle'),
                                                     (103, 43, '#focus'),
                                                     (104, 43, '#hardwork'),
                                                     (105, 43, '#goals'),
                                                     (106, 43, '#achievement'),
                                                     (107, 43, '#growth'),
                                                     (108, 43, '#nevergiveup'),
                                                     (109, 44, '#dreams'),
                                                     (110, 44, '#success'),
                                                     (111, 44, '#independence'),
                                                     (112, 44, '#focus'),
                                                     (113, 44, '#motivation'),
                                                     (114, 44, '#life'),
                                                     (115, 44, '#growth'),
                                                     (116, 44, '#hustle'),
                                                     (117, 44, '#mindset'),
                                                     (118, 44, '#staytrue'),
                                                     (119, 45, '#dreams'),
                                                     (120, 45, '#success'),
                                                     (121, 45, '#independence'),
                                                     (122, 45, '#focus'),
                                                     (123, 45, '#motivation'),
                                                     (124, 45, '#life'),
                                                     (125, 45, '#growth'),
                                                     (126, 45, '#hustle'),
                                                     (127, 45, '#mindset'),
                                                     (128, 45, '#staytrue');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
                            `id` int(11) NOT NULL,
                            `sender_id` int(11) NOT NULL,
                            `receiver_id` int(11) NOT NULL,
                            `message` text NOT NULL,
                            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                            `deleted` tinyint(1) DEFAULT 0,
                            `is_seen` tinyint(1) NOT NULL DEFAULT 0,
                            `likes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `deleted`, `is_seen`, `likes`) VALUES
    (72, 22, 23, 'hello', '2025-02-26 17:59:28', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `message_likes`
--

CREATE TABLE `message_likes` (
                                 `message_id` int(11) NOT NULL,
                                 `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
                         `id` int(11) NOT NULL,
                         `user_id` int(11) NOT NULL,
                         `text` text NOT NULL,
                         `created_at` datetime DEFAULT current_timestamp(),
                         `files` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `text`, `created_at`, `files`) VALUES
                                                                         (34, 22, 'Life is full of opportunities, don\'t waste them.', '2025-02-26 09:43:06', NULL),
                                                                         (35, 22, 'Your hard work is the key to your success. Keep pushing!', '2025-02-26 09:43:18', NULL),
                                                                         (36, 22, 'The future is yours to create, don\'t wait for it!', '2025-02-26 09:43:35', NULL),
                                                                         (37, 22, 'Believe in yourself, even when it’s hard.', '2025-02-26 09:43:42', NULL),
                                                                         (38, 22, 'Your next opportunity might just be around the corner. Stay ready.', '2025-02-26 09:44:08', NULL),
                                                                         (39, 22, 'Small steps lead to big changes.', '2025-02-26 09:44:17', NULL),
                                                                         (40, 22, 'Don’t wait for the perfect moment, take action now!', '2025-02-26 09:44:27', NULL),
                                                                         (41, 22, 'Surround yourself with people who uplift you.', '2025-02-26 09:44:38', NULL),
                                                                         (42, 22, 'Consistency is key to achieving your goals.', '2025-02-26 09:44:48', NULL),
                                                                         (43, 22, 'Success doesn\'t happen overnight, but with persistence, it will come.', '2025-02-26 09:44:56', NULL),
                                                                         (44, 23, 'Chase your dreams, not the crowd.', '2025-02-26 09:55:13', NULL),
                                                                         (45, 23, 'Chase your dreams, not the crowd.', '2025-02-27 12:17:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
                                 `id` int(11) NOT NULL,
                                 `post_id` int(11) NOT NULL,
                                 `user_id` int(11) NOT NULL,
                                 `comment` text NOT NULL,
                                 `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                 `parent_comment_id` int(11) DEFAULT NULL,
                                 `like_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_comments`
--

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `comment`, `created_at`, `parent_comment_id`, `like_count`) VALUES
                                                                                                                         (62, 34, 22, 'hi', '2025-02-26 17:59:41', NULL, 0),
                                                                                                                         (63, 34, 22, 'hi', '2025-02-26 17:59:43', 62, 0);

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
                              `id` int(11) NOT NULL,
                              `post_id` int(11) NOT NULL,
                              `user_id` int(11) NOT NULL,
                              `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
                        `id` int(11) NOT NULL,
                        `username` varchar(50) NOT NULL,
                        `email` varchar(100) NOT NULL,
                        `password` varchar(255) NOT NULL,
                        `profile_picture` varchar(255) DEFAULT NULL,
                        `bio` text DEFAULT NULL,
                        `pronouns` varchar(15) DEFAULT NULL,
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                        `first_name` varchar(255) DEFAULT NULL,
                        `middle_name` varchar(255) DEFAULT NULL,
                        `last_name` varchar(255) DEFAULT NULL,
                        `age` int(11) DEFAULT NULL,
                        `phone` varchar(15) DEFAULT NULL,
                        `address` text DEFAULT NULL,
                        `role` varchar(15) DEFAULT NULL,
                        `profile_picture_path` varchar(255) DEFAULT NULL,
                        `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `profile_picture`, `bio`, `pronouns`, `created_at`, `updated_at`, `first_name`, `middle_name`, `last_name`, `age`, `phone`, `address`, `role`, `profile_picture_path`, `verified`) VALUES
                                                                                                                                                                                                                                                  (22, 'dummy1', 'dummy1@gmail.com', '$2y$10$iusFs/pG3BRTPjsWSJ92GeZz.PZlFg2InDH8qjI.ZJ1DbsdY3aEpW', NULL, NULL, NULL, '2025-02-26 07:40:08', '2025-02-26 07:47:00', '', '', '', NULL, NULL, NULL, 'administrator', NULL, 0),
                                                                                                                                                                                                                                                  (23, 'dummy2', 'dummy2@gmail.com', '$2y$10$ItDcSVG7a6vO8MZctqm.OeCS30Ip0hJA2m6V4cuoj7yIBEAvayKfm', NULL, NULL, NULL, '2025-02-26 07:41:32', '2025-02-26 07:47:09', '', '', '', NULL, NULL, NULL, 'administrator', NULL, 0),
                                                                                                                                                                                                                                                  (24, 'dummy3', 'dummy3@gmail.com', '$2y$10$W6HFTXCPDOCGm5PjWbOMjuC2U33.5FxnDnGageBmtLzJ1kAZqsH6a', NULL, NULL, NULL, '2025-02-26 07:41:45', '2025-02-26 07:41:45', '', '', '', NULL, NULL, NULL, 'user', NULL, 0),
                                                                                                                                                                                                                                                  (25, 'dummy4', 'dummy4@gmail.com', '$2y$10$kr00e6dCblZo2.Fis4eUHebDxWX2xYz9JHJIzruRMY2L.CXGlzhT2', NULL, NULL, NULL, '2025-02-26 07:42:01', '2025-02-26 07:42:01', '', '', '', NULL, NULL, NULL, 'user', NULL, 0),
                                                                                                                                                                                                                                                  (26, 'dummy5', 'dummy5@gmail.com', '$2y$10$sW4KPrDhB3fsJXIzL.pWF.GNqVXF7RrhWRldOdsIvtE.x1FKbmaEm', NULL, NULL, NULL, '2025-02-26 07:42:13', '2025-02-26 07:42:13', '', '', '', NULL, NULL, NULL, 'user', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
    ADD PRIMARY KEY (`id`),
    ADD KEY `comment_id` (`comment_id`),
    ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `unique_follow` (`sender_id`,`receiver_id`),
    ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `hashtags`
--
ALTER TABLE `hashtags`
    ADD PRIMARY KEY (`id`),
    ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
    ADD PRIMARY KEY (`id`),
    ADD KEY `fk_sender_id` (`sender_id`),
    ADD KEY `fk_receiver_id` (`receiver_id`);

--
-- Indexes for table `message_likes`
--
ALTER TABLE `message_likes`
    ADD PRIMARY KEY (`message_id`,`user_id`),
    ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
    ADD PRIMARY KEY (`id`),
    ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
    ADD PRIMARY KEY (`id`),
    ADD KEY `post_id` (`post_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `fk_parent_comment` (`parent_comment_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `post_id` (`post_id`,`user_id`),
    ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `username` (`username`),
    ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comment_likes`
--
ALTER TABLE `comment_likes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hashtags`
--
ALTER TABLE `hashtags`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
    ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `post_comments` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
    ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hashtags`
--
ALTER TABLE `hashtags`
    ADD CONSTRAINT `hashtags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
    ADD CONSTRAINT `fk_receiver_id` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `fk_sender_id` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_likes`
--
ALTER TABLE `message_likes`
    ADD CONSTRAINT `message_likes_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `message_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
    ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
    ADD CONSTRAINT `fk_parent_comment` FOREIGN KEY (`parent_comment_id`) REFERENCES `post_comments` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `post_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `post_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
    ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
