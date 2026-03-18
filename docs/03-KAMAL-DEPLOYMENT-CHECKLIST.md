# 📋 Kamal Deployment Checklist - Raqmi Cash Platform

Checklist complète pour un déploiement réussi avec Kamal.

---

## ✅ Pré-Déploiement

### 1. Vérifications Initiales

- [ ] Kamal est installé (`kamal version`)
- [ ] Docker est installé et fonctionnel
- [ ] SSH key configurée pour le serveur
- [ ] Accès au serveur vérifié (`ssh deploy@your-server.com`)

### 2. Configuration Kamal

- [ ] `.kamal/.env` créé à partir de `.kamal/.env.example`
- [ ] Variables sensibles configurées dans `.kamal/.env`
- [ ] `.kamal/deploy.yml` mis à jour avec :
  - [ ] Adresse IP du serveur
  - [ ] Docker Hub username
  - [ ] Nom de l'image Docker
  - [ ] Configuration SSL/Traefik

### 3. Variables d'Environnement

- [ ] `APP_KEY` généré (`php artisan key:generate --show`)
- [ ] `DB_PASSWORD` défini
- [ ] `KAMAL_REGISTRY_PASSWORD` défini (Docker Hub token)
- [ ] Credentials email configurés
- [ ] Tokens Telegram configurés (optionnel)

### 4. Base de Données

- [ ] MySQL installé sur le serveur
- [ ] Base de données créée
- [ ] Utilisateur DB créé avec permissions
- [ ] Connection testée depuis l'application

---

## 🚀 Déploiement

### 5. Build & Push

- [ ] Login au registry Docker : `kamal registry login`
- [ ] Build local testé : `kamal build`
- [ ] Push de l'image : `kamal push`

### 6. Déploiement Initial

- [ ] Premier déploiement : `kamal deploy`
- [ ] Logs vérifiés : `kamal app logs -f`
- [ ] Health check passing : `curl https://jebab.com/api/health`

### 7. Post-Déploiement

- [ ] Migrations exécutées
- [ ] Cache configuré (`config:cache`, `route:cache`)
- [ ] Storage link créé
- [ ] Workers démarrés
- [ ] SSL certificat généré (Traefik)

---

## 🧪 Tests

### 8. Tests Fonctionnels

- [ ] Page d'accueil accessible
- [ ] Login fonctionnel
- [ ] API health check : `/api/health`
- [ ] Recharge testée
- [ ] Transfert testé
- [ ] Dashboard admin accessible
- [ ] Upload de fichiers fonctionnel

### 9. Tests de Performance

- [ ] Temps de réponse < 500ms
- [ ] Assets CSS/JS chargés
- [ ] Images compressées
- [ ] Cache fonctionnel

---

## 🔐 Sécurité

### 10. Vérifications de Sécurité

- [ ] HTTPS forcé (redirection 80 → 443)
- [ ] `.env` non accessible via web
- [ ] `.git` directory protégée
- [ ] Headers de sécurité présents
- [ ] Rate limiting activé
- [ ] GeoIP filtering actif (Maroc uniquement)

### 11. Backup & Recovery

- [ ] Backup DB automatique configuré
- [ ] Backup storage configuré
- [ ] Procédure de rollback testée
- [ ] Telegram notifications actives

---

## 📊 Monitoring

### 12. Surveillance

- [ ] Logs centralisés
- [ ] Health check monitoring
- [ ] Alertes configurées (disk, CPU, RAM)
- [ ] Uptime monitoring activé

---

## 🔄 Rollback (Si Nécessaire)

### 13. Procédure de Rollback

- [ ] Version précédente identifiée : `kamal app history`
- [ ] Rollback effectué : `kamal rollback`
- [ ] Tests post-rollback passés
- [ ] Issue documentée

---

## 📝 Notes de Déploiement

### Version : _______
### Date : _______
### Déployé par : _______

**Changements :**
```
- 
- 
- 
```

**Issues rencontrées :**
```
- 
- 
```

**Actions correctives :**
```
- 
- 
```

---

## 🎯 Commandes Rapides

```bash
# Déployer
kamal deploy

# Voir les logs
kamal app logs -f

# Health check
kamal app healthcheck

# Redémarrer
kamal app reboot

# Rollback
kamal rollback

# Exécuter une commande
kamal app exec "php artisan about"
```

---

## 📞 Support

En cas de problème :

1. Vérifier les logs : `kamal app logs`
2. Check health : `kamal app healthcheck`
3. Voir status : `kamal app status`
4. Console debug : `kamal app shell`

---

**Dernière mise à jour :** Mars 2026  
**Version :** 1.0
