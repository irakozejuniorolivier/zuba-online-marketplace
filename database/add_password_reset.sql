-- Run this in phpMyAdmin or MySQL to add password reset columns to users table
ALTER TABLE `users`
  ADD COLUMN `reset_token` varchar(64) DEFAULT NULL,
  ADD COLUMN `reset_expires` datetime DEFAULT NULL,
  ADD KEY `reset_token` (`reset_token`);
