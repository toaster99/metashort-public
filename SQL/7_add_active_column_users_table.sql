ALTER TABLE `users` ADD `active` ENUM('y','n')  DEFAULT 'y'  AFTER `lastLogin`;
