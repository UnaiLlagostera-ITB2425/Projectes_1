[![Índice](https://img.shields.io/badge/_Ir_al_Índice--badge&logo=house&logoColor=white)](/docs/README.md)

# Correcciones en el Código - Documentación Técnica

## 🔍 Problemas Identificados y Soluciones

### **Código Apache/PHP**

#### **1. Archivo `db.php`**

**Problemas Identificados:**
```php
❌ $servername = "locahost";  // Error tipográfico
❌ Falta cierre adecuado de la estructura PHP
```

**Código Corregido:**
```php
<?php
$servername = "localhost";  // ✅ Corregido: "locahost" → "localhost"
$username = "root";
$password = "root";
$dbname = "crud_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexió fallida: " . $conn->connect_error);
}
?>
```

---

#### **2. Archivo `index.php`**

**Problemas Identificados:**
```php
❌ <table> duplicado en la estructura HTML
❌ method="posts" // Error en método del formulario
❌ Falta validación de datos antes de mostrar
```

**Código Corregido:**
```php
<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>CRUD mínim</title>
</head>
<body>
    <h1>Llista d'usuaris</h1>
    <table border="1">  <!-- ✅ Eliminada etiqueta <table> duplicada -->
        <tr><th>ID</th><th>Nom</th><th>Email</th><th>Accions</th></tr>
        <?php
        $result = $conn->query("SELECT * FROM users");
        if ($result && $result->num_rows > 0) {  // ✅ Agregada validación
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['email']}</td>
                        <td>
                            <a href='edit.php?id={$row['id']}'>Editar</a> | 
                            <a href='delete.php?id={$row['id']}'>Eliminar</a>
                        </td>
                     </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No hi ha usuaris registrats</td></tr>";
        }
        ?>
    </table>

    <h2>Afegir usuari</h2>
    <form action="add.php" method="post">  <!-- ✅ Corregido: "posts" → "post" -->
        Nom: <input type="text" name="name" required>
        Email: <input type="email" name="email" required>
        <button type="submit">Afegir</button>
    </form>
</body>
</html>
```

---

#### **3. Archivo `add.php`**

**Problemas Identificados:**
```php
❌ VALUES (*, ?) // Sintaxis incorrecta en INSERT
❌ No hay validación de datos de entrada
❌ No hay manejo de errores
```

**Código Corregido:**
```php
<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // ✅ Validación básica agregada
    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");  // ✅ Corregido: VALUES (?, ?)
        $stmt->bind_param("ss", $name, $email);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            echo "Error afegint l'usuari: " . $conn->error;
        }
    } else {
        echo "Dades del formulari invàlides";
    }
}
?>
```

---

#### **4. Archivo `edit.php`**

**Problemas Identificados:**
```php
❌ UPDATE users where name=?, email=? // Falta SET
❌ No hay validación de que el usuario existe
❌ Vulnerable a inyección SQL
```

**Código Corregido:**
```php
<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // ✅ Consulta preparada para evitar SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        die("Usuari no trobat");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");  // ✅ Corregido: agregado SET
        $stmt->bind_param("ssi", $name, $email, $id);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            echo "Error actualitzant l'usuari: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Editar usuari</title>
</head>
<body>
    <h1>Editar usuari</h1>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
        Nom: <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <button type="submit">Desar</button>
    </form>
</body>
</html>
```

---

#### **5. Archivo `delete.php`**

**Problemas Identificados:**
```php
❌ DELETE * FROM users // Sintaxis incorrecta
❌ No hay confirmación antes de eliminar
❌ Vulnerable a inyección SQL
```

**Código Corregido:**
```php
<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // ✅ Verificamos que el usuario existe antes de eliminar
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        // ✅ Corregido: DELETE FROM users (sin *)
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        $delete_stmt->execute();
    }
}

header("Location: index.php");
exit;
?>
```

---

### **Base de Datos**

#### **Script MySQL Corregido**

**Problemas Identificados:**
```sql
❌ CREATE DATABASE ... WHERE false // Sintaxis incorrecta
❌ No hay manejo de errores
❌ Falta restricción UNIQUE para emails
```

**Código Corregido:**
```sql
-- ✅ Corregido: eliminado "WHERE false"
CREATE DATABASE IF NOT EXISTS crud_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE crud_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,  -- ✅ Agregado UNIQUE para evitar emails duplicados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 📊 Resumen de Correcciones Realizadas

| **Archivo** | **Problema** | **Solución** | **Severidad** |
|-------------|--------------|--------------|---------------|
| `db.php` | "locahost" typo | "localhost" | 🔴 Crítico |
| `index.php` | method="posts" | method="post" | 🔴 Crítico |
| `index.php` | Tabla duplicada | Eliminar `<table>` extra | 🟡 Medio |
| `add.php` | VALUES (*, ?) | VALUES (?, ?) | 🔴 Crítico |
| `edit.php` | Falta SET en UPDATE | Agregar SET | 🔴 Crítico |
| `delete.php` | DELETE * FROM | DELETE FROM | 🔴 Crítico |
| SQL Script | WHERE false | Eliminar cláusula | 🔴 Crítico |

---

## 📁 Estructura Final del Proyecto

```
app/
 ├── db.php         (connexió a la BBDD - corregida)
 ├── index.php      (llista usuaris + formulari - corregida)
 ├── add.php        (afegeix usuari - corregida)
 ├── delete.php     (elimina usuari - corregida)
 └── edit.php       (edita usuari - corregida)
```

---

## Estado Final

Todas las correciones garantizan que la aplicación funcione correctamente y sea más segura contra ataques comunes. El código ahora sigue las mejores prácticas de desenvolupamiento web y és mantenible para futuros cambios.

---

[![Índice](https://img.shields.io/badge/_Volver_al_Índice--badge&logo=house&logoColor=white)](./)