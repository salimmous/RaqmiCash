# 🛡️ Security Quick Reference - Raqmi Cash Platform

## 🚨 Emergency Commands

### Lockdown Mode (Block All Users)
```bash
# Enable lockdown (only super admins can access)
php artisan security:lockdown --enable

# Disable lockdown
php artisan security:lockdown --disable

# Check status
php artisan security:lockdown --status

# Force logout ALL users
php artisan security:lockdown --logout-all
```

### Clear Firewall Bans
```bash
# Clear all banned IPs
php artisan cache:clear

# Or specific IP
# (Manually remove from Redis/File cache)
```

---

## 🔐 Security Features Overview

| Feature | Status | Description |
|---------|--------|-------------|
| **Firewall** | ✅ Active | IP banning, honeypots, bot blocking |
| **GeoIP Restriction** | ✅ Active | Morocco-only access (MA) |
| **Security Headers** | ✅ Active | HSTS, CSP, Anti-Clickjacking |
| **Session Validation** | ✅ Active | Prevents session hijacking |
| **Rate Limiting** | ✅ Active | Login, API, transfers |
| **Emergency Lockdown** | ✅ Active | Kill switch for attacks |

---

## 📊 Security Monitoring

### Check Security Logs
```sql
-- Recent critical incidents
SELECT * FROM security_logs 
WHERE severity = 'critical' 
ORDER BY created_at DESC 
LIMIT 50;

-- Failed logins (last 24h)
SELECT COUNT(*) FROM security_logs 
WHERE event_type = 'FAILED_LOGIN' 
AND created_at >= NOW() - INTERVAL 24 HOUR;

-- Top blocked IPs
SELECT ip_address, COUNT(*) as blocks 
FROM security_logs 
WHERE event_type = 'IP_BLOCKED' 
GROUP BY ip_address 
ORDER BY blocks DESC 
LIMIT 20;
```

### Telegram Alerts
All security incidents are sent to Telegram:
- 🚫 IP blocks
- 🔑 Admin logins
- 🚨 Session hijacking attempts
- 🔒 Lockdown enable/disable

---

## 🎯 Common Attack Responses

### Brute Force Attack
```bash
# 1. Check current attacks
tail -f storage/logs/laravel.log | grep -i "failed"

# 2. Enable stricter rate limiting (edit .env)
SECURITY_LOCKDOWN=true

# 3. Apply
php artisan config:clear

# 4. Monitor
php artisan security:lockdown --status
```

### DDoS Attack
```bash
# 1. Enable Cloudflare Under Attack mode (via dashboard)

# 2. Enable lockdown
php artisan security:lockdown --enable

# 3. Block country (if not Morocco)
# Edit .env: GEO_ALLOWED_COUNTRIES=MA

# 4. Clear cache
php artisan cache:clear
```

### Suspicious Activity Detected
```bash
# 1. Force logout suspicious user
php artisan security:lockdown --logout-all

# 2. Review logs
SELECT * FROM security_logs 
WHERE user_id = [USER_ID] 
ORDER BY created_at DESC;

# 3. Ban IP manually (add to firewall blocklist)
```

---

## 🔧 Configuration Files

### Environment Variables (.env)
```env
# Security Lockdown
SECURITY_LOCKDOWN=false

# GeoIP Restrictions
GEO_ALLOWED_COUNTRIES=MA
GEO_REQUIRE_COUNTRY_HEADER=true

# Cloudflare Proxy
TRUST_CLOUDFLARE_PROXY=true
TRUSTED_PROXIES=

# Telegram Alerts
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id

# Session Security
SESSION_LIFETIME=120
SESSION_SECURE_COOKIES=true
```

### Middleware Stack (bootstrap/app.php)
```php
// Order matters!
$middleware->web(append: [
    \App\Http\Middleware\SecurityLockdown::class,    // First!
    \App\Http\Middleware\SecurityHeaders::class,
    \App\Http\Middleware\Firewall::class,
    \App\Http\Middleware\AllowMoroccoOnly::class,
    \App\Http\Middleware\ValidateSession::class,     // Last!
]);
```

---

## 📱 Device Management

### View User Devices
```sql
SELECT 
    u.email,
    ud.browser_name,
    ud.os_name,
    ud.device_type,
    ud.ip_address,
    ud.country,
    ud.is_trusted,
    ud.last_seen_at
FROM user_devices ud
JOIN users u ON u.id = ud.user_id
ORDER BY ud.last_seen_at DESC;
```

### Trust a Device
```php
// In Tinker
$device = App\Models\UserDevice::find($deviceId);
$device->markAsTrusted();
```

---

## 🔒 Password Policy

### Requirements
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number
- At least 1 special character
- No common passwords
- No sequential characters

### Expiry
- Passwords expire every 90 days
- Warning shown 7 days before expiry

---

## 📞 Security Contacts

| Issue | Contact |
|-------|---------|
| Emergency | Telegram Bot |
| Security Questions | security@jebab.com |
| False Positives | Admin Dashboard |

---

## 🎯 Security Checklist (Daily)

- [ ] Review security logs (critical/high severity)
- [ ] Check failed login attempts
- [ ] Monitor blocked IPs
- [ ] Verify active sessions count
- [ ] Check for new device logins
- [ ] Review transfer rate limits

---

## 🚀 Quick Security Test

```bash
# Test firewall (should be blocked)
curl -I https://jebab.com/.env
curl -I https://jebab.com/wp-login.php

# Test rate limiting (should block after 5 attempts)
for i in {1..6}; do curl -X POST https://jebab.com/api/login; done

# Test GeoIP (should work from Morocco)
curl -H "CF-IPCountry: MA" https://jebab.com

# Test security headers
curl -I https://jebab.com | grep -E "X-Frame|X-Content|Strict-Transport"
```

---

## 📚 Full Documentation

- [Advanced Security Hardening](ADVANCED-SECURITY-HARDENING.md)
- [Security Recommendations](SECURITY-RECOMMENDATIONS.md)
- [Security Policy](../SECURITY.md)

---

**Last Updated:** 2026-03-07  
**Version:** 2.0  
**Status:** 🛡️ Maximum Security Active
