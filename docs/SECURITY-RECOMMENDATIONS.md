# 🔒 Security Recommendations - Raqmi Cash Platform

## ✅ Security Features Already Implemented

### 1. **Firewall Middleware** (`app/Http/Middleware/Firewall.php`)
- ✅ IP banning system (24 hours)
- ✅ Honeypot traps (.env, wp-login.php, phpmyadmin, etc.)
- ✅ Bot/scraper blocking (AI bots, security scanners)
- ✅ SQL injection patterns detection
- ✅ XSS pattern detection
- ✅ Path traversal protection
- ✅ Telegram alerts for blocked IPs

### 2. **GeoIP Restriction** (`app/Http/Middleware/AllowMoroccoOnly.php`)
- ✅ Morocco-only access (MA)
- ✅ Cloudflare GeoIP header support
- ✅ Multiple fallback APIs (ip-api, ipwhois)
- ✅ IP whitelist support
- ✅ GeoIP logging to database

### 3. **Security Headers** (`app/Http/Middleware/SecurityHeaders.php`)
- ✅ X-Frame-Options (clickjacking protection)
- ✅ X-Content-Type-Options
- ✅ X-XSS-Protection
- ✅ Strict-Transport-Security (HSTS)
- ✅ Content-Security-Policy
- ✅ Referrer-Policy

### 4. **Session Security**
- ✅ Single session enforcement
- ✅ Session timeout (5 minutes idle)
- ✅ Secure cookies
- ✅ HTTP-only cookies
- ✅ CSRF protection
- ✅ Session activity logging

### 5. **Authentication**
- ✅ Password hashing (bcrypt, 12 rounds)
- ✅ 2FA support (Google Authenticator)
- ✅ PIN code for quick operations
- ✅ Staff account system
- ✅ Role-based access control

### 6. **Activity Logging** (`app/Models/ActivityLog.php`)
- ✅ All user actions logged
- ✅ IP address tracking
- ✅ User agent tracking
- ✅ Subject-based logging (who did what)

### 7. **GeoIP Logging** (`app/Models/GeoIpLog.php`)
- ✅ All access attempts logged
- ✅ Country detection tracking
- ✅ Denied access tracking
- ✅ Top denied IPs monitoring

---

## 🚀 Additional Security Recommendations

### 1. **Rate Limiting** ⭐⭐⭐⭐⭐

**Add to `app/Http/Kernel.php`:**
```php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \Illuminate\Http\Middleware\ThrottleRequests::class.':api',
    ],
];
```

**In `routes/api.php`:**
```php
Route::middleware('throttle:60,1')->group(function () {
    // 60 requests per minute
});
```

**Login rate limiting (already implemented):**
```php
Route::post('/login', ...)->middleware('throttle:login');
```

---

### 2. **Password Security Policy** ⭐⭐⭐⭐⭐

**Create `app/Http/Middleware/PasswordExpiryMiddleware.php`:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PasswordExpiryMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return $next($request);
        }
        
        // Force password change every 90 days
        $passwordChangedAt = $user->password_changed_at ?? $user->created_at;
        if ($passwordChangedAt && $passwordChangedAt->diffInDays(now()) > 90) {
            if (!$request->routeIs('password.*')) {
                return redirect()->route('password.change')
                    ->with('warning', 'Votre mot de passe a expiré. Veuillez le changer.');
            }
        }
        
        return $next($request);
    }
}
```

---

### 3. **Device Fingerprinting** ⭐⭐⭐⭐

**Track user devices:**

**Migration:**
```php
Schema::create('user_devices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('fingerprint'); // Device fingerprint
    $table->string('ip_address', 45);
    $table->text('user_agent');
    $table->boolean('is_trusted')->default(false);
    $table->timestamp('last_seen_at');
    $table->timestamps();
});
```

**In login:**
```php
$fingerprint = hash('sha256', 
    $request->ip() . 
    $request->header('User-Agent') . 
    $request->header('Accept-Language')
);

// Check if new device
$newDevice = !UserDevice::where('user_id', $user->id)
    ->where('fingerprint', $fingerprint)
    ->exists();

if ($newDevice) {
    // Send email notification
    // Require 2FA verification
}
```

---

### 4. **Audit Log Enhancement** ⭐⭐⭐⭐

**Add sensitive actions logging:**

```php
// In controllers
ActivityLog::create([
    'user_id' => $user->id,
    'action' => 'sensitive_action',
    'description' => 'User accessed sensitive data',
    'properties' => [
        'data_type' => 'financial',
        'records_accessed' => 50,
        'ip' => $request->ip(),
    ],
    'severity' => 'high', // low, medium, high, critical
]);
```

---

### 5. **File Upload Security** ⭐⭐⭐⭐⭐

**Add validation:**
```php
$request->validate([
    'file' => 'required|file|max:2048|mimes:jpg,png,pdf',
]);

