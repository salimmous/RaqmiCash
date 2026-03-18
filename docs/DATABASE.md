# 🗄️ Database Documentation - Raqmi Cash Platform

Documentation complète du schéma de base de données.

---

## 📊 Vue d'ensemble

- **SGBD:** MySQL 8.0 / SQLite
- **Encodage:** utf8mb4_unicode_ci
- **Moteur:** InnoDB
- **Clés étrangères:** Activées

---

## 📋 Tables Principales

### `users`

Table principale des utilisateurs.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'outlet', 'customer') DEFAULT 'customer',
    phone VARCHAR(50),
    poscode VARCHAR(50) UNIQUE, -- Code point de vente (ex: OT1001)
    balance DECIMAL(10, 2) DEFAULT 0.00,
    bonus_balance DECIMAL(10, 2) DEFAULT 0.00,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role (role),
    INDEX idx_poscode (poscode),
    INDEX idx_email (email)
);
```

### `outlets`

Informations spécifiques aux points de vente.

```sql
CREATE TABLE outlets (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    poscode VARCHAR(50) UNIQUE NOT NULL,
    commission_rate DECIMAL(5, 2) DEFAULT 7.00, -- Pourcentage
    balance DECIMAL(10, 2) DEFAULT 0.00,
    bonus_balance DECIMAL(10, 2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_poscode (poscode),
    INDEX idx_active (is_active)
);
```

### `recharges`

Historique des recharges de crédit.

```sql
CREATE TABLE recharges (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    operator VARCHAR(50) NOT NULL, -- IAM, Orange, Inwi
    phone VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    card_type VARCHAR(50) DEFAULT 'normal', -- normal, special
    commission DECIMAL(10, 2) DEFAULT 0.00,
    bonus DECIMAL(10, 2) DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL, -- Montant débité
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_number VARCHAR(50) UNIQUE,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_operator (operator),
    INDEX idx_transaction (transaction_number)
);
```

### `subscriptions`

Gestion des abonnements (mobile, ADSL, Fiber).

```sql
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(50) NOT NULL, -- IAM, Orange
    type VARCHAR(50) NOT NULL, -- mobile, adsl, fiber
    phone VARCHAR(50),
    offer_name VARCHAR(255),
    customer_name VARCHAR(255) NOT NULL,
    customer_cin VARCHAR(50) NOT NULL,
    cin_image_path VARCHAR(500),
    service_fee DECIMAL(10, 2) DEFAULT 0.00,
    commission DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    approved_by BIGINT UNSIGNED NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_provider (provider),
    INDEX idx_type (type)
);
```

### `products`

Catalogue produits.

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    purchase_price DECIMAL(10, 2) NOT NULL,
    selling_price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 5, -- Seuil d'alerte
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_active (is_active),
    INDEX idx_stock (stock)
);
```

### `categories`

Catégories de produits.

```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    margin_rate DECIMAL(5, 2) DEFAULT 20.00, -- Marge par défaut
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### `transactions_pos`

Journal centralisé de toutes les transactions.

```sql
CREATE TABLE transactions_pos (
    idx BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    TransactionNumber VARCHAR(50) UNIQUE NOT NULL, -- ex: RE260307134501234
    poscode VARCHAR(50) NOT NULL,
    UserName VARCHAR(255) NOT NULL,
    OperationType VARCHAR(50) NOT NULL, -- recharge, subscription, transfer...
    Amount DECIMAL(10, 2) NOT NULL,
    Commission DECIMAL(10, 2) DEFAULT 0.00,
    Bonus DECIMAL(10, 2) DEFAULT 0.00,
    State ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    OperatorMessage TEXT,
    AddedOn DATE NOT NULL,
    AddedAt TIME NOT NULL,
    ClosedOn DATE NULL,
    ClosedAt TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_poscode (poscode),
    INDEX idx_transaction (TransactionNumber),
    INDEX idx_state (State),
    INDEX idx_date (AddedOn)
);
```

### `transfers`

Transferts de solde entre outlets.

```sql
CREATE TABLE transfers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    fee DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    transaction_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_recipient (recipient_id),
    INDEX idx_transaction (transaction_number)
);
```

### `offers`

Offres promotionnelles.

```sql
CREATE TABLE offers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL, -- recharge, subscription, product
    discount_rate DECIMAL(5, 2) DEFAULT 0.00,
    discount_fixed DECIMAL(10, 2) DEFAULT 0.00,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date)
);
```

### `geoip_logs`

Journal des accès géographiques.

```sql
CREATE TABLE geoip_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    ip VARCHAR(45) NOT NULL,
    country_code VARCHAR(2),
    fallback_country_code VARCHAR(2),
    path VARCHAR(500) NOT NULL,
    url VARCHAR(2000) NOT NULL,
    user_agent TEXT,
    source VARCHAR(50), -- cloudflare, ip-api, ipapi.co
    access_denied BOOLEAN DEFAULT FALSE,
    extra_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ip (ip),
    INDEX idx_country (country_code),
    INDEX idx_denied (access_denied),
    INDEX idx_date (created_at)
);
```

### `activity_logs`

Journal d'activité des utilisateurs.

```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    properties JSON,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_severity (severity)
);
```

---

## 🔗 Relations

```
users (1) ──→ (N) outlets
users (1) ──→ (N) recharges
users (1) ──→ (N) subscriptions
users (1) ──→ (N) transfers (sender)
users (1) ──→ (N) transfers (recipient)
users (1) ──→ (N) activity_logs

