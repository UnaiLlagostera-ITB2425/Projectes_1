[![Índice](https://img.shields.io/badge/_Volver_al_Índice--badge&logo=house&logoColor=white)](./README.md)

# Guía Completa: Configuración de Servidor Web PHP + MySQL Remoto

## 📋 Descripción del Escenario
- **Servidor Web**: 192.168.1.10 (Ubuntu/Debian)
- **Servidor MySQL**: 192.168.1.20 (Ubuntu/Debian)
- **Aplicación**: CRUD PHP con MySQL remoto

---

## 🛠️ CONFIGURACIÓN INICIAL

### 1. Instalación en Servidor Web (192.168.1.10)

```bash
# Actualizar sistema
sudo apt update

# Instalar Apache, PHP y extensiones necesarias
sudo apt install apache2 php libapache2-mod-php php-mysql mysql-client -y

# Verificar instalación
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php
```

**Verificación**: Acceder a `http://192.168.1.10/info.php`

### 2. Configuración del Servidor MySQL (192.168.1.20)

```bash
# Instalar MySQL Server
sudo apt update
sudo apt install mysql-server -y

# Configurar conexiones remotas
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

**Cambiar**:
```ini
bind-address = 0.0.0.0
```

```bash
# Reiniciar servicio
sudo systemctl restart mysql

# Configurar firewall
sudo ufw allow 3306/tcp
```

### 3. Crear Usuario y Base de Datos MySQL

```sql
-- Acceder a MySQL
sudo mysql

-- Crear usuario y privilegios
CREATE USER 'webuser'@'192.168.1.10' IDENTIFIED BY 'TuPasswordSegura';
CREATE DATABASE test;
GRANT ALL PRIVILEGES ON test.* TO 'webuser'@'192.168.1.10';
FLUSH PRIVILEGES;

-- Crear tabla de ejemplo
USE test;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL
);
```

---

## 📁 ESTRUCTURA DE LA APLICACIÓN PHP

### Configuración del Enlace Simbólico (Recomendado)

```bash
# Eliminar directorio actual de Apache
sudo rm -rf /var/www/html

# Crear enlace simbólico al repositorio
sudo ln -s /home/isard/Projectes_1/app /var/www/html

# Ajustar permisos
sudo chown -R isard:www-data /home/isard/Projectes_1/app
sudo chmod -R 755 /home/isard/Projectes_1/app

# Configurar Apache para seguir enlaces simbólicos
sudo nano /etc/apache2/sites-available/000-default.conf
```

**Añadir en la configuración**:
```apache
<Directory /var/www/html>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

```bash
# Reiniciar Apache
sudo systemctl restart apache2
```

---

## 💾 ARCHIVOS DE LA APLICACIÓN CORREGIDOS

### 1. `db.php` - Conexión a Base de Datos

```php
<?php
$host = '192.168.1.20';
$user = 'webuser';
$pass = 'TuPasswordSegura';
$db   = 'test';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexió BD fallida: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>
```

### 2. `index.php` - Lista y Formulario Principal

```php
<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php'; 
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>CRUD mínim</title>
</head>
<body>
    <h1>Llista d'usuaris</h1>

    <?php
    if (!empty($_GET['success'])) echo "<p style='color:green'>Usuari afegit correctament.</p>";
    if (!empty($_GET['updated'])) echo "<p style='color:blue'>Usuari actualitzat correctament.</p>";
    if (!empty($_GET['deleted'])) echo "<p style='color:red'>Usuari eliminat correctament.</p>";
    if (!empty($_GET['error'])) echo "<p style='color:red'>Error: ".htmlspecialchars($_GET['error'])."</p>";
    ?>

    <table border="1">
        <tr><th>ID</th><th>Nom</th><th>Email</th><th>Accions</th></tr>
        <?php
        $result = $conn->query("SELECT * FROM users");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>".htmlspecialchars($row['id'])."</td>
                        <td>".htmlspecialchars($row['name'])."</td>
                        <td>".htmlspecialchars($row['email'])."</td>
                        <td>
                            <a href='edit.php?id=".urlencode($row['id'])."'>Editar</a> |
                            <a href='delete.php?id=".urlencode($row['id'])."' onclick=\"return confirm('Segur?')\">Eliminar</a>
                        </td>
                     </tr>";
            }
            $result->free();
        } else {
            echo "<tr><td colspan='4'>Error a la base de dades: ".htmlspecialchars($conn->error)."</td></tr>";
        }
        ?>
    </table>

    <h2>Afegir usuari</h2>
    <form action="add.php" method="post">
        Nom: <input type="text" name="name" required>
        Email: <input type="email" name="email" required>
        <button type="submit">Afegir</button>
    </form>
</body>
</html>
```

### 3. `add.php` - Añadir Usuario

```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?error=invalid_input");
    exit;
}

$stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
if (!$stmt) {
    header("Location: index.php?error=db_prepare");
    exit;
}

$stmt->bind_param("ss", $name, $email);
$stmt->execute();
$stmt->close();

header("Location: index.php?success=1");
exit;
?>
```

### 4. `edit.php` - Editar Usuario

```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php';

$user = null;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) die("Usuari no trobat.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)$_POST['id'];
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Dades no vàlides.");
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?updated=1");
    exit;
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
    <?php if ($user): ?>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
        Nom: <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br>
        Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
        <button type="submit">Desar</button>
    </form>
    <?php else: ?>
        <p><a href="index.php">Tornar a la llista</a></p>
    <?php endif; ?>
</body>
</html>
```

### 5. `delete.php` - Eliminar Usuario

```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: index.php?deleted=1");
exit;
?>
```
## 6. Vincular carpeta del repositorio al servidor web (symlink)

Si tu proyecto está en `/home/isard/Projectes_1/app` y quieres que aparezca en `/var/www/html`, ejecuta:

```bash
sudo rm -rf /var/www/html
sudo ln -s /home/isard/Projectes_1/app /var/www/html
```

Esto crea un **enlace simbólico**, de modo que los cambios en el repositorio se reflejan automáticamente en el servidor web.

---

## 🧾 11. Permisos recomendados

```bash
sudo chown -R www-data:www-data /home/isard/Projectes_1/app
sudo chmod -R 755 /home/isard/Projectes_1/app
```

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Si la página aparece en blanco:

1. **Activar visualización de errores**:
```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
```

2. **Verificar conexión a MySQL**:
```bash
mysql -h 192.168.1.20 -u webuser -p
```

3. **Revisar logs de Apache**:
```bash
sudo tail -f /var/log/apache2/error.log
```

4. **Verificar permisos**:
```bash
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
```

### Probar conexión de base de datos:
```php
<?php
// testdb.php
$host = '192.168.1.20';
$user = 'webuser';
$pass = 'TuPasswordSegura';
$db   = 'test';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de connexió: " . $conn->connect_error);
}
echo "Conexió OK!";
?>
```

---

## ✅ VERIFICACIÓN FINAL

1. Acceder a: `http://192.168.1.10/index.php`
2. Probar funcionalidades CRUD:
   - Añadir usuario
   - Editar usuario  
   - Eliminar usuario
3. Verificar que los cambios se reflejan en la base de datos

---

## 📝 NOTAS IMPORTANTES

- **Seguridad**: Cambiar las credenciales por defecto
- **Producción**: Desactivar `display_errors` en entorno de producción
- **Backups**: Realizar backups regulares de la base de datos
- **Firewall**: Asegurar que solo IPs autorizadas pueden acceder al puerto 3306

**¡Configuración completada!** 🎉
































---

[![Índice](https://img.shields.io/badge/_Volver_al_Índice--badge&logo=house&logoColor=white)](./README.md)
