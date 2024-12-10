CREATE DATABASE IF NOT EXISTS my_blog;

USE my_blog;

CREATE TABLE
    IF NOT EXISTS Post (
        id INT NOT NULL AUTO_INCREMENT,
        title VARCHAR(60) NOT NULL,
        text TEXT NOT NULL,
        slug VARCHAR(60) UNIQUE NOT NULL,
        created_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATE NULL,
        PRIMARY KEY (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS File (
        id INT NOT NULL AUTO_INCREMENT,
        filename VARCHAR(50) NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'text/plain',
        path VARCHAR(500) UNIQUE NOT NULL,
        size INT NOT NULL DEFAULT 0,
        created_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATE NULL,
        PRIMARY KEY (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Reader (
        id INT NOT NULL AUTO_INCREMENT,
        first_name VARCHAR(20) NOT NULL,
        last_name VARCHAR(20) NOT NULL,
        photo VARCHAR(500) NOT NULL,
        email VARCHAR(320) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        registered_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Comment (
        id INT NOT NULL AUTO_INCREMENT,
        post_id INT NOT NULL,
        reader_id INT NOT NULL,
        comment_id INT NULL,
        text TEXT NOT NULL,
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
        name VARCHAR(255) NOT NULL UNIQUE,
        created_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT (1) NOT NULL DEFAULT 0,
        deleted_at DATE NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (category_id) REFERENCES Category (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE
    IF NOT EXISTS Post_x_Category (
        post_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (post_id, category_id),
        FOREIGN KEY (post_id) REFERENCES Post (id),
        FOREIGN KEY (category_id) REFERENCES Category (id)
    ) DEFAULT CHARSET = utf8 DEFAULT COLLATE utf8_unicode_ci;