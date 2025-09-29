# Conexión con GitHub

## 🎯 Objetivo
Configurar el acceso seguro a GitHub desde los servidores mediante SSH para poder clonar y actualizar el repositorio a través de la línea de comandos (CLT).

---

## 🔑 Generación de Claves RSA

### 📝 Proceso de Creación de Claves

En la máquina que usaremos como entorno de desarrollo, crearemos un par de claves RSA (pública y privada) específicas para este proyecto.

```bash
# Navegar al directorio de claves SSH
cd ~/.ssh

# Generar par de claves RSA de 4096 bits
ssh-keygen -t rsa -b 4096 -C "desarrollo@itb-project" -f ~/.ssh/itb_project_rsa
```

**Explicación del comando:**
- `-t rsa`: Especifica el tipo de clave (RSA)
- `-b 4096`: Establece el tamaño de la clave en 4096 bits (más seguro)
- `-C "desarrollo@itb-project"`: Añade un comentario identificativo
- `-f ~/.ssh/itb_project_rsa`: Define el nombre del archivo de la clave

**Salida esperada:**
```
Generating public/private rsa key pair.
Enter passphrase (empty for no passphrase): [Dejar vacío para este proyecto]
Enter same passphrase again: [Dejar vacío]
Your identification has been saved in /home/usuario/.ssh/itb_project_rsa
Your public key has been saved in /home/usuario/.ssh/itb_project_rsa.pub
The key fingerprint is:
SHA256:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx desarrollo@itb-project
The key's randomart image is:
+---[RSA 4096]----+
|          .o+    |
|         o o .   |
|        . . .    |
|       . .   .   |
|        S . . .  |
|       . = . . . |
|      . + * . . .|
|       . + * . . |
|        E=+ . .  |
+----[SHA256]-----+
```

![Generación de Claves SSH](img/ssh-key-generation.png)
*Captura: Proceso de generación de claves RSA*

---

## 📤 Envío de Clave Pública

### 📋 Proceso de Registro en GitHub

Una vez generadas las claves, debemos enviar la clave pública al administrador del repositorio para su registro en GitHub.

**Paso 1: Mostrar la clave pública**
```bash
# Mostrar el contenido de la clave pública
cat ~/.ssh/itb_project_rsa.pub
```

**Contenido esperado (ejemplo):**
```
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDC1... (continúa) ... desarrollo@itb-project
```

**Paso 2: Copiar la clave pública**
```bash
# En sistemas Linux/Mac
cat ~/.ssh/itb_project_rsa.pub | pbcopy

# O simplemente seleccionar y copiar la salida del comando anterior
```

![Clave Pública](img/public-key-content.png)
*Captura: Contenido de la clave pública lista para copiar*

**Paso 3: El administrador debe registrar la clave en GitHub:**
1. Ir a **Settings** → **SSH and GPG keys**
2. Clic en **New SSH key**
3. Pegar la clave pública en el campo "Key"
4. Asignar un nombre descriptivo: `Servidor Desarrollo - ITB Project`
5. Clic en **Add SSH key**

![Registro GitHub](img/github-ssh-registration.png)
*Captura: Interfaz de GitHub para añadir nueva clave SSH*

---

## 💻 Conexión desde Línea de Comandos

### ⚙️ Configuración del Cliente SSH

**Configurar el agente SSH y añadir la clave privada:**
```bash
# Iniciar el agente SSH en segundo plano
eval "$(ssh-agent -s)"

# Añadir la clave privada al agente
ssh-add ~/.ssh/itb_project_rsa
```

**Salida esperada:**
```
Agent pid 12345
Identity added: /home/usuario/.ssh/itb_project_rsa (desarrollo@itb-project)
```

### 🔧 Configuración del Archivo SSH Config

Crear o editar el archivo de configuración SSH para gestionar múltiples claves:
```bash
# Editar el archivo de configuración
nano ~/.ssh/config
```

**Añadir la siguiente configuración:**
```config
# Configuración para el repositorio ITB Project
Host github-itb
    HostName github.com
    User git
    IdentityFile ~/.ssh/itb_project_rsa
    IdentitiesOnly yes
```

![Configuración SSH](img/ssh-config-file.png)
*Captura: Archivo de configuración SSH personalizado*

---

## 🔍 Verificación de la Conexión

### ✅ Comprobar la Autenticación

