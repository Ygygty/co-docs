# نظام الفهرسة والنسخ الأصلية (Indexer)

ملخص:
- هذا المشروع يقرأ ملفات من مسارات محددة، يحفظ نسخة أصلية لكل إصدار، ويستخرج نصًا للبحث فقط.
- الإصدار المعتمد هو الملف الأصلي البايت-بايت.

التثبيت:
1. PHP 8.3+, Composer.
2. composer install
3. إنشاء .env من .env.example وضبط إعدادات DB و STORAGE.
4. php artisan migrate --seed
5. قم بإعداد قرص التخزين "private" في config/filesystems.php ليتوافق مع storage/app/private أو S3 مع سياسات خصوصية.

التشغيل:
- فحص كامل (CLI): php artisan indexer:scan --source=local-samples
- تشغيل في الخلفية: php artisan queue:work
- جدولة: أضف الأمر في Kernel schedule بدون تداخل.

API أمثلة:
- بدء فحص:
  curl -X POST /api/v1/scan -d '{"source_id":1,"sync":true}' -H 'Content-Type: application/json' -H 'Authorization: Bearer TOKEN'

- جلب الملفات:
  GET /api/v1/files

- تنزيل الإصدار الأصلي:
  GET /api/v1/files/{file}/versions/{version}/original  (تحتاج to auth)

سياسة 200 إصدار:
- كل ملف يحتفظ حتى 200 إصدار (قابل للتعديل عبر config أو scan_source.max_versions).
- عند تجاوز الحد تُحذف أقدم الإصدارات (delete_oldest) ويتم حذف blob إن لم يعد مرتبطًا.

ملاحظات أمنية وأداء:
- نتحقق من realpath وأن الملفات داخل root_path.
- لا نكتب إلى مجلّد المصدر.
- نستخدم streaming للهاش والنسخ.
- نستخدم advisory locks عند إنشاء blobs لتجنّب السباقات.
- لا تعتمد النص المستخرج كنسخة أصلية.

حزم مقترحة خارجية:
- laravel/sanctum (API auth)
- league/flysystem-aws-s3-v3 (S3 storage)
- ext-intl (normalizer)
- php-zip (ZipArchive)
- larastan و phpstan (static analysis)
- pestphp/pest (اختبارات)
