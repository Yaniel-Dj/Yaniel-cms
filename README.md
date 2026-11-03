# Yaniel CMS

Panel web de gestión para servidores privados de **World of Warcraft (AzerothCore 3.3.5a)**. Totalmente bilingüe (Español / Inglés), con tienda, noticias, reglas, rifas, códigos de regalo, guías de profesiones, panel de usuario y un completo panel de administración para GMs.

---

## 📋 Características

- **Autenticación SRP6** compatible con AzerothCore (login y registro).
- **Bilingüe (ES/EN)** en todas las páginas: noticias, reglas, tienda, rifas, guías, panel GM, etc.
- **Tienda** con categorías e items bilingües, compra con oro y envío de items por SOAP.
- **Noticias** bilingües gestionadas desde el panel GM (`noticias_es.json` / `noticias_en.json`).
- **Reglas** bilingües gestionadas desde el panel GM (`reglas_es.json` / `reglas_en.json`).
- **Rifas** con compra de boletos, cuenta atrás, participantes y envío automático del premio al ganador.
- **Códigos de regalo** de un solo uso (oro o item), con usos máximos y fecha de expiración.
- **Guías de profesiones** bilingües (14 profesiones, niveles 1–450).
- **Panel de usuario** con personajes, cambio de contraseña (coste en oro), historial de compras, canje de códigos y rifa.
- **Panel GM** con gestión de items, noticias, usuarios (baneos/rangos), reglas, códigos y rifas.
- **Caja fija** en todas las páginas con nombre de usuario, selector de idioma, botón de atrás y cerrar sesión.
- **Diseño responsive** con temática dorada/azul y fondo personalizable.

---

## 🛠️ Requisitos

- **PHP 8.0 o superior** con extensiones: `pdo_mysql`, `soap`, `gmp`, `session`, `json`.
- **MySQL 5.7 / MariaDB 10.3 o superior**.
- **Apache** (o Nginx) con `mod_rewrite` opcional.
- **AzerothCore** instalado y funcionando (worldserver + authserver).
- **SOAP habilitado** en el worldserver (`WorldServer.conf` → `SOAP.Enabled = 1`).

---

## 🚀 Instalación

1. **Clona el repositorio** en la carpeta de tu servidor web (ej: `C:\laragon\www`):

   ```bash
   git clone /https://github.com/Yaniel-Dj/Yaniel-cms/tree/main
   ```

2. **Configura `config.php`** con tus credenciales de base de datos y SOAP:

   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_PORT', '3306');
   define('DB_USER', 'root');
   define('DB_PASS', 'tu_contraseña');
   define('DB_NAME', 'acore_auth');
   define('CHAR_DB_NAME', 'acore_characters');
   define('WORLD_DB_NAME', 'acore_world');
   define('SHOP_DB_NAME', 'acore_shop');

   define('SOAP_HOST', '127.0.0.1');
   define('SOAP_PORT', 7878);
   define('SOAP_URI', 'urn:AC');
   define('SOAP_GM_USER', 'TU_USUARIO_GM');
   define('SOAP_GM_PASS', 'TU_CONTRASEÑA_GM');
   ```

3. **Coloca una imagen de fondo** en `img/fondo.jpg` (opcional).

4. **Asegúrate de que los archivos JSON tengan permisos de escritura**:
   - `noticias_es.json`, `noticias_en.json`
   - `reglas_es.json`, `reglas_en.json`

5. **Abre `http://localhost/index.php`** en el navegador.

---

## 📁 Estructura del proyecto

