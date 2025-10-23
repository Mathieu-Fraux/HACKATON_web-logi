-- Create the database for the sustainable delivery service
-- We use CHARACTER SET utf8 and COLLATE utf8_general_ci as requested
CREATE DATABASE IF NOT EXISTS sustainable_delivery
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

-- Switch to the newly created database
USE sustainable_delivery;

-- Create the USER table
-- This table stores information about all users, who can act as deliverers
CREATE TABLE IF NOT EXISTS `USER` (
    `id_user` INT AUTO_INCREMENT PRIMARY KEY,
    `login` VARCHAR(50) NULL UNIQUE COMMENT 'User login name',
    `password` VARCHAR(255) NULL COMMENT 'Hashed password (NEVER store plain text)',
    `first_name` VARCHAR(100) NULL,
    `last_name` VARCHAR(100) NULL,
    `email` VARCHAR(100) NULL UNIQUE COMMENT 'User email for notifications and recovery',
    `phone` VARCHAR(20) NULL COMMENT 'User phone number (optional but recommended)',
    `vehicle` VARCHAR(100) NULL COMMENT 'Vehicle model/type (e.g., "Renault Clio", "Velo cargo", "Peugeot 208")',
    `location` VARCHAR(255) NULL COMMENT 'Full address: "88 allées Jean Jaurès 31000 Toulouse"',
    `range_km` INT NULL COMMENT 'Maximum delivery range in kilometers (max 100)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Date and time of account creation'
) ENGINE=InnoDB;

-- Create the DELIVERY table
-- This table stores all delivery requests, including those from the API
CREATE TABLE IF NOT EXISTS `DELIVERY` (
    `id_delivery` INT AUTO_INCREMENT PRIMARY KEY,
    `source` VARCHAR(255) NULL COMMENT 'Source address for the delivery',
    `destination` VARCHAR(255) NULL COMMENT 'Destination address for the delivery',
    `weight_g` FLOAT NULL COMMENT 'Weight of the parcel in grams',
    `is_bulky` BOOLEAN DEFAULT FALSE COMMENT 'Optional: True if the parcel is bulky',
    `is_fresh` BOOLEAN DEFAULT FALSE COMMENT 'Optional: True if the parcel requires fresh/cold transport',
    `price` DECIMAL(10, 2) NULL COMMENT 'Price of the delivery, set once a deliverer is assigned',
    `status` VARCHAR(50) NULL DEFAULT 'pending' COMMENT 'Current status of the delivery (e.g., pending, assigned, in_progress, completed, cancelled)',
    `id_user_assigned` INT NULL COMMENT 'The ID of the user (deliverer) assigned to this delivery',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Date and time the delivery was registered',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date and time the delivery was last updated (e.g., status change)',

    -- Foreign key constraint
    -- Links this delivery to a user in the USER table
    -- ON DELETE SET NULL: If a user is deleted, the delivery is not deleted,
    -- but it becomes unassigned (id_user_assigned becomes NULL).
    FOREIGN KEY (`id_user_assigned`) REFERENCES `USER`(`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB;