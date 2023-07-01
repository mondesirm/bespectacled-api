-- Active: 1667242351786@@127.0.0.1@3306@db
CREATE TABLE refresh_tokens(  
	id int NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Primary Key',
	refresh_token VARCHAR(128) NOT NULL COMMENT 'Refresh Token',
	username VARCHAR(255) NOT NULL COMMENT 'Username',
	valid TIMESTAMP NOT NULL COMMENT 'Valid Until'
) COMMENT 'Refresh Tokens';