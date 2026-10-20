-- ============================================================
-- CFMTS - Campus Facility Maintenance Tracking System
-- Database schema + seed data
-- Import this file into your MySQL database
-- (XAMPP: phpMyAdmin > Import  |  InfinityFree: phpMyAdmin > Import)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. roles
-- ------------------------------------------------------------
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
  role_id   INT AUTO_INCREMENT NOT NULL,
  role_name VARCHAR(50) NOT NULL,
  PRIMARY KEY (role_id),
  UNIQUE KEY uq_roles_name (role_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (role_id, role_name) VALUES
  (1, 'Requester'),
  (2, 'Technician'),
  (3, 'Admin');

-- ------------------------------------------------------------
-- 2. users
-- ------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  user_id    INT AUTO_INCREMENT NOT NULL,
  full_name  VARCHAR(100) NOT NULL,
  email      VARCHAR(120) NOT NULL,
  role_id    INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role_id),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id)
    REFERENCES roles (role_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (user_id, full_name, email, role_id) VALUES
  (1, 'Maria Santos',      'maria.santos@campus.edu',    1),
  (2, 'Juan Dela Cruz',    'juan.delacruz@campus.edu',   1),
  (3, 'Angela Reyes',      'angela.reyes@campus.edu',    1),
  (4, 'Paolo Mendoza',     'paolo.mendoza@campus.edu',   2),
  (5, 'Rico Villanueva',   'rico.villanueva@campus.edu', 2),
  (6, 'Liza Ramos',        'liza.ramos@campus.edu',      2),
  (7, 'Admin Account',     'admin@campus.edu',           3);

-- ------------------------------------------------------------
-- 3. buildings
-- ------------------------------------------------------------
DROP TABLE IF EXISTS buildings;
CREATE TABLE buildings (
  building_id   INT AUTO_INCREMENT NOT NULL,
  building_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (building_id),
  UNIQUE KEY uq_buildings_name (building_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO buildings (building_id, building_name) VALUES
  (1, 'Academic Building'),
  (2, 'Science Hall'),
  (3, 'Library'),
  (4, 'Gymnasium'),
  (5, 'Dormitory A'),
  (6, 'Administration Building');

-- ------------------------------------------------------------
-- 4. rooms
-- ------------------------------------------------------------
DROP TABLE IF EXISTS rooms;
CREATE TABLE rooms (
  room_id     INT AUTO_INCREMENT NOT NULL,
  building_id INT NOT NULL,
  room_number VARCHAR(30) NOT NULL,
  floor_level VARCHAR(30) NOT NULL,
  PRIMARY KEY (room_id),
  KEY idx_rooms_building (building_id),
  CONSTRAINT fk_rooms_building FOREIGN KEY (building_id)
    REFERENCES buildings (building_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rooms (room_id, building_id, room_number, floor_level) VALUES
  (1,  1, '101', 'Ground Floor'),
  (2,  1, '102', 'Ground Floor'),
  (3,  1, '201', 'Second Floor'),
  (4,  1, '202', 'Second Floor'),
  (5,  2, 'Lab 1', 'Ground Floor'),
  (6,  2, 'Lab 2', 'Ground Floor'),
  (7,  2, 'Lecture Room', 'Second Floor'),
  (8,  3, 'Reading Room', 'Ground Floor'),
  (9,  3, 'Computer Area', 'Second Floor'),
  (10, 4, 'Court A', 'Ground Floor'),
  (11, 4, 'Changing Room', 'Ground Floor'),
  (12, 5, 'Room 1A', 'Ground Floor'),
  (13, 5, 'Room 2B', 'Second Floor'),
  (14, 5, 'Room 3C', 'Third Floor'),
  (15, 6, 'Records Office', 'Ground Floor'),
  (16, 6, 'Conference Room', 'Second Floor');

-- ------------------------------------------------------------
-- 5. categories
-- ------------------------------------------------------------
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
  category_id   INT AUTO_INCREMENT NOT NULL,
  category_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (category_id),
  UNIQUE KEY uq_categories_name (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (category_id, category_name) VALUES
  (1, 'Plumbing'),
  (2, 'Electrical'),
  (3, 'HVAC / Ventilation'),
  (4, 'Carpentry & Furniture'),
  (5, 'Cleaning & Waste'),
  (6, 'IT / Network'),
  (7, 'Safety & Security'),
  (8, 'Pest Control');

-- ------------------------------------------------------------
-- 6. maintenance_requests  (main table)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS maintenance_requests;
CREATE TABLE maintenance_requests (
  request_id     INT AUTO_INCREMENT NOT NULL,
  requester_id   INT NOT NULL,
  room_id        INT NOT NULL,
  category_id    INT NOT NULL,
  description    TEXT NOT NULL,
  priority_level ENUM('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  status         ENUM('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  date_submitted DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_resolved  DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (request_id),
  KEY idx_mr_requester (requester_id),
  KEY idx_mr_room (room_id),
  KEY idx_mr_category (category_id),
  KEY idx_mr_status (status),
  CONSTRAINT fk_mr_requester FOREIGN KEY (requester_id)
    REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_mr_room FOREIGN KEY (room_id)
    REFERENCES rooms (room_id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_mr_category FOREIGN KEY (category_id)
    REFERENCES categories (category_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO maintenance_requests
  (request_id, requester_id, room_id, category_id, description, priority_level, status, date_submitted, date_resolved)
VALUES
  (1, 1, 1, 2, 'Fluorescent light above the lectern flickers constantly during classes.', 'High', 'In Progress', '2026-10-06 08:15:00', NULL),
  (2, 2, 5, 1, 'Sink in Lab 1 is leaking under the cabinet; water pooling on the floor.', 'Urgent', 'Pending', '2026-10-08 10:42:00', NULL),
  (3, 3, 8, 3, 'Air conditioning unit blows warm air, reading room is very hot in the afternoon.', 'Medium', 'Pending', '2026-10-09 13:20:00', NULL),
  (4, 1, 12, 4, 'Bunk bed frame is loose and squeaks; one support slat is cracked.', 'Medium', 'Completed', '2026-10-01 09:05:00', '2026-10-03 16:30:00'),
  (5, 2, 9, 6, 'Wi-Fi drops intermittently in the computer area, especially near the back row.', 'High', 'In Progress', '2026-10-10 15:48:00', NULL),
  (6, 3, 15, 7, 'Door lock on the Records Office is jammed and cannot be locked after hours.', 'Urgent', 'Pending', '2026-10-12 07:55:00', NULL),
  (7, 1, 10, 5, 'Trash bins on Court A are overflowing since the sports festival.', 'Low', 'Completed', '2026-09-28 11:10:00', '2026-09-29 08:40:00'),
  (8, 2, 3, 2, 'Power outlet near the window is dead; two students cannot charge laptops.', 'High', 'Cancelled', '2026-10-04 14:25:00', '2026-10-05 09:12:00');

-- ------------------------------------------------------------
-- 7. request_assignments
-- ------------------------------------------------------------
DROP TABLE IF EXISTS request_assignments;
CREATE TABLE request_assignments (
  assignment_id INT AUTO_INCREMENT NOT NULL,
  request_id    INT NOT NULL,
  technician_id INT NOT NULL,
  assigned_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (assignment_id),
  KEY idx_ra_request (request_id),
  KEY idx_ra_technician (technician_id),
  CONSTRAINT fk_ra_request FOREIGN KEY (request_id)
    REFERENCES maintenance_requests (request_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ra_technician FOREIGN KEY (technician_id)
    REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO request_assignments (request_id, technician_id, assigned_date) VALUES
  (1, 4, '2026-10-06 09:00:00'),
  (5, 6, '2026-10-10 16:30:00'),
  (4, 5, '2026-10-01 10:00:00'),
  (7, 4, '2026-09-28 12:00:00');

-- ------------------------------------------------------------
-- 8. activity_logs
-- ------------------------------------------------------------
DROP TABLE IF EXISTS activity_logs;
CREATE TABLE activity_logs (
  log_id        INT AUTO_INCREMENT NOT NULL,
  request_id    INT NOT NULL,
  user_id       INT NULL,
  action_taken  VARCHAR(255) NOT NULL,
  `timestamp`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (log_id),
  KEY idx_al_request (request_id),
  KEY idx_al_user (user_id),
  CONSTRAINT fk_al_request FOREIGN KEY (request_id)
    REFERENCES maintenance_requests (request_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_al_user FOREIGN KEY (user_id)
    REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO activity_logs (request_id, user_id, action_taken, `timestamp`) VALUES
  (1, 1, 'Request submitted',                                              '2026-10-06 08:15:00'),
  (1, 7, 'Updated request — status set to "In Progress", priority "High"', '2026-10-06 09:00:00'),
  (1, 7, 'Technician assignment updated',                                  '2026-10-06 09:00:05'),
  (2, 2, 'Request submitted',                                              '2026-10-08 10:42:00'),
  (3, 3, 'Request submitted',                                              '2026-10-09 13:20:00'),
  (4, 1, 'Request submitted',                                              '2026-10-01 09:05:00'),
  (4, 7, 'Updated request — status set to "Completed", priority "Medium"', '2026-10-03 16:30:00'),
  (5, 2, 'Request submitted',                                              '2026-10-10 15:48:00'),
  (5, 7, 'Technician assignment updated',                                  '2026-10-10 16:30:00'),
  (6, 3, 'Request submitted',                                              '2026-10-12 07:55:00'),
  (7, 1, 'Request submitted',                                              '2026-09-28 11:10:00'),
  (7, 7, 'Updated request — status set to "Completed", priority "Low"',    '2026-09-29 08:40:00'),
  (8, 2, 'Request submitted',                                              '2026-10-04 14:25:00'),
  (8, 7, 'Updated request — status set to "Cancelled", priority "High"',   '2026-10-05 09:12:00');

-- ------------------------------------------------------------
-- 9. feedback
-- ------------------------------------------------------------
DROP TABLE IF EXISTS feedback;
CREATE TABLE feedback (
  feedback_id INT AUTO_INCREMENT NOT NULL,
  request_id  INT NOT NULL,
  user_id     INT NULL,
  rating      TINYINT NULL,
  comments    TEXT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (feedback_id),
  KEY idx_fb_request (request_id),
  CONSTRAINT fk_fb_request FOREIGN KEY (request_id)
    REFERENCES maintenance_requests (request_id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_fb_user FOREIGN KEY (user_id)
    REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
