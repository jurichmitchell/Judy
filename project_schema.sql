# Mitchell Jurich
# Last Edit: 4/26/2018
# Deck Database Schema

DROP DATABASE IF EXISTS deck_db;
CREATE DATABASE deck_db;
USE deck_db;

CREATE TABLE deck_user (
	user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	email VARCHAR(60) NOT NULL UNIQUE,
	username VARCHAR(30) NOT NULL UNIQUE,
	password VARCHAR(40) NOT NULL,
	profile_pic VARCHAR(50), #The name of the picture file on the server
	registration_date DATETIME,
	PRIMARY KEY (user_id),
	INDEX (email)
);

CREATE TABLE deck (
	deck_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	creator_id INT UNSIGNED NOT NULL, #fk with user(user_id)
	file_name VARCHAR(50) NOT NULL, #The name of the deck file on the server
	name VARCHAR(50) NOT NULL, #The name of the deck, titled by user
	uploaded DATETIME,
	PRIMARY KEY (deck_id),
	INDEX (creator_id),
	INDEX (name),
	INDEX (uploaded)
);

CREATE TABLE rating (
	user_id INT UNSIGNED NOT NULL,
	deck_id INT UNSIGNED NOT NULL,
	rating TINYINT(1) UNSIGNED NOT NULL,
	PRIMARY KEY (user_id, deck_id),
	INDEX (deck_id)
);

CREATE TABLE genre (
	genre_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(30) UNIQUE,
	PRIMARY KEY (genre_id)
);

CREATE TABLE map_deck_to_genre (
	deck_id INT UNSIGNED NOT NULL,
	genre_id INT UNSIGNED NOT NULL,
	PRIMARY KEY (deck_id, genre_id)
);

ALTER TABLE deck
	ADD FOREIGN KEY (creator_id)
	REFERENCES deck_user (user_id)
	ON DELETE CASCADE;

ALTER TABLE rating
	ADD FOREIGN KEY (user_id)
	REFERENCES deck_user (user_id)
	ON DELETE CASCADE;

ALTER TABLE rating
	ADD FOREIGN KEY (deck_id)
	REFERENCES deck (deck_id)
	ON DELETE CASCADE;

ALTER TABLE map_deck_to_genre
	ADD FOREIGN KEY (deck_id)
	REFERENCES deck (deck_id)
	ON DELETE CASCADE;

ALTER TABLE map_deck_to_genre
	ADD FOREIGN KEY (genre_id)
	REFERENCES genre (genre_id)
	ON DELETE CASCADE;