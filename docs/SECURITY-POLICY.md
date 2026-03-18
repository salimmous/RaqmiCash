# 🔒 Security Policy - Raqmi Cash Platform

Politique de sécurité et procédures pour la plateforme Raqmi Cash.

---

## 📋 Vue d'ensemble

Ce document décrit la politique de sécurité, les procédures et les bonnes pratiques pour maintenir la plateforme sécurisée.

---

## 🛡️ Mesures de Sécurité Implémentées

### 1. Protection contre les Attaques

| Menace | Protection | Statut |
|--------|------------|--------|
| **SQL Injection** | Prepared Statements (Eloquent) | ✅ Actif |
| **XSS** | Auto-escaping Blade + CSP Header | ✅ Actif |
| **CSRF** | Token CSRF sur tous les formulaires | ✅ Actif |
| **Clickjacking** | X-Frame-Options: SAMEORIGIN | ✅ Actif |
| **DDoS** | Cloudflare + Rate Limiting | ✅ Actif |
| **Brute Force** | Rate Limiting + IP Banning | ✅ Actif |

### 2. Middleware de Sécurité

#### Firewall (`app/Http/Middleware/Firewall.php`)

**Fonctionnalités:**
- 🚫 Bannissement IP automatique (24h)
- 🍯 Honeypot traps (.env, wp-login.php, phpmyadmin)
- 🤖 Blocage bots/scraper (ChatGPT, Claude, sqlmap, nmap)
- 🛡️ Détection patterns SQL injection
- 🛡️ Détection patterns XSS
- 📬 Alertes Telegram en temps réel

#### GeoIP Restriction (`app/Http/Middleware/AllowMoroccoOnly.php`)

**Fonctionnalités:**
- 🇲🇦 Accès limité au Maroc (MA)
- 🌐 Cloudflare GeoIP header support
- 🔄 3 sources fallback (Cloudflare, ip-api, ipapi.co)
- 📝 Logging complet des accès
- ⚪ IP whitelist support

#### Security Headers (`app/Http/Middleware/SecurityHeaders.php`)

**En-têtes:**
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000
Content-Security-Policy: default-src 'self'
Referrer-Policy: strict-origin-when-cross-origin
```

### 3. Authentification

**Sécurité des Mots de Passe:**
- Hachage bcrypt (12 rounds)
- Longueur minimale : 8 caractères
- 2FA support (Google Authenticator)
- PIN code pour opérations rapides

**Sécurité de Session:**
- Session unique par utilisateur
- Timeout après 5 minutes d'inactivité
- Cookies sécurisés (Secure, HttpOnly)
- Validation User-Agent

### 4. Journalisation & Monitoring

| Log Type | Description | Rétention |
|----------|-------------|-----------|
| **Activity Logs** | Actions utilisateurs | Illimitée |
| **GeoIP Logs** | Tentatives d'accès | 90 jours |
| **Transaction Logs** | Opérations financières | Illimitée |
| **Laravel Logs** | Erreurs application | 30 jours |
| **Access Logs** | Requêtes HTTP | 7 jours |

---

## 🔐 Procédures de Sécurité

### Gestion des Incidents

#### Niveau 1 - Incident Mineur

**Exemples:**
- Tentative d'accès bloquée
- Erreur de connexion multiple
- Session expirée

**Action:**
- Logging automatique
- Notification utilisateur
- Aucune action admin requise

#### Niveau 2 - Incident Modéré

**Exemples:**
- IP bannie automatiquement
- Pattern d'attaque détecté
- Multiple failed logins

**Action:**
- Alerte Telegram envoyée
- Review par admin dans les 24h
- Documentation dans incident log

#### Niveau 3 - Incident Critique

**Exemples:**
- Brèche de sécurité confirmée
- Accès non autorisé réussi
- Fuite de données

**Action:**
- 🚨 Alerte immédiate à l'équipe
- 🔒 Kill switch activé
- 📞 Réunion d'urgence
- 🔍 Investigation complète
- 📝 Rapport d'incident

### Procédure de Bannissement IP

**Bannissement Automatique:**
```
1. Détection comportement suspect
2. Vérification patterns (honeypot, injection, etc.)
3. Bannissement immédiat (24h)
4. Notification Telegram
5. Logging dans blocked_ips
```

**Bannissement Manuel:**
```
1. Review par admin
2. Confirmation de la menace
3. Bannissement via Admin Panel
4. Durée : 24h / 7j / permanent
5. Notification (optionnel)
```

**Débannissement:**
```
1. Vérification raison du bannissement
2. Si erreur → Débannir immédiatement
3. Si justifié → Maintenir bannissement
4. Documentation de la décision
```

### Gestion des Mots de Passe

**Exigences:**
- Longueur minimale : 8 caractères
- Doit contenir : majuscule, minuscule, chiffre
- Expiré tous les 90 jours (recommandé)
- Historique : 5 derniers mots de passe interdits

**Procédure de Reset:**
```
1. Utilisateur demande reset
2. Email avec lien sécurisé envoyé
3. Lien valable 1 heure
4. Nouveau mot de passe requis
5. Notification de sécurité envoyée
```

---

## 🚨 Réponse aux Incidents

### Checklist de Réponse

#### Phase 1 : Identification

- [ ] Confirmer l'incident
- [ ] Identifier la source
- [ ] Évaluer l'impact
- [ ] Classifier la sévérité
- [ ] Notifier l'équipe

#### Phase 2 : Containment

- [ ] Isoler le système affecté
- [ ] Bannir IPs malveillantes
- [ ] Révoquer accès compromis
- [ ] Activer kill switch si nécessaire
- [ ] Préserver les preuves

#### Phase 3 : Éradication

- [ ] Supprimer accès attaquant
- [ ] Corriger vulnérabilité
- [ ] Scanner pour backdoors
- [ ] Mettre à jour systèmes
- [ ] Renforcer sécurité

#### Phase 4 : Recovery

- [ ] Restaurer systèmes
- [ ] Vérifier intégrité
- [ ] Surveiller activité
- [ ] Rétablir service normal
- [ ] Documenter actions

#### Phase 5 : Lessons Learned

- [ ] Analyser root cause
- [ ] Documenter incident
- [ ] Mettre à jour procédures
- [ ] Former équipe
- [ ] Implémenter améliorations

---

## 📊 Surveillance Continue

### Dashboard de Sécurité

**Chemin:** `Admin → Security Dashboard`

**Métriques:**
- 🔴 Failed logins (24h)
- 🚫 Blocked IPs
- 🌍 GeoIP denied accesses
- ⚠️ Suspicious activities
- 👥 Active sessions
- 🔑 Recent password changes

### Alertes Automatisées

| Événement | Canal | Priorité |
|-----------|-------|----------|
| IP bloquée | Telegram | Moyenne |
| Login Admin | Telegram | Moyenne |
| Multiple failed logins | Telegram | Haute |
| Attack pattern détecté | Telegram + Email | Haute |
| Brèche confirmée | Téléphone | Critique |

### Revue Quotidienne

**Checklist Admin:**
- [ ] Review blocked IPs
- [ ] Check failed logins
- [ ] Verify GeoIP denials
- [ ] Scan activity logs
- [ ] Monitor queue jobs
- [ ] Check error logs

---

## 🔒 Configuration de Sécurité

### Variables d'Environnement

```env
# Security Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.raqmicash.com

