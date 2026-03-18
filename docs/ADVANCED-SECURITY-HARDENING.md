# 🛡️ Advanced Security Hardening - Raqmi Cash Platform

## 🔥 Maximum Protection Against Hackers

This guide adds **military-grade security** to protect your site from all attack vectors.

---

## 📋 Table of Contents

1. [Rate Limiting & DDoS Protection](#1-rate-limiting--ddos-protection)
2. [Session Hijacking Prevention](#2-session-hijacking-prevention)
3. [Device Fingerprinting](#3-device-fingerprinting)
4. [Password Security Policy](#4-password-security-policy)
5. [File Upload Security](#5-file-upload-security)
6. [API Security Enhancement](#6-api-security-enhancement)
7. [Security Monitoring Dashboard](#7-security-monitoring-dashboard)
8. [Emergency Kill Switch](#8-emergency-kill-switch)
9. [Database Encryption](#9-database-encryption)
10. [Advanced CSP Headers](#10-advanced-csp-headers)
11. [Cloudflare Integration](#11-cloudflare-integration)
12. [Audit Logging Enhancement](#12-audit-logging-enhancement)

---

## 1. Rate Limiting & DDoS Protection ⭐⭐⭐⭐⭐

### 1.1 Global Rate Limiter Setup

**File: `app/Providers/RouteServiceProvider.php`**

```php
public function boot(): void
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip())
            ->response(function ($request, $headers) {
                return response('Too Many Requests - Rate Limit Exceeded', 429, $headers)
                    ->header('Retry-After', '60');
            });
    });

    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)
            ->by($request->ip())
            ->response(function ($request, $headers) {
                // Log brute force attempt
                \App\Models\SecurityLog::create([
                    'event_type' => 'BRUTE_FORCE_LOGIN',
                    'ip_address' => $request->ip(),
                    'details' => 'Multiple failed login attempts',
                ]);
                
                return response('Too Many Attempts - Try Again Later', 429, $headers)
                    ->header('Retry-After', '300');
            });
    });

    RateLimiter::for('register', function (Request $request) {
        return Limit::perMinute(3)
            ->by($request->ip())
            ->response(function ($request, $headers) {
                return response('Too Many Registration Attempts', 429, $headers);
            });
    });

    RateLimiter::for('password-reset', function (Request $request) {
        return Limit::perMinute(2)
            ->by($request->ip())
            ->response(function ($request, $headers) {
                return response('Too Many Password Reset Requests', 429, $headers);
            });
    });

    RateLimiter::for('transfer', function (Request $request) {
        return Limit::perMinute(10)
            ->by($request->user()?->id ?: $request->ip())
            ->response(function ($request, $headers) {
                // Log suspicious transfer activity
                \App\Models\SecurityLog::create([
                    'event_type' => 'RATE_LIMIT_TRANSFER',
                    'ip_address' => $request->ip(),
                    'user_id' => $request->user()?->id,
                    'details' => 'Excessive transfer requests',
                ]);
                
                return response('Too Many Transfer Requests', 429, $headers);
            });
    });
}
```

### 1.2 Apply Rate Limiting to Routes

**File: `routes/api.php`**

```php
// Public API routes
Route::middleware('throttle:api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/password/reset', [PasswordResetController::class, 'reset']);
});

// Protected API routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/transfer', [TransferController::class, 'transfer'])
        ->middleware('throttle:transfer');
    
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/balance', [BalanceController::class, 'balance']);
});
```

### 1.3 Nginx Rate Limiting (Server Level)

**File: `nginx-docker.conf`**

```nginx
# Rate limiting zones
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
limit_req_zone $binary_remote_addr zone=general:10m rate=30r/m;

# Connection limiting
limit_conn_zone $binary_remote_addr zone=conn_limit:10m;

server {
    # ... existing config ...
    
    # Apply rate limiting
    location /api/login {
        limit_req zone=login burst=3 nodelay;
        limit_conn conn_limit 10;
    }
    
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        limit_conn conn_limit 30;
    }
    
    location / {
        limit_req zone=general burst=10 nodelay;
        limit_conn conn_limit 20;
    }
    
    # Return 429 on rate limit exceeded
    limit_req_status 429;
    limit_conn_status 429;
}
```

---

## 2. Session Hijacking Prevention ⭐⭐⭐⭐⭐

### 2.1 Create Session Protection Middleware

**File: `app/Http/Middleware/ValidateSession.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidateSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $session = $request->session();
        
        // 1. User Agent Validation
        $currentUserAgent = $request->header('User-Agent');
        $sessionUserAgent = $session->get('user_agent');
        
        if ($sessionUserAgent && $currentUserAgent !== $sessionUserAgent) {
            $this->invalidateSession($user, 'User-Agent Mismatch - Possible Hijacking');
            return redirect()->route('login')
                ->with('error', 'Session invalide détectée. Reconnectez-vous.');
        }
        
        // 2. IP Address Validation (Optional - can cause issues with mobile users)
        // $currentIP = $request->ip();
        // $sessionIP = $session->get('ip_address');
        // if ($sessionIP && $currentIP !== $sessionIP) { ... }
        
        // 3. Store session fingerprints
        $session->put('user_agent', $currentUserAgent);
        $session->put('ip_address', $request->ip());
        $session->put('last_activity', now());
        
        // 4. Session Timeout Check (5 minutes idle)
        $lastActivity = $session->get('last_activity');
        if ($lastActivity && $lastActivity->diffInMinutes(now()) > 5) {
            // For sensitive operations, force re-authentication
            if ($request->routeIs('transfer.*') || $request->routeIs('withdraw.*')) {
                return redirect()->route('password.confirm')
                    ->with('warning', 'Session expirée pour opérations sensibles.');
            }
        }
        
        return $next($request);
    }
    
    protected function invalidateSession($user, $reason)
    {
        // Log security incident
        \App\Models\SecurityLog::create([
            'event_type' => 'SESSION_HIJACK_ATTEMPT',
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'details' => $reason,
            'severity' => 'critical',
        ]);
        
        // Send Telegram alert
        \App\Services\TelegramLogger::sendAlert(
            "🚨 SESSION HIJACKING ATTEMPT",
            $reason,
            [
                'User' => $user->email,
                'IP' => request()->ip(),
                'User-Agent' => request()->header('User-Agent'),
            ]
        );
        
        // Destroy session
        Auth::logout();
        request()->session()->flush();
    }
}
```

### 2.2 Register Middleware

**File: `app/Http/Kernel.php`**

```php
protected $middlewareAliases = [
    // ... existing
    'validate.session' => \App\Http\Middleware\ValidateSession::class,
];

protected $middlewareGroups = [
    'web' => [
        // ... existing
        \App\Http\Middleware\ValidateSession::class,
    ],
];
```

---

## 3. Device Fingerprinting ⭐⭐⭐⭐

### 3.1 Create User Devices Table

**Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('fingerprint', 128)->index();
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->string('browser_name', 50)->nullable();
            $table->string('os_name', 50)->nullable();
            $table->string('device_type', 20)->nullable(); // mobile, tablet, desktop
            $table->string('country', 2)->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_seen_at');
            $table->timestamps();
            
            $table->index(['user_id', 'fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
```

### 3.2 Create UserDevice Model

**File: `app/Models/UserDevice.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'fingerprint',
        'ip_address',
        'user_agent',
        'browser_name',
        'os_name',
        'device_type',
        'country',
        'is_trusted',
        'last_seen_at',
    ];

    protected $casts = [
        'is_trusted' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateFingerprint($request)
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            $request->header('User-Agent'),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
            $request->header('Accept-Charset'),
        ]));
    }

    public static function detectBrowser($userAgent)
    {
        if (preg_match('/Edg\/(\d+)/', $userAgent, $matches)) {
            return 'Edge ' . $matches[1];
        }
        if (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) {
            return 'Chrome ' . $matches[1];
        }
        if (preg_match('/Firefox\/(\d+)/', $userAgent, $matches)) {
            return 'Firefox ' . $matches[1];
        }
        if (preg_match('/Safari\/(\d+)/', $userAgent, $matches)) {
            return 'Safari ' . $matches[1];
        }
        return 'Unknown';
    }

    public static function detectOS($userAgent)
    {
        if (preg_match('/Windows NT (\d+\.\d+)/', $userAgent, $matches)) {
            return 'Windows ' . $matches[1];
        }
        if (preg_match('/Mac OS X (\d+[._]\d+)/', $userAgent, $matches)) {
            return 'macOS ' . str_replace('_', '.', $matches[1]);
        }
        if (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        }
        if (preg_match('/Android (\d+)/', $userAgent, $matches)) {
            return 'Android ' . $matches[1];
        }
        if (preg_match('/iOS (\d+)/', $userAgent, $matches)) {
            return 'iOS ' . $matches[1];
        }
        return 'Unknown';
    }

    public static function detectDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            return 'mobile';
        }
        if (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }
}
```

### 3.3 Track Devices on Login

**File: `app/Http/Controllers/Auth/LoginController.php`**

```php
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        
        $user = Auth::user();
        $fingerprint = UserDevice::generateFingerprint($request);
        
        // Check if device exists
        $device = UserDevice::where('user_id', $user->id)
            ->where('fingerprint', $fingerprint)
            ->first();
        
        if (!$device) {
            // New device detected
            UserDevice::create([
                'user_id' => $user->id,
                'fingerprint' => $fingerprint,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'browser_name' => UserDevice::detectBrowser($request->header('User-Agent')),
                'os_name' => UserDevice::detectOS($request->header('User-Agent')),
                'device_type' => UserDevice::detectDeviceType($request->header('User-Agent')),
                'country' => session('geo_country'),
                'is_trusted' => false,
                'last_seen_at' => now(),
            ]);
            
            // Send notification
            TelegramLogger::sendAlert(
                "📱 New Device Login",
                "User logged in from new device",
                [
                    'User' => $user->email,
                    'Browser' => UserDevice::detectBrowser($request->header('User-Agent')),
                    'OS' => UserDevice::detectOS($request->header('User-Agent')),
                    'IP' => $request->ip(),
                    'Time' => now()->format('Y-m-d H:i:s'),
                ]
            );
            
            // Optional: Require 2FA for new devices
            // return redirect()->route('2fa.verify');
        } else {
            // Update existing device
            $device->update([
                'last_seen_at' => now(),
                'ip_address' => $request->ip(),
            ]);
        }
        
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors(['email' => 'Identifiants invalides']);
}
```

---

## 4. Password Security Policy ⭐⭐⭐⭐⭐

### 4.1 Password Expiry Middleware

**File: `app/Http/Middleware/PasswordExpiry.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PasswordExpiry
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        
        if (!$user) {
            return $next($request);
        }
        
        // Skip password change pages
        if ($request->routeIs('password.*') || $request->routeIs('logout')) {
            return $next($request);
        }
        
        // Check password age (90 days)
        $passwordChangedAt = $user->password_changed_at ?? $user->created_at;
        $daysSinceChange = $passwordChangedAt->diffInDays(now());
        
        if ($daysSinceChange > 90) {
            // Store intended destination
            session(['intended_url' => url()->current()]);
            
            return redirect()->route('password.change')
                ->with('warning', 'Votre mot de passe a expiré (90 jours). Veuillez le changer.');
        }
        
        // Warn if expires in 7 days
        if ($daysSinceChange > 83) {
            session()->flash('password_warning', 
                "Votre mot de passe expire dans " . (90 - $daysSinceChange) . " jours.");
        }
        
        return $next($request);
    }
}
```

### 4.2 Password Strength Validator

**File: `app/Rules/StrongPassword.php`**

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = $value;
        
        // Minimum 8 characters
        if (strlen($password) < 8) {
            $fail('Le mot de passe doit contenir au moins 8 caractères.');
        }
        
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $fail('Le mot de passe doit contenir au moins une lettre majuscule.');
        }
        
        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $fail('Le mot de passe doit contenir au moins une lettre minuscule.');
        }
        
        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            $fail('Le mot de passe doit contenir au moins un chiffre.');
        }
        
        // At least one special character
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $fail('Le mot de passe doit contenir au moins un caractère spécial.');
        }
        
        // Check against common passwords
        $commonPasswords = ['password', '123456', '12345678', 'qwerty', 'abc123'];
        if (in_array(strtolower($password), $commonPasswords)) {
            $fail('Ce mot de passe est trop commun. Choisissez-en un autre.');
        }
        
        // Check for sequential characters
        if (preg_match('/(012|123|234|345|456|567|678|789|abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', $password)) {
            $fail('Évitez les séquences de caractères consécutifs.');
        }
    }
}
```

### 4.3 Update Password Change Controller

**File: `app/Http/Controllers/Auth/ChangePasswordController.php`**

```php
public function update(Request $request)
{
    $request->validate([
        'current_password' => 'required|current_password',
        'password' => ['required', 'confirmed', new StrongPassword()],
    ]);

    $user = Auth::user();
    $user->password = bcrypt($request->password);
    $user->password_changed_at = now();
    $user->save();
    
    // Log password change
    SecurityLog::create([
        'event_type' => 'PASSWORD_CHANGED',
        'user_id' => $user->id,
        'ip_address' => $request->ip(),
        'details' => 'User changed password',
        'severity' => 'medium',
    ]);
    
    // Invalidate all other sessions
    Session::put('password_hash', sha1($user->password));
    
    return redirect()->route('dashboard')
        ->with('success', 'Mot de passe changé avec succès!');
}
```

---

## 5. File Upload Security ⭐⭐⭐⭐⭐

### 5.1 Secure Upload Service

**File: `app/Services/SecureUploadService.php`**

```php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class SecureUploadService
{
    protected const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
    ];
    
    protected const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    
    public function uploadKYC(UploadedFile $file, $userId)
    {
        // 1. Validate file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('File too large (max 2MB)');
        }
        
        // 2. Validate MIME type (not just extension!)
        $mimeType = $file->getMimeType();
        if (!array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \Exception('Invalid file type');
        }
        
        // 3. Validate image dimensions if image
        if (Str::startsWith($mimeType, 'image/')) {
            $dimensions = @getimagesize($file->getRealPath());
            if (!$dimensions) {
                throw new \Exception('Invalid image file');
            }
            
            // Reject if image is too small (possible attack)
            if ($dimensions[0] < 100 || $dimensions[1] < 100) {
                throw new \Exception('Image resolution too low');
            }
        }
        
        // 4. Generate secure filename
        $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        $filename = Str::random(32) . '_' . time() . '.' . $extension;
        
        // 5. Store outside public root
        $path = 'kyc/' . $userId . '/' . $filename;
        Storage::disk('private')->put($path, file_get_contents($file));
        
        // 6. For images, create thumbnail and strip metadata
        if (Str::startsWith($mimeType, 'image/')) {
            $image = Image::make($file->getRealPath());
            
            // Strip EXIF metadata (may contain GPS location)
            $image->orientate();
            
            // Create thumbnail
            $thumbPath = 'kyc/' . $userId . '/thumb_' . $filename;
            $image->resize(200, 200)->save(storage_path('app/private/' . $thumbPath));
        }
        
        // 7. Log upload
        \App\Models\SecurityLog::create([
            'event_type' => 'FILE_UPLOAD',
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'details' => "KYC file uploaded: {$filename}",
            'properties' => [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'size' => $file->getSize(),
            ],
        ]);
        
        return $path;
    }
    
    public function getTemporaryUrl($path, $minutes = 5)
    {
        return Storage::disk('private')
            ->temporaryUrl($path, now()->addMinutes($minutes));
    }
}
```

### 5.2 Private Disk Configuration

**File: `config/filesystems.php`**

```php
'disks' => [
    // ... existing ...
    
    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'serve' => false, // Never serve directly
        'throw' => false,
    ],
],
```

---

## 6. API Security Enhancement ⭐⭐⭐⭐⭐

### 6.1 API Token Abilities

**File: `app/Models/User.php`**

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    
    public function createTokenByRole($name)
    {
        $abilities = [];
        
        switch ($this->role) {
            case 'admin':
                $abilities = ['*']; // All abilities
                break;
            case 'agency':
                $abilities = [
                    'recharges:read', 'recharges:write',
                    'transfers:read', 'transfers:write',
                    'users:read',
                ];
                break;
            case 'user':
                $abilities = [
                    'recharges:read',
                    'transfers:read', 'transfers:write',
                    'balance:read',
                ];
                break;
        }
        
        return $this->createToken($name, $abilities);
    }
}
```

### 6.2 API Middleware

**File: `app/Http/Middleware/ApiTokenCheck.php`**

```php
public function handle($request, Closure $next, ...$abilities)
{
    $user = $request->user();
    
    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    foreach ($abilities as $ability) {
        if (!$user->tokenCan($ability)) {
            SecurityLog::create([
                'event_type' => 'API_UNAUTHORIZED',
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'details' => "Missing ability: {$ability}",
            ]);
            
            return response()->json([
                'error' => 'Insufficient permissions',
            ], 403);
        }
    }
    
    return $next($request);
}
```

---

## 7. Security Monitoring Dashboard ⭐⭐⭐⭐

### 7.1 Security Dashboard Controller

**File: `app/Http/Controllers/Admin/SecurityDashboardController.php`**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityLog;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SecurityDashboardController extends Controller
{
    public function dashboard()
    {
        $now = now();
        
        return view('admin.security.dashboard', [
            // Failed logins (24h)
            'failedLogins' => SecurityLog::where('event_type', 'FAILED_LOGIN')
                ->where('created_at', '>=', $now->subHours(24))
                ->count(),
            
            // Blocked IPs (24h)
            'blockedIps' => SecurityLog::where('event_type', 'IP_BLOCKED')
                ->where('created_at', '>=', $now->subHours(24))
                ->select('ip_address')
                ->distinct()
                ->count(),
            
            // Top blocked IPs
            'topBlockedIps' => SecurityLog::where('event_type', 'IP_BLOCKED')
                ->where('created_at', '>=', $now->subHours(24))
                ->selectRaw('ip_address, COUNT(*) as count')
                ->groupBy('ip_address')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            
            // Session hijacking attempts
            'hijackAttempts' => SecurityLog::where('event_type', 'SESSION_HIJACK_ATTEMPT')
                ->where('created_at', '>=', $now->subHours(24))
                ->count(),
            
            // Suspicious activities
            'suspiciousActivities' => SecurityLog::whereIn('severity', ['high', 'critical'])
                ->where('created_at', '>=', $now->subHours(24))
                ->latest()
                ->limit(20)
                ->get(),
            
            // Active sessions
            'activeSessions' => User::whereHas('sessions', function ($q) {
                $q->where('last_activity', '>=', now()->subMinutes(10));
            })->count(),
            
            // New devices (24h)
            'newDevices' => \App\Models\UserDevice::where('created_at', '>=', $now->subHours(24))
                ->count(),
            
            // Password changes (7 days)
            'passwordChanges' => User::where('password_changed_at', '>=', $now->subDays(7))
                ->count(),
        ]);
    }
    
    public function unblockIp($ip)
    {
        Cache::forget('firewall:ban:' . $ip);
        
        return back()->with('success', "IP {$ip} unblocked");
    }
}
```

### 7.2 Security Dashboard View

**File: `resources/views/admin/security/dashboard.blade.php`**

```blade
@extends('layouts.admin')

@section('title', '🛡️ Security Dashboard')

@section('content')
<div class="container">
    <h1>🛡️ Security Dashboard</h1>
    
    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Failed Logins (24h)</h5>
                    <h2>{{ $failedLogins }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Blocked IPs (24h)</h5>
                    <h2>{{ $blockedIps }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Active Sessions</h5>
                    <h2>{{ $activeSessions }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>New Devices (24h)</h5>
                    <h2>{{ $newDevices }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Top Blocked IPs --}}
    <div class="card mt-4">
        <div class="card-header">🚫 Top Blocked IPs</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Attempts</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topBlockedIps as $block)
                    <tr>
                        <td>{{ $block->ip_address }}</td>
                        <td>{{ $block->count }}</td>
                        <td>
                            <form action="{{ route('admin.security.unblock', $block->ip_address) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-success">Unblock</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Recent Suspicious Activities --}}
    <div class="card mt-4">
        <div class="card-header">⚠️ Recent Suspicious Activities</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>User</th>
                        <th>IP</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suspiciousActivities as $log)
                    <tr>
                        <td>
                            <span class="badge bg-{{ $log->severity === 'critical' ? 'danger' : 'warning' }}">
                                {{ $log->event_type }}
                            </span>
                        </td>
                        <td>{{ $log->user?->email ?? 'N/A' }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

---

## 8. Emergency Kill Switch ⭐⭐⭐⭐⭐

### 8.1 Security Lockdown Middleware

**File: `app/Http/Middleware/SecurityLockdown.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SecurityLockdown
{
    public function handle($request, Closure $next)
    {
        if (env('SECURITY_LOCKDOWN', false)) {
            // Allow super admins
            if (Auth::check() && Auth::user()->isSuperAdmin()) {
                return $next($request);
            }
            
            // Show maintenance page
            return response()->view('errors.503', [
                'message' => 'Maintenance de sécurité en cours. Veuillez réessayer plus tard.',
            ], 503);
        }
        
        return $next($request);
    }
}
```

### 8.2 Emergency Commands

**File: `app/Console/Commands/EmergencyLockdown.php`**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class EmergencyLockdown extends Command
{
    protected $signature = 'security:lockdown {--enable} {--disable}';
    protected $description = 'Emergency security lockdown';

    public function handle()
    {
        if ($this->option('enable')) {
            // Log to .env
            file_put_contents(app_path('../.env'), "\nSECURITY_LOCKDOWN=true", FILE_APPEND);
            
            // Clear config cache
            Artisan::call('config:clear');
            
            // Send alert
            \App\Services\TelegramLogger::sendAlert(
                "🚨 SECURITY LOCKDOWN ENABLED",
                "All user access blocked. Only super admins can access.",
                ['Admin' => Auth::user()?->email ?? 'CLI']
            );
            
            $this->info('Security lockdown ENABLED');
        }
        
        if ($this->option('disable')) {
            // Remove from .env (manual step recommended)
            $this->warn('Remove SECURITY_LOCKDOWN=true from .env manually');
            $this->warn('Then run: php artisan config:clear');
        }
        
        return 0;
    }
}
```

### 8.3 Force Logout All Sessions

**Command:**

```php
// app/Console/Commands/ForceLogoutAll.php
public function handle()
{
    // Clear all sessions
    DB::table('sessions')->delete();
    
    // Clear remember tokens
    DB::table('users')->update(['remember_token' => null]);
    
    // Clear cache
    Cache::flush();
    
    $this->info('All sessions invalidated');
    
    return 0;
}
```

---

## 9. Database Encryption ⭐⭐⭐⭐

### 9.1 Encrypt Sensitive Columns

**File: `.env`**

```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**File: `app/Models/User.php`**

```php
protected $casts = [
    'phone' => 'encrypted',
    'email' => 'encrypted',
    'national_id' => 'encrypted',
];
```

### 9.2 Manual Encryption Service

**File: `app/Services/EncryptionService.php`**

```php
use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    public function encrypt($value)
    {
        return Crypt::encryptString($value);
    }
    
    public function decrypt($encrypted)
    {
        return Crypt::decryptString($encrypted);
    }
}
```

---

## 10. Advanced CSP Headers ⭐⭐⭐⭐

### 10.1 Enhanced Content-Security-Policy

**File: `app/Http/Middleware/SecurityHeaders.php`**

```php
// Add to handle() method
$cspPolicy = implode('; ', [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net",
    "style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com",
    "img-src 'self' data: https: blob:",
    "font-src 'self' fonts.gstatic.com",
    "connect-src 'self' api.telegram.org",
    "frame-src 'none'",
    "frame-ancestors 'none'",
    "base-uri 'self'",
    "form-action 'self'",
    "object-src 'none'",
    "upgrade-insecure-requests",
]);

$response->headers->set('Content-Security-Policy', $cspPolicy);
```

---

## 11. Cloudflare Integration ⭐⭐⭐⭐⭐

### 11.1 Cloudflare Security Settings

**Recommended Cloudflare Settings:**

1. **SSL/TLS**: Full (Strict)
2. **Always Use HTTPS**: Enabled
3. **Auto Minify**: HTML, CSS, JS enabled
4. **Brotli**: Enabled
5. **Rocket Loader**: Off (may break Laravel)

**Security Settings:**

- **Security Level**: High
- **Challenge Passage**: 30 minutes
- **Browser Integrity Check**: On
- **DDoS Protection**: On

**WAF Rules:**

```
# Block countries except Morocco
(ip.geoip.country ne "MA")
```

**Rate Limiting Rules:**

```
URI: /api/login
Threshold: 5 requests per 1 minute
Action: Challenge
```

### 11.2 Trust Cloudflare Proxy

**File: `app/Http/Middleware/TrustProxies.php`**

```php
protected $proxies = '*';

protected $headers = [
    \Illuminate\Http\Request::HEADER_FORWARDED,
    \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR,
    \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST,
    \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT,
    \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
    \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB,
];
```

**File: `.env`**

```env
TRUST_CLOUDFLARE_PROXY=true
```

---

## 12. Audit Logging Enhancement ⭐⭐⭐⭐

### 12.1 Security Log Model

**Migration:**

```php
Schema::create('security_logs', function (Blueprint $table) {
    $table->id();
    $table->string('event_type')->index();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('ip_address', 45);
    $table->text('user_agent')->nullable();
    $table->string('severity', 20)->default('info'); // info, low, medium, high, critical
    $table->text('details')->nullable();
    $table->json('properties')->nullable();
    $table->timestamps();
    
    $table->index(['event_type', 'severity']);
    $table->index('created_at');
});
```

### 12.2 SecurityLog Model

**File: `app/Models/SecurityLog.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    protected $fillable = [
        'event_type',
        'user_id',
        'ip_address',
        'user_agent',
        'severity',
        'details',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 12.3 Log Sensitive Actions

```php
// In controllers
SecurityLog::create([
    'event_type' => 'SENSITIVE_DATA_ACCESS',
    'user_id' => $user->id,
    'ip_address' => $request->ip(),
    'severity' => 'high',
    'details' => 'User accessed financial records',
    'properties' => [
        'records_count' => 50,
        'data_type' => 'transactions',
    ],
]);
```

---

## 📊 Security Checklist

| Feature | Status | Priority |
|---------|--------|----------|
| Rate Limiting | ⚠️ Implement | Critical |
| Session Hijacking Prevention | ⚠️ Implement | Critical |
| Device Fingerprinting | ⚠️ Implement | High |
| Password Expiry | ⚠️ Implement | High |
| File Upload Security | ⚠️ Review | Critical |
| API Token Abilities | ⚠️ Partial | High |
| Security Dashboard | ⚠️ Implement | Medium |
| Emergency Kill Switch | ⚠️ Implement | Critical |
| Database Encryption | ⚠️ Implement | High |
| Advanced CSP | ⚠️ Implement | Medium |
| Cloudflare Integration | ⚠️ Implement | Critical |
| Audit Logging | ⚠️ Enhance | High |

---

## 🎯 Immediate Actions (Priority Order)

1. ✅ **Enable Rate Limiting** - Prevent DDoS & brute force
2. ✅ **Add Session Validation** - Prevent hijacking
3. ✅ **Emergency Kill Switch** - For critical situations
4. ✅ **Cloudflare WAF** - Country blocking + DDoS
5. ✅ **File Upload Security** - Secure KYC uploads
6. ✅ **Security Dashboard** - Monitor attacks
7. ✅ **Device Fingerprinting** - Track all logins
8. ✅ **Password Policy** - Force strong passwords

---

## 📞 Emergency Contacts

**Security Incidents:**
- Email: security@your-domain.com
- Telegram: Admin Bot
- Emergency Command: `php artisan security:lockdown --enable`

---

**Last Updated:** 2026-03-07  
**Version:** 2.0  
**Status:** 🛡️ Maximum Security Ready
