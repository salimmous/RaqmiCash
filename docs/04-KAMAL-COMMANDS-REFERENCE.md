# 🚀 Kamal Quick Reference - Raqmi Cash Platform

Référence rapide des commandes Kamal pour le déploiement quotidien.

---

## Installation

```bash
# Installer Kamal
gem install kamal

# Vérifier la version
kamal version
```

---

## Commandes Principales

### Déploiement

```bash
# Déployer la version actuelle
kamal deploy

# Déployer avec un message
kamal deploy -m "Fix: correction bug pagination"

# Déployer une version spécifique
kamal deploy --version v1.2.3

# Déployer un rôle spécifique
kamal deploy -r web
kamal deploy -r workers
```

### Status & Monitoring

```bash
# Voir l'état des conteneurs
kamal app status

# Voir les détails d'un conteneur
kamal app details

# Health check
kamal app healthcheck

# Ping pour vérifier la disponibilité
kamal app ping

# Utilisation CPU/Mémoire
kamal app top

# Statistiques
kamal app stats
```

### Logs

```bash
# Voir les logs
kamal app logs

# Follow les logs (temps réel)
kamal app logs -f

# Logs avec tail
kamal app logs --tail=100

# Logs d'un rôle spécifique
kamal app logs -r workers
kamal app logs -r traefik
```

### Gestion des Conteneurs

```bash
# Redémarrer l'application
kamal app reboot

# Arrêter l'application
kamal app stop

# Démarrer l'application
kamal app start

# Redémarrer un rôle spécifique
kamal app reboot -r workers
```

### Exécution de Commandes

```bash
# Exécuter une commande
kamal app exec "php artisan about"

# Exécuter sur un rôle spécifique
kamal app exec -r workers "php artisan queue:restart"

# Exécuter avec shell interactif
kamal app exec "bash"
```

### Shell & Debug

```bash
# SSH dans un conteneur
kamal app shell

# SSH dans un rôle spécifique
kamal app shell -r workers

# SSH sur le serveur
kamal server shell
```

---

## Registry & Build

```bash
# Login au registry Docker
kamal registry login

# Build l'image Docker
kamal build

# Push l'image
kamal push

# Build et push
kamal build && kamal push
```

---

## Rollback

```bash
# Voir l'historique des versions
kamal app history

# Revenir à la version précédente
kamal rollback

# Revenir à une version spécifique
kamal rollback abc1234
```

---

## Configuration

```bash
# Vérifier la configuration
kamal config

# Initialiser une nouvelle config
kamal init
```

---

## Hooks

Les hooks sont exécutés automatiquement :

- `pre-build` : Avant le build Docker
- `pre-deploy` : Avant le déploiement
- `post-deploy` : Après le déploiement

```bash
# Tester un hook manuellement
.kamal/hooks/pre-build
.kamal/hooks/post-deploy
```

---

## Variables d'Environnement

```bash
# Charger les variables depuis .kamal/.env
export $(cat .kamal/.env | xargs)

# Vérifier les variables
kamal config | grep env
```

---

## Scénarios Communs

### 1. Premier Déploiement

```bash
# 1. Login
kamal registry login

# 2. Déployer
kamal deploy

# 3. Vérifier
kamal app status
kamal app healthcheck
```

### 2. Déploiement Rapide (Hotfix)

```bash
# Build et deploy en une commande
kamal deploy -m "Hotfix: critical bug fix"

# Suivre les logs
kamal app logs -f
```

### 3. Rollback d'Urgence

```bash
# Voir l'historique
kamal app history

# Rollback immédiat
kamal rollback

# Vérifier
kamal app status
```

### 4. Debug d'un Problème

```bash
# Voir les logs d'erreur
kamal app logs --tail=200 | grep -i error

# SSH dans le conteneur
kamal app shell

# Vérifier l'état
kamal app healthcheck
```

### 5. Redémarrage des Workers

```bash
# Redémarrer les queue workers
kamal app reboot -r workers

# Ou restart queue via artisan
kamal app exec -r workers "php artisan queue:restart"
```

---

## Astuces

### Alias Utiles

Ajoutez à votre `~/.bashrc` ou `~/.zshrc` :

```bash
alias kd='kamal deploy'
alias ks='kamal app status'
alias kl='kamal app logs -f'
alias kr='kamal app reboot'
alias kh='kamal app healthcheck'
alias ksh='kamal app shell'
```

### Script de Déploiement

```bash
#!/bin/bash
# deploy.sh

echo "🚀 Starting deployment..."
kamal deploy -m "$1"
kamal app healthcheck
echo "✅ Deployment completed!"
```

Usage :
```bash
./deploy.sh "Fix: bug correction"
```

---

## Dépannage Rapide

| Problème | Commande |
|----------|----------|
| Container ne démarre pas | `kamal app logs --tail=100` |
| Erreur de build | `kamal builder clean` |
| Health check échoue | `kamal app shell` puis debug manuel |
| Workers bloqués | `kamal app exec -r workers "php artisan queue:restart"` |
| SSL expiré | `kamal app reboot -r traefik` |

---

## Ressources

- 📖 [Documentation Complète](./02-KAMAL-DEPLOYMENT-GUIDE.md)
- 📋 [Checklist de Déploiement](./03-KAMAL-DEPLOYMENT-CHECKLIST.md)
- 🔗 [Kamal Officiel](https://kamal-deploy.org/)

---

**Dernière mise à jour :** Mars 2026  
**Version :** 1.0
