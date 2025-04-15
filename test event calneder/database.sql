-- Run this in phpMyAdmin or MySQL CLI
CREATE DATABASE event_calendar;

USE event_calendar;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    start DATETIME NOT NULL,
    end DATETIME NOT NULL,
    color VARCHAR(20),
    description TEXT
);