# Session Security
SESSION_LIFETIME=300
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true

# GeoIP
GEO_ALLOWED_COUNTRIES=MA
GEO_REQUIRE_COUNTRY_HEADER=true
GEO_ALLOWLIST_IPS=127.0.0.1,::1

# Rate Limiting
RATE_LIMIT_LOGIN=5
RATE_LIMIT_API=60

# Telegram Notifications
TELEGRAM_BOT_TOKEN="your_token"
TELEGRAM_CHAT_ID="your_chat_id"

# Kill Switch
SECURITY_LOCKDOWN=false
```

### Permissions de Fichiers

```bash
# Répertoires sensibles
chmod 775 storage/
chmod 775 bootstrap/cache/
chmod 644 .env
chmod 644 artisan

# Propriétaire
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

---

## 🛡️ Bonnes Pratiques

### Pour les Administrateurs

1. ✅ Utiliser 2FA toujours
2. ✅ Changer mot de passe tous les 90 jours
3. ✅ Vérifier activité session régulièrement
4. ✅ Ne jamais partager credentials
5. ✅ Se déconnecter après utilisation
6. ✅ Utiliser réseau sécurisé
7. ✅ Review logs quotidiennement

### Pour les Développeurs

1. ✅ Valider tous les inputs
2. ✅ Utiliser prepared statements
3. ✅ Échapper outputs
4. ✅ Ne pas logger données sensibles
5. ✅ Review code avant merge
6. ✅ Tests de sécurité obligatoires
7. ✅ Mettre à jour dépendances

### Pour les Utilisateurs

1. ✅ Mot de passe fort
2. ✅ Ne pas partager credentials
3. ✅ Vérifier URL avant login
4. ✅ Se déconnecter après utilisation
5. ✅ Signaler activité suspecte
6. ✅ Garder contact info à jour

---

## 📈 Audit de Sécurité

### Audit Trimestriel

**Checklist:**
- [ ] Review accès utilisateurs
- [ ] Vérifier permissions fichiers
- [ ] Scanner vulnérabilités
- [ ] Test penetration
- [ ] Review logs d'incidents
- [ ] Mettre à jour dépendances
- [ ] Backup test & recovery
- [ ] Review politique sécurité

### Outils Recommandés

| Outil | Usage |
|-------|-------|
| **OWASP ZAP** | Scan vulnérabilités |
| **Nmap** | Scan réseau |
| **SQLMap** | Test SQL injection |
| **Nikto** | Scan serveur web |
| **Laravel Audit** | Scan code Laravel |

---

## 🆘 Contacts d'Urgence

### Équipe de Sécurité

| Rôle | Contact | Disponibilité |
|------|---------|---------------|
| **Security Lead** | security@raqmicash.com | 24/7 |
| **Admin On-Call** | +212 600 000 000 | 24/7 |
| **Technical Lead** | tech@raqmicash.com | 9h-18h |

### Escalation

```
Niveau 1 → Admin On-Call (30 min)
Niveau 2 → Security Lead (15 min)
Niveau 3 → All Hands (immédiat)
```

---

## 📝 Conformité

### RGPD

- ✅ Consentement explicite pour données
- ✅ Droit à l'oubli
- ✅ Portabilité des données
- ✅ Notification brèche (72h)
- ✅ DPO désigné

### PCI DSS

- ✅ Chiffrement données carte
- ✅ Accès restreint
- ✅ Monitoring continu
- ✅ Tests de sécurité réguliers

---

## 📚 Ressources

- **OWASP Top 10:** https://owasp.org/www-project-top-ten/
- **Laravel Security:** https://laravel.com/docs/security
- **CWE/SANS:** https://www.sans.org/top25-software-errors/

---

**Dernière mise à jour :** Mars 2026  
**Version :** 1.0  
**Statut :** ✅ Production Ready
