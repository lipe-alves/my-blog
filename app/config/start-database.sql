CREATE DATABASE IF NOT EXISTS my_blog;

USE my_blog;

CREATE TABLE
    IF NOT EXISTS Post (
        `slug` VARCHAR(60) NOT NULL,
        `title` VARCHAR(60) NOT NULL,
        `text` TEXT NOT NULL,
        `created_at` DATE NOT NULL DEFAULT (CURRENT_DATE),
        `deleted` TINYINT (1) NOT NULL DEFAULT 0,
        `deleted_at` DATE NULL,
        PRIMARY KEY (`slug`)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS File (
        `path` VARCHAR(320) NOT NULL,
        `filename` VARCHAR(50) NOT NULL,
        `type` VARCHAR(50) NOT NULL DEFAULT 'text/plain',
        `size` INT NOT NULL DEFAULT 0,
        `created_at` DATE NOT NULL DEFAULT (CURRENT_DATE),
        `deleted` TINYINT (1) NOT NULL DEFAULT 0,
        `deleted_at` DATE NULL,
        PRIMARY KEY (`path`)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Reader (
        `email` VARCHAR(320) NOT NULL,
        `first_name` VARCHAR(20) NOT NULL,
        `last_name` VARCHAR(20) NOT NULL,
        `photo` VARCHAR(500) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `registered_at` DATE NOT NULL DEFAULT (CURRENT_DATE),
        PRIMARY KEY (`email`)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Comment (
        `id` INT NOT NULL AUTO_INCREMENT,
        `post_slug` INT NOT NULL,
        `reader_email` INT NOT NULL,
        `comment_id` INT NULL,
        `text` TEXT NOT NULL,
        `created_at` DATE NOT NULL DEFAULT (CURRENT_DATE),
        `deleted` TINYINT (1) NOT NULL DEFAULT 0,
        `deleted_at` DATE NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`post_slug`) REFERENCES Post (`slug`),
        FOREIGN KEY (`reader_email`) REFERENCES Reader (`email`),
        FOREIGN KEY (`comment_id`) REFERENCES Comment (`id`)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Category (
        `name` VARCHAR(320) NOT NULL,
        `category_name` INT NOT NULL,
        `created_at` DATE NOT NULL DEFAULT (CURRENT_DATE),
        `deleted` TINYINT (1) NOT NULL DEFAULT 0,
        `deleted_at` DATE NULL,
        PRIMARY KEY (`name`),
        FOREIGN KEY (`category_name`) REFERENCES Category (`name`)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Post_x_Category (
        `post_slug` INT NOT NULL,
        `category_name` INT NOT NULL,
        PRIMARY KEY (`post_slug`, `category_name`),
        FOREIGN KEY (`post_slug`) REFERENCES Post (`slug`),
        FOREIGN KEY (`category_name`) REFERENCES Category (`name`)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;
