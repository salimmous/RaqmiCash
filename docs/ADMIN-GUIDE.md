# 👨‍💼 Guide Administrateur - Raqmi Cash Platform

Guide complet pour les administrateurs de la plateforme.

---

## 🎯 Vue d'ensemble

Ce document couvre toutes les fonctionnalités d'administration de la plateforme Raqmi Cash.

---

## 🔐 Accès Admin

### URL de Connexion

```
https://app.your-domain.com/admin/login
```

### Compte Admin par Défaut

```
Email: admin@raqmicash.ma
Mot de passe: password
```

**⚠️ Important:** Changez le mot de passe immédiatement après la première connexion !

---

## 📊 Tableau de Bord

### Statistiques Affichées

| Carte | Description |
|-------|-------------|
| **Total Recharges** | Nombre total de recharges (tous statuts) |
| **Revenus du Jour** | Commissions générées aujourd'hui |
| **Abonnements en Attente** | Demandes à approuver/rejeter |
| **Nouveaux Outlets** | Outlets créés récemment |

### Graphiques

- **Évolution des Recharges** (7 derniers jours)
- **Répartition par Opérateur** (IAM, Orange, Inwi)
- **Top 5 Outlets** (par volume)

### Actions Rapides

1. **Nouvelle Alimentation** - Ajouter du solde à un outlet
2. **Approuver Abonnements** - Traiter les demandes en attente
3. **Créer Outlet** - Ajouter un nouveau point de vente

---

## 🏪 Gestion des Outlets

### Liste des Outlets

**Chemin:** `Admin → Outlets`

**Fonctionnalités:**
- ✅ Voir tous les outlets
- ✅ Filtrer par statut (actif/inactif)
- ✅ Rechercher par nom/poscode
- ✅ Exporter en Excel

### Créer un Outlet

1. Cliquer sur **"Nouvel Outlet"**
2. Remplir le formulaire :
   - Nom de l'outlet
   - Email
   - Téléphone
   - Taux de commission (%)
   - Solde initial (optionnel)
3. Enregistrer

**Résultat:**
- Un utilisateur avec rôle `outlet` est créé
- Un code POS unique est généré (ex: OT1001)
- Identifiants envoyés par email

### Modifier un Outlet

1. Cliquer sur l'outlet dans la liste
2. Modifier les informations :
   - Taux de commission
   - Statut (actif/inactif)
   - Solde (manuel)
3. Enregistrer

### Alimentation (Ajout de Solde)

**Chemin:** `Admin → Alimentations`

**Procédure:**
1. Sélectionner l'outlet
2. Entrer le montant
3. Choisir le type (Solde / Bonus)
4. Confirmer

**Impact:**
- Débit du compte admin
- Crédit du compte outlet
- Transaction enregistrée dans `transactions_pos`

---

## 📱 Gestion des Recharges

### Liste des Recharges

**Chemin:** `Admin → Recharges`

**Filtres Disponibles:**
- Par statut (pending, completed, failed)
- Par opérateur (IAM, Orange, Inwi)
- Par date
- Par outlet

### Traiter une Recharge

1. Voir les recharges **pending**
2. Vérifier les détails
3. Cliquer sur **"Traiter"**
4. Choisir le statut (completed/failed)
5. Ajouter un message (optionnel)

---

## 🌐 Gestion des Abonnements

### Liste des Abonnements

**Chemin:** `Admin → Subscriptions`

**Statuts:**
- 🟡 **Pending** - En attente de validation
- 🟢 **Approved** - Approuvé et traité
- 🔴 **Rejected** - Rejeté avec motif

### Approuver un Abonnement

1. Ouvrir la demande
2. Vérifier les informations :
   - CIN du client
   - Image CIN
   - Offre choisie
3. Cliquer sur **"Approuver"**
4. Le service fee est débité
5. La commission est créditée à l'outlet

### Rejeter un Abonnement

