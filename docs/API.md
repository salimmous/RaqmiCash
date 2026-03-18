# 📡 API Documentation - Raqmi Cash Platform

Documentation complète de l'API REST pour les développeurs.

---

## 🚀 Vue d'ensemble

- **Base URL:** `https://app.your-domain.com/api`
- **Format:** JSON
- **Authentification:** Bearer Token (Laravel Sanctum)
- **Version:** v1

---

## 🔐 Authentification

### Obtenir un Token

```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Réponse:**
```json
{
  "success": true,
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "outlet"
  }
}
```

### Utiliser le Token

Inclure le token dans tous les headers des requêtes suivantes :

```http
Authorization: Bearer 1|abc123def456...
```

### Logout

```http
POST /api/logout
Authorization: Bearer {token}
```

---

## 🏪 Points de Vente (Outlets)

### Liste des Outlets (Admin)

```http
GET /api/outlets
Authorization: Bearer {token}
```

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Outlet Center",
      "poscode": "OT1001",
      "balance": 5000.00,
      "bonus_balance": 350.00,
      "commission_rate": 7.0
    }
  ]
}
```

### Mon Outlet

```http
GET /api/my-outlet
Authorization: Bearer {token}
```

### Détails d'un Outlet

```http
GET /api/outlets/{id}
Authorization: Bearer {token}
```

### Mettre à jour un Outlet

```http
PUT /api/outlets/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Nouveau Nom",
  "commission_rate": 8.0
}
```

---

## 📱 Recharges

### Liste des Recharges

```http
GET /api/recharges
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): pending, completed, failed
- `operator` (optional): IAM, Orange, Inwi
- `page` (optional): Numéro de page

### Créer une Recharge

```http
POST /api/recharges
Authorization: Bearer {token}
Content-Type: application/json

{
  "operator": "IAM",
  "phone": "0612345678",
  "amount": 100,
  "card_type": "normal"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Recharge créée avec succès",
  "data": {
    "id": 123,
    "transaction_number": "RE260307134501234",
    "operator": "IAM",
    "phone": "0612345678",
    "amount": 100.00,
    "commission": 7.00,
    "total": 93.00,
    "status": "pending"
  }
}
```

### Détails d'une Recharge

```http
GET /api/recharges/{id}
Authorization: Bearer {token}
```

### Traiter une Recharge (Admin)

```http
POST /api/recharges/{id}/process
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "completed"
}
```

### Supprimer une Recharge

```http
DELETE /api/recharges/{id}
Authorization: Bearer {token}
```

---

## 🌐 Abonnements (Subscriptions)

### Liste des Abonnements

```http
GET /api/subscriptions
Authorization: Bearer {token}
```

### Créer un Abonnement

```http
POST /api/subscriptions
Authorization: Bearer {token}
Content-Type: multipart/form-data

provider: IAM
type: mobile
phone: 0612345678
offer_name: Red 150
customer_name: Ahmed Mohamed
customer_cin: AB123456
cin_image: [file]
```

**Réponse:**
```json
{
  "success": true,
  "message": "Demande d'abonnement soumise",
  "data": {
    "id": 456,
    "transaction_number": "SB260307134601234",
    "provider": "IAM",
    "type": "mobile",
    "status": "pending",
    "service_fee": 50.00
  }
}
```

### Approuver un Abonnement (Admin)

```http
POST /api/subscriptions/{id}/approve
Authorization: Bearer {token}
```

### Rejeter un Abonnement (Admin)

```http
POST /api/subscriptions/{id}/reject
Authorization: Bearer {token}
Content-Type: application/json

{
  "rejection_reason": "CIN illisible"
}
```

---

## 💸 Transferts

### Liste des Transferts

```http
GET /api/transfers
Authorization: Bearer {token}
```

### Créer un Transfert

```http
POST /api/transfers
Authorization: Bearer {token}
Content-Type: application/json

{
  "recipient_poscode": "OT1002",
  "amount": 500.00
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Transfert effectué avec succès",
  "data": {
    "id": 789,
    "transaction_number": "TR260307134701234",
    "from_outlet": "OT1001",
    "to_outlet": "OT1002",
    "amount": 500.00,
    "fee": 0.00,
    "status": "completed"
  }
}
```

### Vérifier un Code Outlet

```http
POST /api/transfers/verify-outlet
Authorization: Bearer {token}
Content-Type: application/json

