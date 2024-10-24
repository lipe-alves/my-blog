CREATE DATABASE IF NOT EXISTS my_blog;

USE DATABASE my_blog;

CREATE TABLE Post  (
    id int NOT NULL AUTO_INCREMENT,
    title varchar(60) NOT NULL,
    `text` text(5000) NOT NULL,
    created_at date NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    deleted bool NOT NULL DEFAULT 0,
    deleted_at date NULL, 
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE Slug (
    slug varchar(60) NOT NULL,
    post_id int,
    PRIMARY KEY (slug),
    FOREIGN KEY (post_id) REFERENCES Post (id)
) DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE File (
    id int NOT NULL AUTO_INCREMENT,
    `filename` varchar(50) NOT NULL,
    `type` varchar(50) NOT NULL DEFAULT "text/plain",
    `path` varchar(500) NOT NULL,
    `size` int NOT NULL DEFAULT 0,
    created_at date NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    deleted bool NOT NULL DEFAULT 0,
    deleted_at date NULL,
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE Reader (
    id: int NOT NULL AUTO_INCREMENT,
    email varchar(320) NOT NULL,
    first_name varchar(20) NOT NULL,
    last_name varchar(20) NOT NULL,
    photo_id int NOT NULL,
    registered_at date NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    PRIMARY KEY (photo_id) REFERENCES File(id)
) DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE Upvote (
    post_id int NOT NULL,
    reader_id int NOT NULL,
    upvoted_at date NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    deleted bool NOT NULL DEFAULT 0,
    deleted_at date NULL,
    PRIMARY KEY (post_id, reader_id),
    FOREIGN KEY (post_id) REFERENCES Post (id),
    FOREIGN KEY (reader_id) REFERENCES Reader(id)
) DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE Comment (
    id: int NOT NULL AUTO_INCREMENT,
    post_id int NOT NULL,
    reader_id int NOT NULL,
    comment_id int NULL,
    created_at date NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    deleted bool NOT NULL DEFAULT 0,
    deleted_at date NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (post_id) REFERENCES Post (id),
    FOREIGN KEY (reader_id) REFERENCES Reader(id),
    FOREIGN KEY (comment_id) REFERENCES Comment(id)
) DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

CREATE TABLE Categories (
    id: int NOT NULL AUTO_INCREMENT,
    created_at date NOT NULL DEFAULT (CURRENT_TIMESTAMP),
    deleted bool NOT NULL DEFAULT 0,
    deleted_at date NULL,
    PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;