categories (1) ──→ (N) products

products (1) ──→ (N) product_orders
```

---

## 📊 Vues Utiles

### Vue: Solde par Outlet

```sql
CREATE VIEW outlet_balances AS
SELECT 
    o.poscode,
    o.name,
    o.balance,
    o.bonus_balance,
    (o.balance + o.bonus_balance) as total_balance,
    COUNT(r.id) as total_recharges,
    SUM(r.amount) as total_recharge_amount
FROM outlets o
LEFT JOIN recharges r ON o.user_id = r.user_id
GROUP BY o.id;
```

### Vue: Transactions par Jour

```sql
CREATE VIEW daily_transactions AS
SELECT 
    DATE(created_at) as transaction_date,
    COUNT(*) as total_transactions,
    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount
FROM recharges
GROUP BY DATE(created_at);
```

---

## 🔍 Requêtes Courantes

### Top 10 Outlets par Recharges

```sql
SELECT 
    o.poscode,
    o.name,
    COUNT(r.id) as recharge_count,
    SUM(r.amount) as total_amount
FROM outlets o
JOIN recharges r ON o.user_id = r.user_id
WHERE r.status = 'completed'
GROUP BY o.id
ORDER BY total_amount DESC
LIMIT 10;
```

### Revenus par Mois

```sql
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    SUM(commission) as total_commission,
    COUNT(*) as total_recharges
FROM recharges
WHERE status = 'completed'
GROUP BY month
ORDER BY month DESC;
```

### Abonnements en Attente

```sql
SELECT 
    s.*,
    u.name as customer_name,
    u.phone as customer_phone
FROM subscriptions s
JOIN users u ON s.user_id = u.id
WHERE s.status = 'pending'
ORDER BY s.created_at DESC;
```

---

## 🛠️ Migrations

Les migrations Laravel sont situées dans `database/migrations/`.

**Exemple:**
```php
// 2026_01_01_000001_create_users_table.php
public function up()
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->enum('role', ['admin', 'outlet', 'customer'])->default('customer');
        $table->string('poscode')->unique()->nullable();
        $table->decimal('balance', 10, 2)->default(0.00);
        $table->decimal('bonus_balance', 10, 2)->default(0.00);
        $table->timestamps();
    });
}
```

---

## 📈 Performance

### Index Recommandés

| Table | Colonnes | Type |
|-------|----------|------|
| users | email | UNIQUE |
| users | poscode | UNIQUE |
| recharges | user_id, status | COMPOSITE |
| transactions_pos | TransactionNumber | UNIQUE |
| geoip_logs | created_at | BTREE |

### Optimisations

- Activer `query_cache` MySQL
- Utiliser `EXPLAIN` pour analyser les requêtes
- Archiver les anciennes transactions (> 1 an)

---

## 🔒 Sécurité

### Backup Automatique

```bash
# Script de backup
mysqldump -u rwsUserMA -p main_raqmicash | gzip > backup_$(date +%Y%m%d).sql.gz
```

### Chiffrement

Les colonnes sensibles peuvent être chiffrées :

```php
// Dans les modèles Laravel
protected $casts = [
    'phone' => 'encrypted',
    'email' => 'encrypted',
];
```

---

**Dernière mise à jour :** Mars 2026  
**Version :** 1.0