1. Ouvrir la demande
2. Cliquer sur **"Rejeter"**
3. Spécifier le motif :
   - CIN illisible
   - Informations incorrectes
   - Service indisponible
4. Confirmer

---

## 💸 Gestion des Transferts

### Liste des Transferts

**Chemin:** `Admin → Transfers`

**Informations Affichées:**
- Outlet émetteur
- Outlet destinataire
- Montant
- Date et heure
- Statut

### Suivi des Transferts

Tous les transferts sont automatiquement traités. Le rôle de l'admin est :
- ✅ Surveiller les transactions suspectes
- ✅ Vérifier les soldes
- ✅ Intervenir en cas d'erreur

---

## 🛍️ Gestion des Produits

### Catalogue Produits

**Chemin:** `Admin → Products`

**Fonctionnalités:**
- ✅ Ajouter un produit
- ✅ Modifier prix et stock
- ✅ Activer/Désactiver
- ✅ Gérer les catégories

### Ajouter un Produit

1. Cliquer sur **"Nouveau Produit"**
2. Remplir :
   - Nom
   - Catégorie
   - Prix d'achat
   - Prix de vente
   - Stock initial
   - Description
3. Enregistrer

**Calcul automatique:**
- Marge = Prix vente - Prix achat
- % Marge = (Marge / Prix achat) × 100

### Gestion des Stocks

**Chemin:** `Admin → Stock Movements`

**Types de Mouvements:**
- 📥 **Arrivage** - Ajout de stock
- 📤 **Vente** - Déduction automatique
- ⚠️ **Perte** - Stock endommagé/perdu
- 🔄 **Ajustement** - Correction manuelle

---

## 🎁 Gestion des Offres

### Créer une Offre

**Chemin:** `Admin → Offers`

**Champs:**
- Titre de l'offre
- Description
- Type (recharge, subscription, product)
- Réduction (% ou fixe)
- Dates de début/fin
- Statut (actif/inactif)

### Exemples d'Offres

```
🌙 Promotion Ramadan
- Type: Recharge
- Réduction: 10%
- Période: 01/03 - 31/03

🎉 Offre Spéciale IAM
- Type: Abonnement
- Réduction: 50 DH
- Période: 01/03 - 15/03
```

---

## 📊 Rapports & Analytics

### Rapports Disponibles

**Chemin:** `Admin → Reports`

1. **Rapport des Recharges**
   - Par jour/semaine/mois
   - Par opérateur
   - Par outlet

2. **Rapport des Abonnements**
   - Taux d'approbation
   - Délai moyen de traitement
   - Par provider

3. **Rapport Financier**
   - Revenus totaux
   - Commissions versées
   - Transferts nets

4. **Rapport de Stock**
   - Valeur du stock
   - Produits en rupture
   - Mouvements par période

### Export des Données

- 📄 **PDF** - Pour impression
- 📊 **Excel** - Pour analyse
- 📋 **CSV** - Pour import autre système

---

## 🔒 Sécurité Admin

### Journal d'Activité

**Chemin:** `Admin → Activity Logs`

**Informations Trackées:**
- Qui a fait quoi
- Quand (date/heure)
- Depuis quelle IP
- Détails de l'action

### Logs GeoIP

**Chemin:** `Admin → GeoIP Logs`

**Fonctionnalités:**
- Voir tous les accès
- Filtrer par pays
- Voir les accès refusés
- Top IPs bloquées

### Paramètres de Sécurité

**Chemin:** `Admin → Settings → Security`

**Options:**
- ✅ Durée de session (minutes)
- ✅ Pays autorisés
- ✅ IPs whitelistées
- ✅ 2FA obligatoire

---

## ⚙️ Paramètres

### Configuration Générale

**Chemin:** `Admin → Settings`

