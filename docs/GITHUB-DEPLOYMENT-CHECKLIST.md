# 🚀 GitHub Deployment Checklist

Use this checklist before pushing to GitHub to ensure no sensitive data is exposed.

---

## 🔒 Security Check

### Files to Remove/Verify

- [ ] **`.env`** - Environment file with secrets
- [ ] **`.env.local`** - Local environment overrides
- [ ] **`.env.production`** - Production credentials
- [ ] **`database/database.sqlite`** - Database with real data
- [ ] **`storage/logs/laravel.log`** - Logs may contain sensitive info
- [ ] **`storage/app/backups/*.sql`** - Database backups
- [ ] **`storage/app/backups/*.zip`** - Backup archives
- [ ] **`storage/framework/sessions/*`** - User sessions
- [ ] **`kyc-files/`** - Customer identification documents
- [ ] **`client raqmi cash web jebab.xlsx`** - Customer data spreadsheets

### Commands to Run

```bash
# Check for .env files
find . -name ".env*" -type f

# Check for SQL files
find . -name "*.sql" -type f

# Check for backup files
find storage/app/backups -type f

# Check git status
git status

# Preview what will be committed
git add .
git status
```

---

## 📁 Files Safe to Commit

### ✅ Core Application Files

- [x] `app/` - Application code
- [x] `config/` - Configuration files (without secrets)
- [x] `database/migrations/` - Database schema
- [x] `database/seeders/` - Database seeders (no real data)
- [x] `database/factories/` - Model factories
- [x] `public/` - Public assets (except build artifacts)
- [x] `resources/` - Views, lang files, assets
- [x] `routes/` - Route definitions
- [x] `tests/` - Test files
- [x] `docs/` - Documentation

### ✅ Configuration Files

- [x] `.gitignore`
- [x] `.editorconfig`
- [x] `composer.json`
- [x] `package.json`
- [x] `vite.config.js`
- [x] `phpunit.xml`
- [x] `Dockerfile`

### ✅ Documentation

- [x] `README.md` or `GITHUB-README.md`
- [x] `LICENSE`
- [x] `CONTRIBUTING.md`
- [x] `SECURITY.md`
- [x] `docs/*` (technical docs)

---

## ⚠️ Files to Review

### May Contain Sensitive Data

- [ ] `.htaccess` - Check for hardcoded paths/credentials
- [ ] `nginx-docker.conf` - Check for internal IPs
- [ ] `nginx-storage.conf` - Check for internal paths
- [ ] `php.ini` - Check for custom settings
- [ ] `activate-security.sh` - Check for secrets
- [ ] `fix-storage-permissions.sh` - Check for credentials
- [ ] `setup_admin.php` - Remove or sanitize
- [ ] `make_zip.php` - Review for security
- [ ] `migrate_v2.php` - Migration scripts
- [ ] `debug_types.php` - Debug files
- [ ] `inspect_excel.php` - Debug files
- [ ] `inspect_headers.php` - Debug files

### Scripts to Review

- [ ] `jebab-clean.sh` - Remove if contains paths
- [ ] `jebab-optimize.sh` - Review for secrets
- [ ] `raqmicash-clean.sh` - Review for secrets
- [ ] `raqmicash-optimize.sh` - Review for secrets
- [ ] `compress-for-download.sh` - Review
- [ ] `telegram-backup-cron.sh` - Remove credentials
- [ ] `CRON-TELEGRAM-BACKUP.txt` - Remove credentials

### Configuration to Sanitize

- [ ] `supervisor-worker.conf` - Remove paths/usernames
- [ ] `.agent/` - Remove if contains internal workflows
- [ ] `scripts/` - Review all scripts

---

## 🧹 Cleanup Commands

### Before Committing

```bash
# Clean Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Remove logs
> storage/logs/laravel.log

# Remove generated files
rm -rf bootstrap/cache/*.php
rm -rf public/build/*

# Remove backups
rm -rf storage/app/backups/*

# Remove KYC files (if any)
rm -rf kyc-files/*

# Remove Excel exports
rm -rf storage/app/exports/*
```

### Git Clean

```bash
# Dry run - see what would be removed
git clean -xdn

# Actually remove untracked files
git clean -xdf

# Be careful! This removes everything not in .gitignore
```

---

## 📝 .env.example Checklist

Ensure `.env.example` has placeholder values:

```env
# App
APP_NAME=Raqmi Cash
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database (use placeholders)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=raqmicash
DB_USERNAME=root
DB_PASSWORD=

# Mail (use placeholders)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com

# Telegram (use placeholders)
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## 🔍 Final Review

### Git Diff Review

```bash
# See all changes
git diff --cached

# Check for secrets
git diff --cached | grep -i "password\|secret\|token\|key"

# Check file sizes (large files might be data)
git diff --cached --numstat
```

### Security Scan

```bash
# Check for exposed credentials
grep -r "password.*=" --include="*.php" --include="*.js" app/
grep -r "API_KEY\|SECRET\|TOKEN" --include="*.php" --include="*.js" app/

# Check for hardcoded emails
grep -r "@gmail.com\|@yahoo.com" --include="*.php" app/
```

---

## ✅ Pre-Push Checklist

- [ ] No `.env` files committed
- [ ] No database files with real data
- [ ] No backup files
- [ ] No log files
- [ ] No KYC/customer documents
- [ ] No hardcoded credentials
- [ ] No API keys or tokens
- [ ] `.env.example` has placeholder values
- [ ] README.md is GitHub-safe
- [ ] All tests pass
- [ ] Code follows standards
- [ ] Documentation is updated

---

## 🚀 Push to GitHub

```bash
# Initial commit
git add .
git commit -m "Initial commit: Raqmi Cash Platform"

# Add remote (replace with your repo)
git remote add origin https://github.com/your-org/raqmicash.git

# Push to main
git push -u origin main
```

---

## 📊 Repository Stats

After pushing, verify:

- [ ] Repository size is reasonable (< 100MB ideal)
- [ ] No unexpected files in commit history
- [ ] README displays correctly
- [ ] License is visible
- [ ] Documentation links work

---

## 🛡️ Post-Push Security

### GitHub Settings

1. **Enable Private Repository** (if not public)
2. **Enable Branch Protection** for main
3. **Enable Security Advisories**
4. **Enable Dependabot Alerts**
5. **Configure Secret Scanning**
6. **Add Repository Description**
7. **Add Topics** (laravel, php, ecommerce, etc.)

### Recommended Settings

```
Settings → Actions → General
☑ Disable non-GitHub Actions (optional)

Settings → Security → Security & Analysis
☑ Enable Dependabot alerts
☑ Enable Dependabot security updates
☑ Enable secret scanning
```

---

**Last Updated:** March 2026
**Version:** 1.0

---

<div align="center">

**Stay Secure! 🔒**

*Raqmi Cash - Made with ❤️ in Morocco 🇲🇦*

</div>
