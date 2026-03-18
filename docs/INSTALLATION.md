# 🔧 Guide d'Installation - Raqmi Cash Platform

Guide complet pour installer et configurer la plateforme Raqmi Cash en local ou en production.

---

## 📋 Prérequis

### Requirements Système

| Composant | Version Requise | Version Recommandée |
|-----------|-----------------|---------------------|
| PHP | 8.2+ | 8.4+ |
| MySQL | 5.7+ | 8.0+ |
| Node.js | 18.x | 20.x |
| Composer | 2.x | 2.7+ |
| Nginx | 1.20+ | 1.24+ |

### Extensions PHP Requises

```bash
# Extensions obligatoires
php-bcmath
php-ctype
php-curl
php-dom
php-fileinfo
php-json
php-mbstring
php-mysql
php-openssl
php-pdo
php-tokenizer
php-xml
php-zip

# Extensions recommandées
php-redis (pour le cache)
php-gd (pour les images)
php-intl (pour l'internationalisation)
```

**Installation Ubuntu/Debian :**
```bash
sudo apt-get install php8.4-bcmath php8.4-curl php8.4-mbstring php8.4-mysql php8.4-xml php8.4-zip php8.4-gd php8.4-intl
```

---

## 🚀 Installation Locale (Développement)

### Étape 1 : Cloner le Projet

```bash
# Cloner le repository
git clone https://github.com/your-org/raqmicash.com.git
```

### Étape 2 : Installer les Dépendances

```bash
# Installer les dépendances PHP
composer install

# Installer les dépendances Node.js
npm install
```

### Étape 3 : Configuration de l'Environnement

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### Étape 4 : Configuration de la Base de Données

**Option A : SQLite (Recommandé pour le développement)**

```bash
# Créer la base SQLite
touch database/database.sqlite

# Mettre à jour .env
echo "DB_CONNECTION=sqlite" >> .env
echo "DB_DATABASE=database/database.sqlite" >> .env

# Lancer les migrations
php artisan migrate:fresh --seed
```

**Option B : MySQL/MariaDB**

```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE jebab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'jebab_user'@'localhost' IDENTIFIED BY 'votre_password';
GRANT ALL PRIVILEGES ON jebab.* TO 'jebab_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Mettre à jour .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jebab
DB_USERNAME=jebab_user
DB_PASSWORD=votre_password

# Lancer les migrations
php artisan migrate:fresh --seed
```

### Étape 5 : Storage & Permissions

```bash
# Créer les liens symboliques
php artisan storage:link

# Définir les permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/ bootstrap/cache/
```

### Étape 6 : Build des Assets

```bash
# Build pour développement
npm run dev

# Ou build pour production
npm run build
```

### Étape 7 : Lancer le Serveur

```bash
# Démarrer le serveur de développement
php artisan serve

# Accéder à l'application
http://127.0.0.1:8000
```

---

## 🏢 Installation Production

### Étape 1 : Préparation du Serveur

```bash
# Mettre à jour le système
sudo apt-get update && sudo apt-get upgrade -y

# Installer les dépendances
sudo apt-get install -y nginx mysql-server php8.4-fpm php8.4-mysql \
    php8.4-bcmath php8.4-curl php8.4-mbstring php8.4-xml php8.4-zip \
    php8.4-gd php8.4-intl unzip git composer nodejs npm
```

### Étape 2 : Cloner et Configurer

```bash
# Cloner dans le répertoire web
cd /var/www
git clone https://github.com/your-org/raqmicash.com.git

# Installer les dépendances
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Étape 3 : Configuration Nginx

**Créer le fichier de configuration :**
```bash
sudo nano /etc/nginx/sites-available/raqmicash.com
```

**Configuration Nginx :**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name raqmicash.com www.raqmicash.com;
    root /var/www/raqmicash.com/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Activer le site :**
```bash
sudo ln -s /etc/nginx/sites-available/raqmicash.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Étape 4 : SSL avec Certbot

```bash
# Installer Certbot
sudo apt-get install -y certbot python3-certbot-nginx

# Obtenir le certificat
sudo certbot --nginx -d raqmicash.com -d www.raqmicash.com

# Vérifier le renouvellement automatique
sudo certbot renew --dry-run
```

### Étape 5 : Optimisations Production

```bash
# Cache de configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
chown -R www-data:www-data /var/www/raqmicash.com
chmod -R 775 /var/www/raqmicash.com/storage
chmod -R 775 /var/www/raqmicash.com/bootstrap/cache
```

### Étape 6 : Configuration Supervisor (Queue)

**Créer le fichier Supervisor :**
```bash
sudo nano /etc/supervisor/conf.d/jebab-worker.conf
```

**Configuration :**
```ini
[program:jebab-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/raqmicash.com/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasuser=false
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/raqmicash.com/storage/logs/worker.log
stopwaitsecs=3600
```

**Démarrer le worker :**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start jebab-worker:*
```

### Étape 7 : Configuration Cron

```bash
# Éditer le crontab
crontab -e

# Ajouter la ligne suivante
* * * * * cd /var/www/raqmicash.com && php artisan schedule:run >> /dev/null 2>&1
```

---

## 👤 Comptes par Défaut

Après l'installation avec `--seed`, ces comptes sont créés :

### Administrateur
```
Email: admin@raqmicash.ma
Mot de passe: password
Rôle: admin
```

### Point de Vente (Outlet)
```
Email: pos@raqmicash.ma
Mot de passe: password
Rôle: outlet
Code POS: OT1002
```

---

## 🐛 Dépannage

### Problème : Permissions incorrectes

```bash
# Réinitialiser les permissions
sudo chown -R www-data:www-data /var/www/raqmicash.com
sudo chmod -R 775 /var/www/raqmicash.com/storage
sudo chmod -R 775 /var/www/raqmicash.com/bootstrap/cache
```

### Problème : Erreur 500

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Problème : Base de données

```bash
# Re-migrer
php artisan migrate:fresh --seed

# Vérifier la connexion
php artisan db:show
```

### Problème : Assets non chargés

```bash
# Rebuild les assets
npm install
npm run build
```

---

## ✅ Vérification de l'Installation

```bash
# Vérifier la configuration
php artisan about

# Vérifier les routes
php artisan route:list

# Vérifier la base de données
php artisan db:show

# Test de santé
curl http://localhost:8000/api/health
```

---

## 📞 Support

En cas de problème lors de l'installation :

- **Documentation**: Consultez les autres fichiers dans `docs/`
- **Issues**: Ouvrez une issue sur GitHub
- **Email**: salim.moustanir@gmail.com

---

**Dernière mise à jour :** Mars 2026  
**Version :** 1.0
