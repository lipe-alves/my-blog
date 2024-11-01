CREATE DATABASE IF NOT EXISTS my_blog;

USE my_blog;

CREATE TABLE
    IF NOT EXISTS Post (
        id INT NOT NULL AUTO_INCREMENT,
        title VARCHAR(60) NOT NULL,
        `text` TEXT NOT NULL,
        created_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATE NULL,
        PRIMARY KEY (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Slug (
        slug VARCHAR(60) NOT NULL,
        post_id INT,
        PRIMARY KEY (slug),
        FOREIGN KEY (post_id) REFERENCES Post (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS File (
        id INT NOT NULL AUTO_INCREMENT,
        `filename` VARCHAR(50) NOT NULL,
        `type` VARCHAR(50) NOT NULL DEFAULT 'text/plain',
        `path` VARCHAR(500) NOT NULL,
        `size` INT NOT NULL DEFAULT 0,
        created_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATE NULL,
        PRIMARY KEY (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Reader (
        id INT NOT NULL AUTO_INCREMENT,
        email VARCHAR(320) NOT NULL,
        first_name VARCHAR(20) NOT NULL,
        last_name VARCHAR(20) NOT NULL,
        photo_id INT NOT NULL,
        registered_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (photo_id) REFERENCES File (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Upvote (
        post_id INT NOT NULL,
        reader_id INT NOT NULL,
        upvoted_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATE NULL,
        PRIMARY KEY (post_id, reader_id),
        FOREIGN KEY (post_id) REFERENCES Post (id),
        FOREIGN KEY (reader_id) REFERENCES Reader (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Comment (
        id INT NOT NULL AUTO_INCREMENT,
        post_id INT NOT NULL,
        reader_id INT NOT NULL,
        comment_id INT NULL,
        created_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATE NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (post_id) REFERENCES Post (id),
        FOREIGN KEY (reader_id) REFERENCES Reader (id),
        FOREIGN KEY (comment_id) REFERENCES Comment (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Category (
        id INT NOT NULL AUTO_INCREMENT,
        category_id INT NOT NULL,
        created_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATE NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (category_id) REFERENCES Category (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Category_x_Post (
        post_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (post_id, category_id),
        FOREIGN KEY (post_id) REFERENCES Post (id),
        FOREIGN KEY (category_id) REFERENCES Category (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;