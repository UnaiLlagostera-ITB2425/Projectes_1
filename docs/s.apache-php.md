[![Índice](https://img.shields.io/badge/_Ir_al_Índice--badge&logo=house&logoColor=white)](/docs/README.md)
# 🧰 Guía: Servidor Web PHP conectado a MySQL en otra IP

## 🧱 Escenario

* **Servidor web (Apache + PHP):** `192.168.1.10`
* **Servidor de base de datos (MySQL):** `192.168.1.20`
* **Objetivo:** Aplicación CRUD mínima en PHP conectada a MySQL remoto.

---

## ⚙️ 1. Configuración de Apache y PHP

Instalar Apache y PHP en el servidor web:

```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql -y
```

Verificar que PHP funciona creando el archivo:

```bash
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php
```

Accede en navegador:
👉 `http://192.168.1.10/info.php`

---

## 🗃️ 2. Configuración del servidor MySQL (en 192.168.1.20)

Instalar MySQL si no está instalado:

```bash
sudo apt install mysql-server -y
```

Permitir conexiones remotas:

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Cambia la línea:

```
bind-address = 127.0.0.1
```

por:

```
bind-address = 0.0.0.0
```

Reinicia MySQL:

```bash
sudo systemctl restart mysql
```

Crear usuario remoto y dar permisos:

```sql
CREATE USER 'webuser'@'192.168.1.10' IDENTIFIED BY '1234';
GRANT ALL PRIVILEGES ON *.* TO 'webuser'@'192.168.1.10';
FLUSH PRIVILEGES;
```

---

## 🔗 3. Estructura de la aplicación PHP

Ubicación del código: `/var/www/html/`

Archivos:

```
index.php
add.php
edit.php
delete.php
db.php
```

---

## 🧩 4. Archivo `db.php`

```php
<?php
$host = '192.168.1.20';
$user = 'webuser';
$pass = '1234';
$db   = 'crud_php';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexió fallida: " . $conn->connect_error);
}
?>
```

---

## 📄 5. Archivo `index.php`

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
    <h1>Llista d’usuaris</h1>
    <table border="1">
        <tr><th>ID</th><th>Nom</th><th>Email</th><th>Accions</th></tr>
        <?php
        $result = $conn->query("SELECT * FROM users");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <a href='./edit.php?id={$row['id']}'>Editar</a> |
                        <a href='./delete.php?id={$row['id']}'>Eliminar</a>
                    </td>
                 </tr>";
        }
        ?>
    </table>

    <h2>Afegir usuari</h2>
    <form action="./add.php" method="post">
        Nom: <input type="text" name="name" required>
        Email: <input type="email" name="email" required>
        <button type="submit">Afegir</button>
    </form>
</body>
</html>
```

---

## ➕ 6. Archivo `add.php`

```php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>
```

---

## ✏️ 7. Archivo `edit.php`

```php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM users WHERE id=$id");
    $user = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)$_POST['id'];
    $name  = $_POST['name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $email, $id);
    $stmt->execute();

    header("Location: index.php");
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
    <form method="post">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        Nom: <input type="text" name="name" value="<?= $user['name'] ?>" required>
        Email: <input type="email" name="email" value="<?= $user['email'] ?>" required>
        <button type="submit">Desar</button>
    </form>
</body>
</html>
```

---

## ❌ 8. Archivo `delete.php`

```php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'db.php';

$id = (int)$_GET['id'];
$conn->query("DELETE FROM users WHERE id=$id");

header("Location: index.php");
exit;
?>
```

---

## 🧪 9. Prueba de conexión independiente (`testdb.php`)

```php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = '192.168.1.20';
$user = 'webuser';
$pass = '1234';
$db   = 'crud_php';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de connexió: " . $conn->connect_error);
}

echo "Conexió OK!";
?>
```

Acceder en navegador:
👉 `http://192.168.1.10/testdb.php`

---

## 🔄 10. Vincular carpeta del repositorio al servidor web (symlink)

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

## ✅ 12. Verificación final

1. Abre en navegador:
   👉 `http://192.168.1.10/index.php`

2. Asegúrate de que:

   * Se muestra la tabla de usuarios (aunque esté vacía).
   * El formulario de agregar usuario funciona.
   * Puedes editar y eliminar usuarios correctamente.

---
[![Índice](https://img.shields.io/badge/_Ir_al_Índice--badge&logo=house&logoColor=white)](/docs/README.md)
