select * from users;

-- 1. Crear la tabla de roles
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- 2. Insertar los roles base
INSERT INTO roles (name) VALUES ('Nutriologo'), ('Paciente');

-- 3. Crear la tabla de usuarios
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    
    -- El hash de Bcrypt/Argon2 puede ser largo, 255 es seguro.
    password_hash VARCHAR(255) NOT NULL, 
    
    -- Clave foránea que referencia la tabla 'roles'
    role_id INT NOT NULL,
    
    CONSTRAINT fk_role
        FOREIGN KEY (role_id) 
        REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);