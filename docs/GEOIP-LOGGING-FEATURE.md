# GeoIP Logging & Monitoring - تحسينات الأمان

## 📋 نظرة عامة

تم إضافة نظام متقدم لتسجيل وتحليل محاولات الوصول الجغرافية (GeoIP) لتحسين أمان التطبيق ومراقبة الوصول من المغرب فقط.

---

## ✨ الميزات الجديدة

### 1. **تسجيل شامل لـ GeoIP**
- تسجيل كل محاولات الوصول مع الدولة المكتشفة
- تتبع مصادر البيانات (Cloudflare, ip-api.com, ipapi.co)
- تسجيل حالات رفض الوصول (Access Denied)

### 2. **لوحة تحكم للإدارة**
- إحصائيات شاملة للوصول
- أهم IPs المرفوضة
- أهم الدول
- فلترة متقدمة (حسب الدولة، IP، الحالة)

### 3. **تحسين دقة GeoIP**
- 3 مصادر احتياطية:
  1. Cloudflare `CF-IPCountry` header (الأولوية القصوى)
  2. ip-api.com API (مجاني)
  3. ipapi.co API (fallback)
- تخزين مؤقت (cache) لمدة 6 ساعات

### 4. **تنظيف السجلات**
- إمكانية حذف السجلات القديمة
- تحديد عدد الأيام من لوحة التحكم

---

## 📁 الملفات المضافة/المعدلة

### ملفات جديدة:
```
app/Models/GeoIpLog.php                          - Model جديد
app/Http/Controllers/Admin/GeoIpLogController.php - Admin Controller
resources/views/admin/geoip_logs/index.blade.php  - Admin View
database/migrations/2026_03_06_223540_create_geoip_logs_table.php
```

### ملفات معدلة:
```
app/Http/Middleware/AllowMoroccoOnly.php  - تحسين GeoIP + Logging
routes/web.php                            - إضافة routes جديدة
resources/views/layouts/admin.blade.php   - إضافة menu link
```

---

## 🗄️ قاعدة البيانات

### جدول `geoip_logs`:

| العمود | النوع | الوصف |
|--------|-------|--------|
| id | bigint | Primary key |
| ip | varchar(45) | IP address |
| country_code | varchar(2) | الدولة من Cloudflare |
| fallback_country_code | varchar(2) | الدولة من API fallback |
| path | varchar(500) | المسار المطلوب |
| url | varchar(2000) | URL الكامل |
| user_agent | text | User agent |
| source | varchar(50) | مصدر البيانات (ip-api, ipapi.co, cloudflare) |
| access_denied | boolean | هل تم رفض الوصول؟ |
| extra_data | json | بيانات إضافية |
| created_at | timestamp | وقت التسجيل |

---

## 🎯 الاستخدام

### من لوحة التحكم:

1. **الدخول للصفحة:**
   - Admin Dashboard → GeoIP Logs
   - أو مباشرة: `/admin/geoip-logs`

2. **الفلترة:**
   - **All Logs:** كل السجلات
   - **Denied Only:** الوصول المرفوض فقط
   - **Unknown Country:** الحالات اللي الدولة مجهولة
   - **Morocco Only:** الوصول من المغرب فقط

3. **البحث:**
   - حسب الدولة (مثال: MA, US, FR)
   - حسب IP address

4. **تنظيف السجلات:**
   - حدد عدد الأيام (مثال: 30)
   - اضغط "Delete Old Logs"

---

## 📊 الإحصائيات

لوحة التحكم كتعرض:

| الإحصائية | الوصف |
|-----------|--------|
| Total Logs | مجموع السجلات |
| Denied Access | الوصول المرفوض |
| Unknown Country | الدولة مجهولة |
| Morocco (MA) | الوصول من المغرب |
| Today | سجلات اليوم |
| Denied Today | المرفوض اليوم |

---

## 🔧 التكوين

### إضافة Cloudflare (موصى به):

1. سجل فـ [Cloudflare](https://cloudflare.com)
2. غير Nameservers للدومين
3. فعل Proxy لـ your-domain.com

**الفائدة:**
- GeoIP دقيق ومجاني
- حماية DDoS
- CDN مجاني

### متغيرات البيئة (اختياري):

```env
# GeoIP Configuration
GEO_ALLOWED_COUNTRIES=MA
GEO_REQUIRE_COUNTRY_HEADER=true
GEO_ALLOWLIST_IPS=127.0.0.1,::1

# Cloudflare (if using)
TRUST_CLOUDFLARE_PROXY=true
```

---

## 📈 التحليلات

### أهم التقارير:

1. **Top Denied IPs:**
   - IPs اللي كتحاول الدخول بشكل متكرر
   - مفيد للـ Firewall blocking

2. **Top Countries:**
   - توزيع الدول اللي كتحاول الدخول
   - كشف الأنماط المشبوهة

3. **Unknown Country Trends:**
   - IPs اللي الـ GeoIP فشل في تحديدها
   - VPNs / Data Centers / Proxies

---

## 🔒 الأمان

### حماية البيانات:
- السجلات كاتحفظ لمدة غير محدودة (يمكن تنظيفها يدوياً)
- الوصول للصفحة محمي بـ `admin` middleware
- logging كيتم بشكل async وماكايعطلش التطبيق

### توصيات:
1. دورياً نقّي السجلات القديمة (> 90 يوم)
2. راقب Top Denied IPs وحظر الـ IPs الخطرة
3. فعل Cloudflare لـ GeoIP أدق

---

## 🐛 استكشاف الأخطاء

### المشكلة: السجلات ماكاتسجلش

**الحل:**
```bash
# تأكد من الصلاحيات
sudo chown -R mouttaki:www-data storage/
sudo chmod -R 775 storage/

# تأكد من الهجرة
php artisan migrate:status
```

### المشكلة: Unknown Country كثير

**الأسباب المحتملة:**
- IPs من VPNs/Data centers
- Cloudflare غير مفعل
- API rate limits

**الحل:**
1. فعل Cloudflare
2. زد الـ IPs المعروفة فـ `GEO_ALLOWLIST_IPS`

---

## 📝 API Endpoints

### للمطورين:

```php
// تسجيل GeoIP check
\App\Models\GeoIpLog::log(
    ip: '1.2.3.4',
    countryCode: 'MA',
    fallbackCountryCode: null,
    source: 'cloudflare',
    accessDenied: false,
    path: '/dashboard',
    url: 'https://your-domain.com/dashboard',
    userAgent: 'Mozilla/5.0...',
    extraData: ['custom' => 'data']
);

// Query scopes
GeoIpLog::denied()->latest()->get();        // الوصول المرفوض
GeoIpLog::unknown()->get();                  // الدولة مجهولة
GeoIpLog::forIp('1.2.3.4')->get();           // IP محدد
GeoIpLog::forCountry('US')->get();           // دولة محددة
```

---

## 🎉 الخلاصة

هاد التحسينات كتعطي:

✅ رؤية شاملة للوصول الجغرافي
✅ كشف مبكر للهجمات المشبوهة
✅ بيانات للتحليل واتخاذ القرارات
✅ لوحة تحكم سهلة للإدارة
✅ نظام مرن وقابل للتوسع

---

**آخر تحديث:** 2026-03-06  
**المطور:** AI Assistant  
**الحالة:** ✅ جاهز للإنتاج
