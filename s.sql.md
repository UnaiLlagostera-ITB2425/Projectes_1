[![Índice](https://img.shields.io/badge/_Volver_al_Índice--badge&logo=house&logoColor=white)](./)

# 🧭 Guía para Configurar y Ejecutar la Base de Datos MySQL (Proyecto CRUD)


## 🪛 1. Preparación del Código Original

Antes de crear y ejecutar el script, recibimos un fichero que contenía **todo el código mezclado.**

Nuestra primera tarea fue **separar las partes**, dejando el fragmento SQL por separado para poder trabajar correctamente con la base de datos.  

El código original del fichero SQL estaba así, **sin corregir los fallos**:

```sql
CREATE DATABASE crud_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci Where false;

USE crud_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL
);
```

## 🧱 2. Crear y Ejecutar el Script SQL

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
```

Ahora ya tenemos la **base de datos** y sus **tablas** creadas.

[![Índice](https://img.shields.io/badge/_Volver_al_Índice--badge&logo=house&logoColor=white)](./)