// Generate random filename
$filename = Str::random(32) . '.' . $file->getClientOriginalExtension();

// Store outside public root
$path = $file->store('private-uploads', [
    'disk' => 'private',
]);

// Generate temporary signed URL
$url = Storage::disk('private')->temporaryUrl(
    $path, now()->addMinutes(5)
);
```

---

### 6. **API Security** ⭐⭐⭐⭐⭐

**Sanctum token abilities:**
```php
// Create token with specific abilities
$token = $user->createToken('api-token', [
    'recharges:read',
    'recharges:write',
    'transfers:write',
])->plainTextToken;

// In controller
if (!$request->user()->tokenCan('recharges:write')) {
    abort(403, 'Unauthorized action');
}
```

**API rate limiting:**
```php
// In RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

---

### 7. **Database Security** ⭐⭐⭐⭐⭐

**Encrypt sensitive columns:**
```php
// In User model
protected $casts = [
    'phone' => 'encrypted',
    'email' => 'encrypted',
];
```

**Use prepared statements (Eloquent does this automatically):**
```php
// ✅ GOOD - Uses parameter binding
DB::table('users')->where('email', $email)->first();

// ❌ BAD - Vulnerable to SQL injection
DB::select("SELECT * FROM users WHERE email = '$email'");
```

---

### 8. **Security Headers Enhancement** ⭐⭐⭐⭐

**Add to `SecurityHeaders` middleware:**
```php
$response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
$response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
$response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
$response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
```

---

### 9. **Session Hijacking Prevention** ⭐⭐⭐⭐⭐

**Add user agent validation:**
```php
// In AuthenticatedSession middleware
$currentUserAgent = $request->header('User-Agent');
$sessionUserAgent = $request->session()->get('user_agent');

if ($currentUserAgent !== $sessionUserAgent) {
    // Possible session hijacking
    $request->session()->flush();
    Auth::logout();
    return redirect()->route('login')
        ->with('error', 'Session invalide. Veuillez vous reconnecter.');
}

// Store user agent
$request->session()->put('user_agent', $currentUserAgent);
```

---

### 10. **Security Monitoring Dashboard** ⭐⭐⭐⭐

**Create admin page for security overview:**

**Route:**
```php
Route::get('/admin/security-dashboard', [SecurityController::class, 'dashboard'])
    ->name('admin.security.dashboard');
```

**Show:**
- Failed login attempts (last 24h)
- Top blocked IPs
- GeoIP denied accesses
- Suspicious activities
- Active sessions
- Recent password changes
- New devices logins

---

### 11. **Emergency Features** ⭐⭐⭐⭐⭐

**Kill switch for maintenance:**
```php
// In .env
SECURITY_LOCKDOWN=true

// In middleware
if (env('SECURITY_LOCKDOWN', false)) {
    if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
        return response('Maintenance de sécurité en cours', 503);
    }
}
```

**Force logout all sessions:**
```php
// Admin action
User::where('id', $userId)->update(['remember_token' => null]);
// This invalidates all remember me tokens
```

---

### 12. **Input Validation & Sanitization** ⭐⭐⭐⭐⭐

**Always validate:**
```php
$request->validate([
    'email' => 'required|email|max:255',
    'phone' => 'required|string|min:8|max:30',
    'amount' => 'required|numeric|min:0|max:999999',
    'poscode' => 'required|string|alpha_dash|max:50',
]);
```

**Sanitize output in Blade:**
```blade
{{-- Auto-escaped by default --}}
{{ $userInput }}

{{-- For HTML --}}
{!! Purifier::clean($userInput) !!}
```

---

## 📊 Security Checklist

| Feature | Status | Priority |
|---------|--------|----------|
| Firewall | ✅ Done | Critical |
| GeoIP Restriction | ✅ Done | Critical |
| Security Headers | ✅ Done | Critical |
| Rate Limiting | ⚠️ Partial | High |
| Password Expiry | ❌ Todo | Medium |
| Device Fingerprinting | ❌ Todo | High |
| Activity Logging | ✅ Done | Critical |
| File Upload Security | ⚠️ Review | High |
| API Token Abilities | ⚠️ Partial | High |
| Session Hijacking Prevention | ❌ Todo | Critical |
| Security Dashboard | ❌ Todo | Medium |
| Emergency Kill Switch | ❌ Todo | High |

---

## 🎯 Immediate Actions (This Week)

1. ✅ **Enable rate limiting** on all API routes
2. ✅ **Add session hijacking prevention** (user agent check)
3. ✅ **Create security dashboard** for monitoring
4. ✅ **Review file upload security** (KYC images)
5. ✅ **Add emergency kill switch**

---

## 📞 Security Contacts

**For security issues:**
- Email: security@your-domain.com
- Telegram: [Admin Bot]
- Emergency: [Phone number]

---

**Last Updated:** 2026-03-06  
**Version:** 1.0  
**Status:** ✅ Production Ready
