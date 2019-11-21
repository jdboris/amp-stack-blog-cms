SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `template_blog_with_users` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `template_blog_with_users`;

DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `blog_post_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_alias` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preview` varchar(210) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_html` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_posted` datetime NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `blog_post_comments`;
CREATE TABLE `blog_post_comments` (
  `blog_post_comment_id` int(11) NOT NULL,
  `blog_post_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `comment_date` datetime NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `email_verification_tokens`;
CREATE TABLE `email_verification_tokens` (
  `email_verification_token_id` int(11) NOT NULL,
  `user_account_id` int(11) NOT NULL,
  `email_verification_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tokens used to allow email verification';

DROP TABLE IF EXISTS `html_content`;
CREATE TABLE `html_content` (
  `html_content_id` int(11) NOT NULL,
  `content_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_change_tokens`;
CREATE TABLE `password_change_tokens` (
  `password_change_token_id` int(11) NOT NULL,
  `user_account_id` int(11) NOT NULL,
  `password_change_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tokens used to allow password changes';

DROP TABLE IF EXISTS `user_accounts`;
CREATE TABLE `user_accounts` (
  `user_account_id` int(11) NOT NULL,
  `email_address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `power_level` tinyint(4) NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `join_date` datetime NOT NULL,
  `status_id` tinyint(4) NOT NULL,
  `email_verified` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts';

DROP TABLE IF EXISTS `user_account_statuses`;
CREATE TABLE `user_account_statuses` (
  `user_account_status_id` tinyint(4) NOT NULL,
  `user_account_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='The status of a user account (active, inactive, deactivated, etc.)';

INSERT INTO `user_account_statuses` (`user_account_status_id`, `user_account_status`) VALUES
(1, 'active'),
(3, 'deactivated'),
(2, 'inactive');

DROP TABLE IF EXISTS `user_account_status_history`;
CREATE TABLE `user_account_status_history` (
  `user_account_status_history_id` int(11) NOT NULL,
  `user_account_id` int(11) NOT NULL,
  `user_account_status_id` int(11) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='The history of user account status changes';

DROP TABLE IF EXISTS `user_login_history`;
CREATE TABLE `user_login_history` (
  `login_id` int(11) NOT NULL,
  `user_account_id` int(11) NOT NULL,
  `login_date` datetime NOT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='The history of user logins';


ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`blog_post_id`),
  ADD KEY `author_id` (`author_id`);
ALTER TABLE `blog_posts` ADD FULLTEXT KEY `body-html` (`body_html`);
ALTER TABLE `blog_posts` ADD FULLTEXT KEY `body-text` (`body_text`);

ALTER TABLE `blog_post_comments`
  ADD PRIMARY KEY (`blog_post_comment_id`),
  ADD KEY `FK_blog_post_comments_user_accounts` (`author_id`),
  ADD KEY `FK_blog_post_comments_blog_posts` (`blog_post_id`);

ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`email_verification_token_id`),
  ADD KEY `user_account_id` (`user_account_id`);

ALTER TABLE `html_content`
  ADD PRIMARY KEY (`html_content_id`),
  ADD UNIQUE KEY `content_key` (`content_key`);

ALTER TABLE `password_change_tokens`
  ADD PRIMARY KEY (`password_change_token_id`),
  ADD KEY `user_account_id` (`user_account_id`);

ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`user_account_id`),
  ADD UNIQUE KEY `email_address` (`email_address`),
  ADD KEY `status_id` (`status_id`);

ALTER TABLE `user_account_statuses`
  ADD PRIMARY KEY (`user_account_status_id`),
  ADD UNIQUE KEY `user_account_status` (`user_account_status`);

ALTER TABLE `user_account_status_history`
  ADD PRIMARY KEY (`user_account_status_history_id`),
  ADD KEY `user_account_status_id` (`user_account_status_id`),
  ADD KEY `user_account_id` (`user_account_id`);

ALTER TABLE `user_login_history`
  ADD PRIMARY KEY (`login_id`),
  ADD KEY `user_account_id` (`user_account_id`);


ALTER TABLE `blog_posts`
  MODIFY `blog_post_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `blog_post_comments`
  MODIFY `blog_post_comment_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `email_verification_tokens`
  MODIFY `email_verification_token_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `html_content`
  MODIFY `html_content_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `password_change_tokens`
  MODIFY `password_change_token_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_accounts`
  MODIFY `user_account_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_account_statuses`
  MODIFY `user_account_status_id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `user_account_status_history`
  MODIFY `user_account_status_history_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `user_accounts` (`user_account_id`);

ALTER TABLE `blog_post_comments`
  ADD CONSTRAINT `FK_blog_post_comments_blog_posts` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`blog_post_id`),
  ADD CONSTRAINT `FK_blog_post_comments_user_accounts` FOREIGN KEY (`author_id`) REFERENCES `user_accounts` (`user_account_id`);

ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `email_verification_tokens_ibfk_1` FOREIGN KEY (`user_account_id`) REFERENCES `user_accounts` (`user_account_id`);

ALTER TABLE `password_change_tokens`
  ADD CONSTRAINT `password_change_tokens_ibfk_1` FOREIGN KEY (`user_account_id`) REFERENCES `user_accounts` (`user_account_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
