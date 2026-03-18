# 💰 Add Balance to All Users

Commande artisan pour ajouter du solde à tous les utilisateurs/points de vente.

---

## 📖 Description

Cette commande permet d'ajouter un montant de solde à **tous les outlets** enregistrés dans la base de données. Elle met à jour la table `outlet_balances` et crée des enregistrements pour les outlets qui n'en ont pas.

---

## 🚀 Utilisation

### Commande de base

```bash
php artisan users:add-balance [montant]
```

### Exemples

```bash
# Ajouter 100 DH à tous les outlets
php artisan users:add-balance 100

# Ajouter 500 DH à tous les outlets
php artisan users:add-balance 500

# Ajouter 1000 DH à tous les outlets
php artisan users:add-balance 1000
```

---

## 📊 Options

| Option | Description |
|--------|-------------|
| `amount` | Le montant à ajouter (obligatoire) |
| `--outlet` | Mettre à jour uniquement les outlets (par défaut) |

---

## 🔍 Comment ça marche

1. **Recherche tous les outlets** dans la base de données
2. **Pour chaque outlet:**
   - Si un enregistrement `outlet_balance` existe → **Met à jour** le solde
   - Si aucun enregistrement n'existe → **Crée** un nouvel enregistrement
3. **Affiche un résumé** des opérations

---

## 📋 Sortie Exemple

```
🚀 Adding Balance to All Users/Outlets
======================================

💰 Amount to add: 100 DH

📊 Found 25 outlet(s)

[============================================] 25/25

======================================
✅ Summary:
┌─────────────────┬───────┐
│ Metric          │ Count │
├─────────────────┼───────┤
│ Outlets Updated │ 20    │
│ Outlets Created │ 5     │
│ Total Added     │ 2500  │
│ Total Outlets   │ 25    │
└─────────────────┴───────┘

🎉 Done!
```

---

## 🗄️ Tables Impactées

### `outlet_balances`
- `balance` - Mis à jour avec le nouveau solde
- `bonus` - Non modifié (reste à 0 pour les nouveaux)

### Structure de `outlet_balances`:
```sql
CREATE TABLE outlet_balances (
    id BIGINT UNSIGNED PRIMARY KEY,
    outlet_id BIGINT UNSIGNED,
    pos_code VARCHAR(50),
    full_name VARCHAR(255),
    balance DECIMAL(10,2),
    bonus DECIMAL(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## ⚠️ Précautions

1. **Backup de la base de données** avant d'exécuter la commande
2. **Tester en local** avant la production
3. **Vérifier le montant** avant d'exécuter
4. **Exécuter en maintenance** si possible

---

## 🔧 En Production

### Mode Maintenance

```bash
# Activer le mode maintenance
php artisan down

# Ajouter le solde
php artisan users:add-balance 100

# Désactiver le mode maintenance
php artisan up
```

### Avec Logging

```bash
# Exécuter et logger la sortie
php artisan users:add-balance 100 >> storage/logs/balance-add.log 2>&1
```

---

## 📝 Cas d'Usage

### 1. Bonus de Bienvenue
```bash
# Donner 50 DH à tous les nouveaux outlets
php artisan users:add-balance 50
```

### 2. Compensation d'Erreur
```bash
# Compenser une erreur système avec 200 DH
php artisan users:add-balance 200
```

### 3. Promotion Spéciale
```bash
# Bonus de Ramadan/Eid: 500 DH
php artisan users:add-balance 500
```

---

## 🐛 Dépannage

### Erreur: "Amount must be greater than 0"
```bash
# Le montant doit être positif
php artisan users:add-balance 100  # ✅
php artisan users:add-balance 0    # ❌
php artisan users:add-balance -50  # ❌
```

### Erreur: "Class not found"
```bash
# Vider le cache
php artisan cache:clear
php artisan config:clear

# Réessayer
php artisan users:add-balance 100
```

### Aucun outlet trouvé
```bash
# Vérifier qu'il y a des outlets
php artisan tinker
>>> App\Models\Outlet::count()

# Si 0, créer des outlets d'abord
```

---

## 📞 Support

Pour toute question ou problème:

- **Email:** salim.moustanir@gmail.com
- **WhatsApp:** +212 660 727 185

---

**Dernière mise à jour:** Mars 2026
**Version:** 1.0

---

<div align="center">

**صُنع بـ ❤️ au Maroc 🇲🇦**

</div>
