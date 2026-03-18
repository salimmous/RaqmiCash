# 🏗️ Architecture Technique - Raqmi Cash Platform

Vue d'ensemble complète de l'architecture technique de la plateforme.

---

## 📐 Architecture Globale

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENTS                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │  Web     │  │  Mobile  │  │   API    │  │  Admin   │        │
│  │ Browser  │  │   App    │  │ Clients  │  │  Panel   │        │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘        │
└───────┼─────────────┼─────────────┼─────────────┼───────────────┘
        │             │             │             │
        └─────────────┴──────┬──────┴─────────────┘
                             │
                    ┌────────▼────────┐
                    │   Cloudflare    │
                    │   (CDN + WAF)   │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │   Nginx Server  │
                    │   (Load Balancer)│
                    └────────┬────────┘
                             │
        ┌────────────────────┼────────────────────┐
        │                    │                    │
┌───────▼────────┐  ┌───────▼────────┐  ┌───────▼────────┐
│   Frontend     │  │   Laravel 12   │  │   Queue        │
│   (Site 1)     │  │   Backend      │  │   Workers      │
│   jebab.com    │  │   app.jebab.com│  │   (Redis)      │
└────────────────┘  └───────┬────────┘  └────────────────┘
                            │
                ┌───────────┼───────────┐
                │           │           │
        ┌───────▼───┐  ┌───▼────┐  ┌──▼──────┐
        │  MySQL    │  │ Redis  │  │ Storage │
        │  Database │  │ Cache  │  │  (S3)   │
        └───────────┘  └────────┘  └─────────┘
```

---

## 🗂️ Structure du Projet

```
jebab.com/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Contrôleurs Admin
│   │   │   ├── Api/             # Contrôleurs API REST
│   │   │   └── Frontend/        # Contrôleurs Site Public
│   │   ├── Middleware/
│   │   │   ├── Firewall.php     # Protection contre attaques
│   │   │   ├── AllowMoroccoOnly.php  # Restriction géographique
│   │   │   └── SecurityHeaders.php   # En-têtes de sécurité
│   │   └── Requests/            # Validation des requêtes
│   ├── Models/                  # Modèles Eloquent
│   │   ├── User.php
│   │   ├── Outlet.php
│   │   ├── Recharge.php
│   │   ├── Subscription.php
│   │   ├── Product.php
│   │   ├── Transaction.php
│   │   └── GeoIpLog.php
│   ├── Services/                # Services métier
│   │   ├── TransactionService.php
│   │   ├── PricingService.php
│   │   └── GeoIpService.php
│   └── Providers/               # Providers Laravel
├── database/
│   ├── migrations/              # Migrations DB
│   ├── seeders/                 # Seeders de données
│   └── factories/               # Factories de test
├── resources/
│   ├── views/
│   │   ├── admin/               # Vues Admin
│   │   ├── frontend/            # Vues Site Public
│   │   └── layouts/             # Layouts communs
│   └── lang/
│       ├── fr/                  # Traductions Français
│       └── ar/                  # Traductions Arabe
├── routes/
│   ├── web.php                  # Routes Web
│   ├── api.php                  # Routes API
│   └── admin.php                # Routes Admin
├── public/                      # Point d'entrée
├── storage/                     # Fichiers générés
├── tests/                       # Tests PHPUnit
└── vendor/                      # Dépendances
```

---

## 🗄️ Architecture de la Base de Données

### Tables Principales

```
┌─────────────────────────────────────────────────────────────┐
│                         USERS                                │
├─────────────────────────────────────────────────────────────┤
│ id, name, email, password, role (admin/outlet/customer),    │
│ phone, poscode, balance, bonus_balance, created_at          │
└─────────────────────────────────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
┌──────────────┐   ┌──────────────┐   ┌──────────────┐
│   OUTLETS    │   │  RECHARGES   │   │SUBSCRIPTIONS │
├──────────────┤   ├──────────────┤   ├──────────────┤
│ id, user_id  │   │ id, user_id  │   │ id, user_id  │
│ poscode      │   │ operator     │   │ provider     │
│ commission_% │   │ amount       │   │ type         │
│ balance      │   │ status       │   │ status       │
└──────────────┘   └──────────────┘   └──────────────┘
        │                   │                   │
        └───────────────────┼───────────────────┘
                            │
                            ▼
                  ┌─────────────────┐
                  │  TRANSACTIONS   │
                  ├─────────────────┤
                  │ id, user_id     │
                  │ type, amount    │
                  │ status, ref     │
                  └─────────────────┘