**Probar la conexión SSH con GitHub:**
```bash
# Verificar la conexión usando nuestro host configurado
ssh -T github-itb
```

**Salida esperada (éxito):**
```
Hi UnaiLlagostera-ITB2425/Projectes_1! You've successfully authenticated, but GitHub does not provide shell access.
```

**Verificar que la clave está cargada correctamente:**
```bash
# Listar claves cargadas en el agente
ssh-add -l

# Verificar la configuración SSH
ssh -G github-itb
```

![Verificación Conexión](img/ssh-connection-test.png)
*Captura: Verificación exitosa de la conexión SSH*

---

## 📥 Clonación del Repositorio

### 🚀 Comandos para Clonar y Configurar

**Clonar el repositorio usando nuestra configuración SSH:**
```bash
# Clonar usando el host configurado
git clone github-itb:UnaiLlagostera-ITB2425/Projectes_1.git

# Navegar al directorio del proyecto
cd Projectes_1
```

**Configurar el usuario de Git (solo primera vez):**
```bash
# Configurar nombre de usuario
git config user.name "Unai Llagostera"

# Configurar email
git config user.email "tu-email@itb.cat"

# Verificar configuración
git config --list
```

![Clonación Repositorio](img/git-clone-success.png)
*Captura: Repositorio clonado exitosamente*

---

## 🛠️ Comandos Esenciales de Git

### 📋 Lista de Comandos Básicos

```bash
# Estado del repositorio
git status

# Añadir archivos al staging
git add .

# Hacer commit de los cambios
git commit -m "Descripción del cambio"

# Subir cambios al repositorio remoto
git push origin main

# Actualizar repositorio local
git pull origin main

# Ver historial de commits
git log --oneline --graph
```

### 🔄 Flujo de Trabajo Típico

```bash
# 1. Actualizar repositorio local
git pull origin main

# 2. Realizar cambios en el código
# ... editar archivos ...

# 3. Verificar cambios
git status

# 4. Añadir cambios al staging
git add .

# 5. Hacer commit
git commit -m "fix: corregir error en conexión BD"

# 6. Subir cambios
git push origin main
```

![Flujo Git](img/git-workflow.png)
*Captura: Ejemplo de flujo de trabajo con Git*

---

## 🚨 Solución de Problemas Comunes

### ❌ Error: Permiso Denegado

```bash
# Verificar permisos de las claves
chmod 600 ~/.ssh/itb_project_rsa
chmod 644 ~/.ssh/itb_project_rsa.pub

# Verificar que el agente SSH está corriendo
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/itb_project_rsa
```

### ❌ Error: Clave No Encontrada

```bash
# Verificar que la clave está cargada
ssh-add -l

# Si no aparece, añadirla manualmente
ssh-add ~/.ssh/itb_project_rsa
```

### ❌ Error: Host No Reconocido

```bash
# Verificar la configuración SSH
ssh -T git@github.com

# O usando nuestra configuración personalizada
ssh -T github-itb
```

---

## ✅ Checklist de Configuración

- [x] Claves RSA generadas correctamente
- [x] Clave pública registrada en GitHub
- [x] Configuración SSH personalizada creada
- [x] Conexión SSH verificada exitosamente
- [x] Repositorio clonado correctamente
- [x] Configuración de usuario Git establecida
- [x] Permisos de archivos SSH configurados

---

## 📝 Resumen de Comandos Clave

| Comando | Función |
|---------|---------|
| `ssh-keygen -t rsa -b 4096` | Generar nuevas claves RSA |
| `ssh-add ~/.ssh/itb_project_rsa` | Añadir clave al agente SSH |
| `ssh -T github-itb` | Verificar conexión con GitHub |
| `git clone github-itb:usuario/repo.git` | Clonar repositorio |
| `git config user.name "Nombre"` | Configurar usuario Git |

---

<div align="center">

[![🏠 Volver al Índice](https://img.shields.io/badge/🏠_VOLVER_AL_ÍNDICE_PRINCIPAL-ReadMe_Completo-FF6B6B?style=for-the-badge&logo=house&logoColor=white)](readme.md)

</div>

**🔄 ¿Problemas con la conexión?** Revisa los permisos de las claves y verifica que la clave pública esté correctamente registrada en GitHub.







[![Índice](https://img.shields.io/badge/_Volver_al_Índice--badge&logo=house&logoColor=white)](readme.md)