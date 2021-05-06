DROP DATABASE IF EXISTS LSteam;
CREATE DATABASE LSteam;

DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL DEFAULT '',
    `email` VARCHAR(255) NOT NULL DEFAULT '',
    `password` VARCHAR(255) NOT NULL DEFAULT '',
    `birthday` DATETIME NOT NULL,
    `phone` VARCHAR(255) NOT NULL DEFAULT '',
    `activated` BOOLEAN,
    `token` VARCHAR(255) NOT NULL DEFAULT '',
    `wallet` INTEGER NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `uuid` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Game`;
CREATE TABLE `Game` (
    `id` INT(11) unsigned NOT NULL,
    `storeID` INT(11) unsigned NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `thumb` VARCHAR(255) NOT NULL DEFAULT '',
    `dealRating` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `User-Game-Bought`;
CREATE TABLE `User-Game-Bought` (
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `gameID` INT(11) unsigned NOT NULL,
    `userID` INT(11) unsigned NOT NULL,
    `sellPrice` FLOAT DEFAULT 0,
    `dateBought` DATETIME NOT NULL,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`gameID`) REFERENCES Game(`id`),
    FOREIGN KEY(`userID`) REFERENCES User(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `User-Game-Wishlist`;
CREATE TABLE `User-Game-Wishlist` (
    `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `gameID` INT(11) unsigned NOT NULL,
    `userID` INT(11) unsigned NOT NULL,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`gameID`) REFERENCES Game(`id`),
    FOREIGN KEY(`userID`) REFERENCES User(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Friend-User`;
CREATE TABLE `Friend-User` (
    `id` INT(11) unsigned NOT NULL AUTO INCREMENT,
    `user1_id` INT(11) unsigned NOT NULL,
    `user2_id` INT(11) unsigned NOT NULL,
    `date_accepted` DATETIME NOT NULL,
    PRIMARY KEY(`id`),
    FOREIGN KEY(`user1_id`) REFERENCES User(`id`),
    FOREIGN KEY(`user2_id`) REFERENCES User(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `Request`;
CREATE TABLE `Request` (
    `request_id` INT(11) unsigned NOT NULL AUTO INCREMENT,
    `user1_id` INT(11) unsigned NOT NULL,
    `user2_id` INT(11) unsigned NOT NULL, 
    PRIMARY KEY(`request_id`),
    FOREIGN KEY(`user1_id`) REFERENCES User(`id`),
    FOREIGN KEY(`user2_id`) REFERENCES User(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;





