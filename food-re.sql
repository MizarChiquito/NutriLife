DROP DATABASE IF EXISTS foodre_db;

CREATE DATABASE foodre_db;

USE foodre_db;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (name) VALUES ('Nutriologo'), ('Paciente');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    
    -- El hash de Bcrypt/Argon2 puede ser largo, 255 es seguro.
    password_hash VARCHAR(255) NOT NULL, 
    
    role_id INT NOT NULL,
    
    CONSTRAINT fk_role
        FOREIGN KEY (role_id) 
        REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

use foodre_db;

-- Agregar columnas a la tabla 'users'
ALTER TABLE users
ADD COLUMN weight DECIMAL(5, 2) NULL, -- Peso: hasta 999.99 kg
ADD COLUMN height DECIMAL(4, 2) NULL; -- Altura: hasta 9.99 metros

INSERT INTO roles (name) VALUES ('Administrador');

INSERT INTO users (first_name, last_name, email, password_hash, role_id)
VALUES (
    'Admin', 
    'Sistema', 
    'admin@nutrilife.com', -- CAMBIA ESTE EMAIL
    '$2y$10$T9yP3lVjQ/A5Zc8GvN5mP.t5.l6W8I6s7H4q9r2u3E4I5J6K7L8M9N0O', -- PEGA AQUÍ EL HASH QUE GENERASTE
    3 -- Reemplaza 3 si tu ID de 'Administrador' es diferente
);

INSERT INTO users (first_name, last_name, email, password_hash, role_id)
VALUES (
    'Admin', 
    'Sistema', 
    'admin@nutri.com', -- CAMBIA ESTE EMAIL
    '$2y$10$TwNqBea8/v2eU9fxBIIh7.HRTdzBUiRWnfYicQxU4XKtMFcRO9RIC', -- PEGA AQUÍ EL HASH QUE GENERASTE
    3 -- Reemplaza 3 si tu ID de 'Administrador' es diferente
);

use foodre_db;

SELECT * FROM users;


