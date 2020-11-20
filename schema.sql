CREATE DATABASE readme
	DEFAULT CHARACTER SET utf8
	DEFAULT COLLATE utf8_general_ci;

USE readme;

CREATE TABLE user (
	id INT AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	email CHAR(128) NOT NULL UNIQUE,
	login CHAR(255) NOT NULL,
	password CHAR(255) NOT NULL,
	avatar_path CHAR(255)
);

CREATE TABLE post (
	id INT AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	title CHAR(255) NOT NULL,
	content TEXT NOT NULL,
	quote_author CHAR(255),
	image_path CHAR(255),
	video_path CHAR(255),
	link CHAR(255),
	show_count INT NOT NULL DEFAULT 0,
	user_id INT NOT NULL,
	content_type_id INT NOT NULL,
	hashtag_id INT
);

CREATE TABLE comment (
	id INT AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	content TEXT NOT NULL,
	user_id INT NOT NULL,
	post_id INT NOT NULL
);

CREATE TABLE post_like (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	post_id INT NOT NULL
);

CREATE TABLE subscription (
	id INT AUTO_INCREMENT PRIMARY KEY,
	author_id INT NOT NULL,
	user_id INT NOT NULL
);

CREATE TABLE message (
	id INT AUTO_INCREMENT PRIMARY KEY,
	dt_add TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	content TEXT NOT NULL,
	sender_id INT NOT NULL,
	recipient_id INT NOT NULL
);

CREATE TABLE hashtag (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name CHAR(255) NOT NULL
);

CREATE TABLE post_hashtag (
	id INT AUTO_INCREMENT PRIMARY KEY,
	hashtag_id INT NOT NULL,
	post_id INT NOT NULL
);

CREATE TABLE content_type (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name CHAR(64) NOT NULL,
	class_name CHAR(64) NOT NULL
);

CREATE FULLTEXT INDEX t_index ON post(title);
CREATE FULLTEXT INDEX c_index ON post(content);