{
  "poscode": "OT1002"
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "poscode": "OT1002",
    "outlet_name": "Outlet Center",
    "is_active": true
  }
}
```

---

## 🛍️ Produits

### Liste des Produits

```http
GET /api/products
Authorization: Bearer {token}
```

**Query Parameters:**
- `category_id` (optional): Filtrer par catégorie
- `search` (optional): Recherche textuelle
- `page` (optional): Numéro de page

### Détails d'un Produit

```http
GET /api/products/{id}
Authorization: Bearer {token}
```

### Créer un Produit (Admin)

```http
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Smartphone XYZ",
  "category_id": 1,
  "purchase_price": 2000.00,
  "selling_price": 2400.00,
  "stock": 10,
  "description": "Description du produit"
}
```

### Mettre à jour un Produit (Admin)

```http
PUT /api/products/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Nouveau Nom",
  "selling_price": 2300.00
}
```

### Supprimer un Produit (Admin)

```http
DELETE /api/products/{id}
Authorization: Bearer {token}
```

---

## 🎁 Offres (Offers)

### Liste des Offres

```http
GET /api/offers
Authorization: Bearer {token}
```

### Offres Actives

```http
GET /api/offers/active
Authorization: Bearer {token}
```

### Créer une Offre (Admin)

```http
POST /api/offers
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Promotion Ramadan",
  "description": "Offre spéciale",
  "type": "recharge",
  "discount_rate": 10.0,
  "start_date": "2026-03-01",
  "end_date": "2026-03-31",
  "is_active": true
}
```

---

## 📊 Dashboard & Stats

### Statistiques Générales

```http
GET /api/dashboard/stats
Authorization: Bearer {token}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "total_recharges": 1250,
    "total_subscriptions": 89,
    "total_transfers": 45,
    "total_revenue": 125000.00,
    "today_recharges": 25,
    "pending_subscriptions": 5
  }
}
```

### Statistiques par Période

```http
GET /api/dashboard/stats?period=7
Authorization: Bearer {token}
```

**Query Parameters:**
- `period`: Nombre de jours (7, 30, 90)

---

## 💳 Solde & Bonus

### Mon Solde

```http
GET /api/balance
Authorization: Bearer {token}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "balance": 5000.00,
    "bonus_balance": 350.00,
    "currency": "MAD"
  }
}
```

### Historique des Transactions

```http
GET /api/transactions
Authorization: Bearer {token}
```

**Query Parameters:**
- `type` (optional): recharge, subscription, transfer, alimentation
- `status` (optional): pending, success, failed
- `start_date` (optional): YYYY-MM-DD
- `end_date` (optional): YYYY-MM-DD

---

## 🔧 Codes d'Erreur

### Format des Erreurs

```json
{
  "success": false,
  "message": "Erreur description",
  "errors": {
    "field": ["Erreur spécifique"]
  }
}
```

### Codes HTTP

| Code | Signification |
|------|---------------|
| 200 | Succès |
| 201 | Créé avec succès |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Accès refusé |
| 404 | Non trouvé |
| 422 | Erreur de validation |
| 429 | Trop de requêtes |
| 500 | Erreur serveur |

### Messages d'Erreur Courants

```json
// Token invalide
{
  "success": false,
  "message": "Unauthenticated."
}

// Solde insuffisant
{
  "success": false,
  "message": "Solde insuffisant pour cette opération"
}

// Outlet inexistant
{
  "success": false,
  "message": "Code outlet invalide"
}

// Rate limit
{
  "success": false,
  "message": "Too Many Attempts."
}
```

---

## 📝 Exemples de Code

### JavaScript (Fetch)

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('https://app.your-domain.com/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });
  
  const data = await response.json();
  return data.token;
};

// Créer une recharge
const createRecharge = async (token, operator, phone, amount) => {
  const response = await fetch('https://app.your-domain.com/api/recharges', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({ operator, phone, amount }),
  });
  
  return await response.json();
};
```

### PHP (cURL)

```php
// Login
$ch = curl_init('https://app.your-domain.com/api/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'user@example.com',
    'password' => 'password'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$data = json_decode($response, true);
$token = $data['token'];

// Créer une recharge
$ch = curl_init('https://app.your-domain.com/api/recharges');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'operator' => 'IAM',
    'phone' => '0612345678',
    'amount' => 100
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$response = curl_exec($ch);
$result = json_decode($response, true);
```

### Python (Requests)

```python
import requests

# Login
response = requests.post('https://app.your-domain.com/api/login', json={
    'email': 'user@example.com',
    'password': 'password'
})
token = response.json()['token']

# Créer une recharge
headers = {'Authorization': f'Bearer {token}'}
response = requests.post(
    'https://app.your-domain.com/api/recharges',
    json={'operator': 'IAM', 'phone': '0612345678', 'amount': 100},
    headers=headers
)
result = response.json()
```

---

## 🔒 Rate Limiting

| Endpoint | Limite |
|----------|--------|
| /api/login | 5 requêtes/minute |
| /api/register | 3 requêtes/minute |
| Autres endpoints API | 60 requêtes/minute |

---

## 📞 Support API

- **Documentation:** docs/API.md
- **Email:** api@your-domain.com
- **Status:** https://status.your-domain.com

---

**Dernière mise à jour :** Mars 2026  
**Version API:** v1