```
yaniel-cms/
├── config.php                 # Configuración global (BD, SOAP, economía)
├── lang.php                   # Cargador de idiomas (ES/EN)
├── lang_es.php                # Traducciones en español
├── lang_en.php                # Traducciones en inglés
├── index.php                  # Página principal (login/registro/dashboard)
├── cuenta.php                 # Panel de usuario
├── tienda.php                 # Tienda con categorías
├── noticias.php               # Listado público de noticias
├── reglas.php                 # Página pública de reglas
├── canjear.php                # Canjear códigos de regalo
├── rifa.php                   # Rifa activa
├── guias.php                  # Guías de profesiones
├── personajes.php             # (Opcional) Listado de personajes
├── cambiar_password.php       # (Opcional) Cambio de contraseña
├── noticias_es.json           # Noticias en español
├── noticias_en.json           # Noticias en inglés
├── reglas_es.json             # Reglas en español
├── reglas_en.json             # Reglas en inglés
├── img/
│   └── fondo.jpg              # Imagen de fondo
├── css/
│   ├── base.css
│   ├── layout.css
│   ├── forms.css
│   ├── noticias.css
│   ├── tienda.css
│   ├── admin.css
│   └── guias.css
├── js/
│   ├── tabs.js
│   └── uptime.js
└── admin/
    ├── index.php              # Panel GM (pestañas)
    ├── items.php              # Gestión de items
    ├── noticias.php           # Gestión de noticias
    ├── usuarios.php           # Gestión de usuarios
    ├── reglas.php             # Gestión de reglas
    ├── codigos.php            # Gestión de códigos
    └── rifas.php              # Gestión de rifas
```

---

## 🔐 Seguridad

- Cambia `DB_PASS`, `SOAP_GM_USER` y `SOAP_GM_PASS` por credenciales seguras.
- Habilita HTTPS en producción.
- No expongas `config.php` públicamente (colócalo fuera del webroot si es posible).
- Revisa los permisos de escritura de los archivos JSON.
- Limita el acceso a `admin/` por IP si es necesario.

---

## 🎮 Uso

### Usuario
- Regístrate o inicia sesión.
- Accede a **Mi Cuenta** para ver personajes, cambiar contraseña, historial de compras, canjear códigos y participar en rifas.
- Compra en la **Tienda** (solo con personajes desconectados).
- Consulta **Noticias**, **Reglas** y **Guías**.

### GM
- Accede a `admin/index.php` (requiere `gmlevel >= 3`).
- Gestiona **items**, **noticias**, **usuarios**, **reglas**, **códigos** y **rifas**.
- Los cambios se guardan en la base de datos o en los archivos JSON correspondientes.

---

## 🧪 Pruebas

1. Regístrate, inicia sesión y compra un item.
2. Canjea un código de regalo.
3. Participa en una rifa.
4. Cambia de idioma con los botones ES/EN y verifica que todo se traduce.
5. Entra al panel GM y gestiona items, noticias, usuarios, reglas, códigos y rifas.

---

## 📝 Notas

- El oro se almacena en **cobre** en la base de datos. `COBRE_POR_ORO = 10000`.
- Las fechas se muestran con `fechaBonita()` en español o inglés según el idioma.
- Los códigos de regalo y las rifas son **desechables** (una vez usados/finalizados, no se pueden reutilizar).
- Los archivos `noticias.json` y `reglas.json` (antiguos) pueden eliminarse; ahora se usan `noticias_es.json`, `noticias_en.json`, `reglas_es.json` y `reglas_en.json`.
- `personajes.php` y `cambiar_password.php` son redundantes si usas `cuenta.php` (que ya incluye esas funciones). Puedes eliminarlos si no los necesitas.

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**. Puedes usarlo, modificarlo y distribuirlo libremente, siempre que se mantenga el aviso de copyright.

---

## 👨‍💻 Autor

**Yaniel**  
Proyecto: **Yaniel CMS**  
Repositorio: [https://github.com/Yaniel-Dj/yaniel-cms](https://github.com/Yaniel-Dj/Yaniel-cms)

---

## 🙏 Agradecimientos

- [AzerothCore](https://www.azerothcore.org/) por el servidor.
- [Font Awesome](https://fontawesome.com/) por los iconos.
- [Wowhead](https://www.wowhead.com/) por los iconos de items.

---

**¡Disfruta de tu servidor!** 🐉⚔️