```

### Schéma Relationnel

```sql
users (1) ──→ (N) outlets
users (1) ──→ (N) recharges
users (1) ──→ (N) subscriptions
users (1) ──→ (N) transactions
users (1) ──→ (N) transfers (outgoing)
users (1) ──→ (N) transfers (incoming)

categories (1) ──→ (N) products
products (1) ──→ (N) product_orders
products (1) ──→ (N) stock_movements

offers (1) ──→ (N) offer_users
```

---

## 🔐 Architecture de Sécurité

### Couches de Sécurité

```
┌─────────────────────────────────────────────────────┐
│  Couche 1: Nginx                                    │
│  - Rate Limiting                                    │
│  - SSL/TLS                                          │
│  - Security Headers                                 │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│  Couche 2: Cloudflare (Optionnel)                   │
│  - DDoS Protection                                  │
│  - WAF (Web Application Firewall)                   │
│  - GeoIP Filtering                                  │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│  Couche 3: Laravel Middleware                       │
│  ├── Firewall.php (IP Banning, Honeypots)          │
│  ├── AllowMoroccoOnly.php (GeoIP Restriction)      │
│  ├── SecurityHeaders.php (HSTS, XSS, CSRF)         │
│  └── AuthenticatedSession.php (Session Security)   │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│  Couche 4: Application                              │
│  - Input Validation                                 │
│  - SQL Injection Prevention (Prepared Statements)   │
│  - XSS Prevention (Auto-escaping)                   │
│  - CSRF Protection                                  │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│  Couche 5: Base de Données                          │
│  - Encrypted Connections (SSL)                      │
│  - User Privileges (Least Privilege)                │
│  - Foreign Keys (Data Integrity)                    │
└─────────────────────────────────────────────────────┘
```

---

## 🔄 Flux de Données

### Recharge de Crédit

```
1. Outlet → Formulaire de recharge
2. Frontend → Validation JavaScript
3. API Controller → Validation serveur
4. PricingService → Calcul commission/bonus
5. TransactionService → Création transaction
6. Database → Enregistrement
7. Response → JSON avec statut
8. Frontend → Affichage résultat
```

### Subscription (Abonnement)

```
1. Customer → Soumission demande
2. API Controller → Validation + Upload documents
3. Database → Création subscription (status: pending)
4. TransactionService → Journalisation
5. Admin → Notification
6. Admin → Approve/Reject
7. Database → Update status
8. Customer → Notification résultat
```

### Transfert entre Outlets

```
1. Outlet A → Demande de transfert
2. API Controller → Vérification solde
3. TransactionService → Vérification code Outlet B
4. Database → Débit Outlet A
5. Database → Crédit Outlet B
6. TransactionService → Double journalisation
7. Response → Confirmation
```

---

## ⚡ Architecture de Performance

### Cache Strategy

```
┌─────────────────────────────────────────────────────┐
│                    CACHE LAYERS                      │
├─────────────────────────────────────────────────────┤
│  Niveau 1: Browser Cache                            │
│  - Static assets (CSS, JS, Images)                  │
│  - Duration: 1 week - 1 month                       │
├─────────────────────────────────────────────────────┤
│  Niveau 2: OPcache (PHP Bytecode)                   │
│  - Code PHP compilé                                 │
│  - Duration: Until deployment                       │
├─────────────────────────────────────────────────────┤
│  Niveau 3: Redis Cache                              │
│  - Config cache                                     │
│  - Route cache                                      │
│  - View cache                                       │
│  - Settings cache (10 min)                          │
│  - Session data                                     │
├─────────────────────────────────────────────────────┤
│  Niveau 4: Database Query Cache                     │
│  - MySQL Query Cache                                │
│  - Eloquent Model Cache                             │
└─────────────────────────────────────────────────────┘
```

### Queue System

```
┌─────────────────────────────────────────────────────┐
│                   QUEUE WORKFLOW                     │
├─────────────────────────────────────────────────────┤
│  Job Created → Redis Queue → Worker Processing      │
│                                                      │
│  Jobs traités:                                      │
│  - Email notifications                              │
│  - Telegram alerts                                  │
│  - GeoIP lookups                                    │
│  - Report generation                                │
│  - Backup operations                                │
└─────────────────────────────────────────────────────┘
```

---

## 🌐 Architecture Multi-Sites

### Séparation Frontend/Backend

```
┌─────────────────────────────────────────────────────┐
│                  DOMAIN ROUTING                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│  jebab.com (Site 1 - Frontend)                      │
│  ├── Landing page                                   │
│  ├── Pages marketing                                │
│  ├── Informations publiques                         │
│  └── Liens vers app.jebab.com                       │
│                                                      │
│  app.jebab.com (Site 2 - Backend)                   │
│  ├── Authentication                                 │
│  ├── Dashboard Admin                                │
│  ├── Dashboard Client                               │
│  ├── API Endpoints                                  │
│  └── Toutes les fonctionnalités métier              │
│                                                      │
└─────────────────────────────────────────────────────┘
```

**Implémentation Laravel :**
```php
// routes/web.php
if (request()->getHost() === 'jebab.com') {
    // Routes frontend uniquement
    Route::get('/', [WelcomeController::class, 'index']);
    Route::get('/about', [PageController::class, 'about']);
} else {
    // Routes backend complètes
    Route::get('/login', [AuthController::class, 'login']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // ... toutes les routes métier
}
```

---

## 📊 Monitoring & Logging

### Système de Logs

```
┌─────────────────────────────────────────────────────┐
│                    LOGGING SYSTEM                    │
├─────────────────────────────────────────────────────┤
│  Laravel Log (storage/logs/laravel.log)             │
│  ├── Application errors                             │
│  ├── Debug information                              │
│  └── Custom logs                                    │
├─────────────────────────────────────────────────────┤
│  Activity Logs (Database: activity_logs)            │
│  ├── User actions                                   │
│  ├── Admin operations                               │
│  └── Sensitive data access                          │
├─────────────────────────────────────────────────────┤
│  GeoIP Logs (Database: geoip_logs)                  │
│  ├── Access attempts                                │
│  ├── Country detection                              │
│  └── Denied accesses                                │
├─────────────────────────────────────────────────────┤
│  Transaction Logs (Database: transactions_pos)      │
│  ├── All financial operations                       │
│  ├── Status tracking                                │
│  └── Audit trail                                    │
└─────────────────────────────────────────────────────┘
```

---

## 🔧 Technologies Utilisées

### Backend Stack
- **Framework:** Laravel 12
- **PHP:** 8.4+
- **Database:** MySQL 8.0 / SQLite
- **Cache:** Redis
- **Queue:** Database/Redis
- **Authentication:** Laravel Sanctum

### Frontend Stack
- **CSS:** Modern CSS3 (Gradients, Animations)
- **JavaScript:** Vanilla JS + Alpine.js
- **Icons:** Font Awesome
- **Fonts:** Google Fonts (Cairo)
- **Design:** RTL Support, Responsive

### DevOps Stack
- **Web Server:** Nginx
- **SSL:** Let's Encrypt (Certbot)
- **CDN:** Cloudflare (Optionnel)
- **Monitoring:** Laravel Logs + Custom Dashboards
- **Backup:** Automated scripts + Telegram notifications

---

## 📈 Scalabilité

### Horizontal Scaling

```
┌─────────────────────────────────────────────────────┐
│              LOAD BALANCED ARCHITECTURE              │
├─────────────────────────────────────────────────────┤
│                                                      │
│         ┌──────────────┐                            │
│         │  Load Balancer│                            │
│         │  (Nginx/HA)   │                            │
│         └──────┬───────┘                            │
│                │                                     │
│    ┌───────────┼───────────┐                        │
│    │           │           │                        │
│ ┌──▼──┐    ┌──▼──┐    ┌──▼──┐                      │
│ │App 1│    │App 2│    │App 3│                      │
│ └──┬──┘    └──┬──┘    └──┬──┘                      │
│    │           │           │                        │
│    └───────────┼───────────┘                        │
│                │                                     │
│         ┌──────▼───────┐                            │
│         │ Shared DB     │                            │
│         │ (Master/Slave)│                            │
│         └──────────────┘                            │
│                                                      │
└─────────────────────────────────────────────────────┘
```

### Vertical Scaling

- Augmenter RAM PHP-FPM workers
- Optimiser MySQL buffer pool
- Utiliser SSD pour storage
- Activer OPcache

---

**Dernière mise à jour :** Mars 2026  
**Version :** 1.0
