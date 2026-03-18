# 🚀 Kamal Deployment Guide - Raqmi Cash Platform

Guide complet pour déployer la plateforme Raqmi Cash avec **Kamal** - l'outil de déploiement moderne par 37signals.

---

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Installation de Kamal](#installation-de-kamal)
3. [Configuration](#configuration)
4. [Déploiement](#déploiement)
5. [Commandes Utiles](#commandes-utiles)
6. [Rollback](#rollback)
7. [Monitoring](#monitoring)
8. [Troubleshooting](#troubleshooting)

---

## 📦 Prérequis

### Côté Local (Machine de Déploiement)

| Composant | Version Requise |
|-----------|-----------------|
| Ruby | 3.0+ |
| Docker | 20.10+ |
| Kamal | 1.0+ |
| SSH Key | Configuré |

### Côté Serveur (Production)

| Composant | Version Requise |
|-----------|-----------------|
| Ubuntu/Debian | 20.04+ |
| Docker | 20.10+ |
| Docker Compose | 2.0+ |
| MySQL | 8.0+ |
| Nginx | 1.20+ |

---

## 🔧 Installation de Kamal

### Étape 1 : Installer Ruby (si nécessaire)

```bash
# Ubuntu/Debian
sudo apt-get install -y ruby-full

# macOS
brew install ruby
```

### Étape 2 : Installer Kamal

```bash
# Installer Kamal via gem
gem install kamal

# Vérifier l'installation
kamal version
```

### Étape 3 : Configurer SSH

```bash
# Générer une clé SSH (si ce n'est pas déjà fait)
ssh-keygen -t ed25519 -C "kamal@jebab.com"

# Copier la clé vers le serveur
ssh-copy-ituser@votre-serveur.com
```

---

## ⚙️ Configuration

### Structure des Fichiers

```
jebab.com/
├── .kamal/
│   ├── deploy.yml          # Configuration principale
│   ├── .env                # Variables d'environnement (gitignored)
│   └── hooks/
│       ├── pre-build       # Hook avant build
│       ├── pre-deploy      # Hook avant déploiement
│       └── post-deploy     # Hook après déploiement
├── Dockerfile              # Dockerfile de l'application
└── docs/
    └── 02-KAMAL-DEPLOYMENT-GUIDE.md # Ce fichier
```

### Fichier `.kamal/deploy.yml`

```yaml
# Nom de l'application
service: jebab

# Image Docker
image: your-dockerhub-username/jebab

# Serveurs
servers:
  web:
    - 192.168.1.100  # IP de votre serveur
  workers:
    - 192.168.1.100

# Configuration globale
registry:
  username: your-dockerhub-username
  password:
    - KAMAL_REGISTRY_PASSWORD

# Variables d'environnement
env:
  clear:
    APP_ENV: production
    APP_DEBUG: false
    APP_URL: https://jebab.com
    LOG_CHANNEL: stderr
    QUEUE_CONNECTION: database
    CACHE_STORE: database
    SESSION_DRIVER: database
  secret:
    - APP_KEY
    - DB_CONNECTION
    - DB_HOST
    - DB_PORT
    - DB_DATABASE
    - DB_USERNAME
    - DB_PASSWORD
    - MAIL_MAILER
    - MAIL_HOST
    - MAIL_PORT
    - MAIL_USERNAME
    - MAIL_PASSWORD

# Configuration SSH
ssh:
  user: deploy
  port: 22

# Configuration des rôles
roles:
  web:
    cmd: php artisan serve --host=0.0.0.0 --port=8080
    options:
      memory: 512m
      cpus: "0.5"
    labels:
      traefik.enable: "true"
      traefik.http.routers.jebab.rule: "Host(`jebab.com`) || Host(`www.jebab.com`)"
      traefik.http.routers.jebab.entrypoints: "websecure"
      traefik.http.routers.jebab.tls.certresolver: "letsencrypt"
    healthcheck:
      path: /api/health
      interval: 10s
      timeout: 5s
      retries: 3

  workers:
    cmd: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    replicas: 2
    options:
      memory: 256m
      cpus: "0.25"

# Configuration Traefik (reverse proxy)
traefik:
  image: traefik:v2.10
  options:
    publish:
      - "80:80"
      - "443:443"
    volume:
      - /var/run/docker.sock:/var/run/docker.sock
      - /opt/traefik/acme.json:/acme.json
  env:
    clear:
      TRAEFIK_DASHBOARD_ENABLE: "true"
      TRAEFIK_PROVIDERS_DOCKER_EXPOSEDBYDEFAULT: "false"
      TRAEFIK_ENTRYPOINTS_WEB_ADDRESS: ":80"
      TRAEFIK_ENTRYPOINTS_WEB_HTTP_REDIRECTIONSENTRYPOINT: "websecure"
      TRAEFIK_ENTRYPOINTS_WEBSECURE_ADDRESS: ":443"
      TRAEFIK_CERTIFICATESRESOLVERS_LETSENCRYPT_ACME_EMAIL: "admin@jebab.com"
      TRAEFIK_CERTIFICATESRESOLVERS_LETSENCRYPT_ACME_STORAGE: "/acme.json"
      TRAEFIK_CERTIFICATESRESOLVERS_LETSENCRYPT_ACME_HTTPCHALLENGE_ENTRYPOINT: "web"

# Hooks personnalisés
hooks:
  pre-build:
    - npm install && npm run build
  post-deploy:
    - php artisan migrate --force
    - php artisan config:cache
    - php artisan route:cache
    - php artisan view:cache
```

### Fichier `.kamal/.env` (à ne PAS committer)

```bash
# Docker Registry
KAMAL_REGISTRY_PASSWORD=votre-token-dockerhub

# Application
APP_KEY=base64:votre-cle-secrete-générée-avec-php-artisan-key-generate

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jebab_production
DB_USERNAME=jebab_user
DB_PASSWORD=votre-mot-de-passe-mysql

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password

# SSL/Tokens
TELEGRAM_BOT_TOKEN=votre-token-telegram
TELEGRAM_CHAT_ID=votre-chat-id
```

### Dockerfile (à la racine)

```dockerfile
FROM php:8.4-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du projet
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Installer les dépendances Node et build
RUN npm install && npm run build

# Créer les répertoires nécessaires
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
RUN chown -R www-data:www-data storage bootstrap/cache

# Copier la configuration Nginx
COPY nginx-docker.conf /etc/nginx/sites-available/default

# Copier la configuration Supervisor
COPY supervisor-worker.conf /etc/supervisor/conf.d/worker.conf

# Exposer les ports
EXPOSE 8080

# Script de démarrage
CMD ["sh", "-c", "service nginx start && service supervisor start && php artisan serve --host=0.0.0.0 --port=8080"]
```

### nginx-docker.conf

```nginx
server {
    listen 8080;
    server_name _;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### supervisor-worker.conf

```ini
[program:jebab-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasuser=false
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 🚀 Déploiement

### Étape 1 : Initialiser Kamal

```bash
# Se connecter au registry Docker
kamal registry login

# Vérifier la configuration
kamal config
```

### Étape 2 : Premier Déploiement

```bash
# Déployer l'application
kamal deploy

# Déployer avec un message de commit
kamal deploy -m "Fix: correction bug pagination"
```

### Étape 3 : Vérifier le Déploiement

```bash
# Voir l'état des conteneurs
kamal app status

# Voir les logs
kamal app logs

# Vérifier la santé
kamal app healthcheck
```

---

## 📝 Commandes Utiles

### Gestion de l'Application

```bash
# Déployer
kamal deploy

# Redémarrer
kamal app reboot

# Arrêter
kamal app stop

# Démarrer
kamal app start

# Voir les logs
kamal app logs
kamal app logs -f  # Follow

# Exécuter une commande
kamal app exec "php artisan migrate --status"
kamal app exec "php artisan about"
kamal app exec -r workers "php artisan queue:restart"
```

### Gestion des Workers

```bash
# Redémarrer les workers
kamal app reboot -r workers

# Voir les logs des workers
kamal app logs -r workers

# Exécuter une commande sur les workers
kamal app exec -r workers "ps aux"
```

### Gestion du Registry

```bash
# Se connecter
kamal registry login

# Push l'image
kamal push

# Build l'image
kamal build
```

### Rollback

```bash
# Voir l'historique des versions
kamal app history

# Revenir à la version précédente
kamal rollback

# Revenir à une version spécifique
kamal rollback <version>
```

### Monitoring

```bash
# Voir l'utilisation des ressources
kamal app top

# Voir les détails d'un conteneur
kamal app details

# Ping pour vérifier la disponibilité
kamal app ping
```

### SSH & Debug

```bash
# SSH dans un conteneur
kamal app shell

# SSH dans un rôle spécifique
kamal app shell -r workers

# SSH sur le serveur
kamal server shell
```

---

## 🔄 Rollback

### En Cas de Problème

```bash
# Rollback vers la version précédente
kamal rollback

# Spécifier une version
kamal rollback abc1234

# Vérifier l'état après rollback
kamal app status
```

### Rollback Manuel

```bash
# Lister les images disponibles
docker images

# Revenir à une image spécifique
kamal deploy --version <tag>
```

---

## 📊 Monitoring

### Logs en Temps Réel

```bash
# Logs de l'application
kamal app logs -f

# Logs des workers
kamal app logs -r workers -f

# Logs Traefik
kamal app logs -r traefik -f
```

### Santé de l'Application

```bash
# Healthcheck
kamal app healthcheck

# Ping
kamal app ping

# Détails
kamal app details
```

### Performance

```bash
# Utilisation CPU/Mémoire
kamal app top

# Statistiques
kamal app stats
```

---

## 🐛 Troubleshooting

### Problème : Échec du Build

```bash
# Nettoyer le cache Docker
kamal builder clean

# Rebuild
kamal build --no-cache
```

### Problème : Container ne démarre pas

```bash
# Voir les logs détaillés
kamal app logs --tail=100

# Vérifier la configuration
kamal config

# SSH dans le container pour debug
kamal app shell
```

### Problème : Base de Données

```bash
# Exécuter les migrations
kamal app exec "php artisan migrate --force"

# Vérifier la connexion
kamal app exec "php artisan db:show"

# Seeder
kamal app exec "php artisan db:seed"
```

### Problème : Permissions

```bash
# Fixer les permissions
kamal app exec "chown -R www-data:www-data storage bootstrap/cache"
kamal app exec "chmod -R 775 storage bootstrap/cache"
```

### Problème : Cache

```bash
# Vider le cache
kamal app exec "php artisan cache:clear"
kamal app exec "php artisan config:clear"
kamal app exec "php artisan route:clear"
kamal app exec "php artisan view:clear"

# Recréer le cache
kamal app exec "php artisan config:cache"
kamal app exec "php artisan route:cache"
kamal app exec "php artisan view:cache"
```

---

## 🔐 Sécurité

### Best Practices

1. **Variables Sensibles** : Toujours utiliser `.kamal/.env` pour les secrets
2. **SSH Keys** : Utiliser des clés SSH fortes (ed25519)
3. **Firewall** : Configurer UFW sur le serveur
4. **SSL** : Toujours utiliser HTTPS en production
5. **Updates** : Maintenir Docker et Kamal à jour

### Fichiers à Gitignore

```gitignore
# Kamal
.kamal/.env
.kamal/secrets

# Docker
.docker/

# Logs
storage/logs/*.log
```

---

## 📚 Ressources

- [Documentation Officielle Kamal](https://kamal-deploy.org/)
- [GitHub Kamal](https://github.com/basecamp/kamal)
- [Docker Documentation](https://docs.docker.com/)
- [Laravel Deployment](https://laravel.com/docs/deployment)

---

## 🆘 Support

En cas de problème :

- **Logs** : `kamal app logs -f`
- **Status** : `kamal app status`
- **Config** : `kamal config`
- **Help** : `kamal help`

---

**Dernière mise à jour :** Mars 2026  
**Version :** 1.0  
**Auteur :** Raqmi Cash Team

---

<div align="center">

**Déployé avec ❤️ au Maroc 🇲🇦**

*Kamal - Simple, Fast, Reliable Deployment*

</div>