| Paramètre | Description |
|-----------|-------------|
| **Nom du Site** | Nom affiché partout |
| **Email Contact** | Email de support |
| **Téléphone** | Numéro de contact |
| **Devise** | MAD, EUR, USD... |
| **Fuseau Horaire** | Africa/Casablanca |

### Configuration des Services

**Chemin:** `Admin → Settings → Services`

**Activer/Désactiver:**
- 📱 Service Recharges
- 🌐 Service Abonnements
- 🛍️ Service E-Boutique
- 💸 Service Transferts

### Configuration des Taux

**Chemin:** `Admin → Settings → Rates`

**Taux par Défaut:**
- Commission Recharge: 7%
- Bonus Recharge: 7%
- Commission IAM Mobile: 50 DH
- Commission IAM ADSL: 150 DH

---

## 🛠️ Maintenance

### Nettoyage des Logs

**Chemin:** `Admin → Maintenance`

**Actions:**
- 🗑️ Supprimer logs > 90 jours
- 🗑️ Vider cache
- 🗑️ Archiver transactions anciennes

### Sauvegarde Base de Données

**Chemin:** `Admin → Maintenance → Backup`

**Options:**
- 💾 Backup complet
- 💾 Backup partiel (tables spécifiques)
- 📅 Planification automatique

### File d'Attente (Queue)

**Chemin:** `Admin → Maintenance → Queue`

**Surveillance:**
- Jobs en attente
- Jobs échoués
- Retry des jobs échoués

---

## 📱 Notifications

### Configuration Telegram

**Chemin:** `Admin → Settings → Notifications`

**Variables .env:**
```env
TELEGRAM_BOT_TOKEN="your_bot_token"
TELEGRAM_CHAT_ID="your_chat_id"
```

**Notifications Envoyées:**
- 🚫 IP bloquée
- 🔑 Login admin
- ⚠️ Erreur critique
- 📊 Rapport quotidien

---

## 👥 Gestion des Utilisateurs

### Liste des Utilisateurs

**Chemin:** `Admin → Users`

**Filtres:**
- Par rôle (admin, outlet, customer)
- Par statut (actif/inactif)
- Par date d'inscription

### Modifier un Utilisateur

1. Sélectionner l'utilisateur
2. Modifier :
   - Nom
   - Email
   - Téléphone
   - Rôle
   - Statut
3. Enregistrer

### Réinitialiser Mot de Passe

1. Ouvrir le profil utilisateur
2. Cliquer sur **"Reset Password"**
3. Nouveau mot de passe généré
4. Envoyer par email

---

## 📋 Checklist Quotidienne Admin

### Matin
- [ ] Vérifier abonnements en attente
- [ ] Contrôler transactions échouées
- [ ] Vérifier alertes de sécurité
- [ ] Review logs GeoIP

### Après-midi
- [ ] Traiter nouvelles demandes
- [ ] Vérifier soldes outlets
- [ ] Contrôler stocks produits
- [ ] Review activités suspectes

### Soir
- [ ] Générer rapport journalier
- [ ] Archiver logs anciens
- [ ] Vérifier backups
- [ ] Planifier tâches lendemain

---

## 🆘 Dépannage

### Problème: Outlet ne peut pas se connecter

**Solution:**
1. Vérifier statut du compte (actif/inactif)
2. Réinitialiser mot de passe
3. Vérifier logs de connexion

### Problème: Recharge bloquée en pending

**Solution:**
1. Ouvrir la recharge
2. Vérifier détails
3. Traiter manuellement (completed/failed)
4. Contacter le support si besoin

### Problème: Solde incorrect

**Solution:**
1. Vérifier historique des transactions
2. Contrôler logs d'activité
3. Ajuster manuellement si erreur
4. Documenter l'ajustement

---

## 📞 Support Admin

- **Email:** admin@your-domain.com
- **Urgence:** +212 600 000 000
- **Documentation:** docs/ADMIN-GUIDE.md

---

**Dernière mise à jour :** Mars 2026  
**Version :** 1.0
