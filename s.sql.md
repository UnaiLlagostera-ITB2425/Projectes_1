[![Índice](https://img.shields.io/badge/_Volver_al_Índice--badge&logo=house&logoColor=white)](./)

# 🧭 Guía para Configurar y Ejecutar la Base de Datos MySQL (Proyecto CRUD)

## 🧱 1. Crear y Ejecutar el Script SQL

El fichero `scriptbd.sql` contiene las instrucciones para crear la base de datos y la tabla principal del proyecto.

### 📄 Ejemplo de `scriptbd.sql`

```sql
CREATE DATABASE IF NOT EXISTS crud_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE crud_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL
);





---

[![Índice](https://img.shields.io/badge/_Volver_al_Índice--badge&logo=house&logoColor=white)](./)