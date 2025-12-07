# تحديث الباكدج على Packagist

## الوضع الحالي

الباكدج `devmahmoudmustafa/laravel-imagekit` موجود بالفعل على Packagist.

## الحلول

### الحل 1: تحديث الباكدج الموجود (إذا كنت المالك)

إذا كنت مالك الباكدج على Packagist:

1. **اذهب إلى صفحة الباكدج:**
   https://packagist.org/packages/devmahmoudmustafa/laravel-imagekit

2. **تأكد من أن المستودع مرتبط بشكل صحيح:**
   - يجب أن يكون المستودع: `https://github.com/devmahmoudmustafa/laravel-imagekit`

3. **تحديث الباكدج:**
   - اضغط على زر "Update" في صفحة الباكدج
   - أو انتظر التحديث التلقائي (إذا كان webhook مفعّل)
   - أو استخدم API: https://packagist.org/packages/devmahmoudmustafa/laravel-imagekit/update

4. **تأكد من أن tag v1.0.0 موجود على GitHub:**
   ```bash
   git push origin v1.0.0
   ```

### الحل 2: تغيير اسم الباكدج (إذا لم تكن المالك)

إذا لم تكن مالك الباكدج الموجود، يجب تغيير الاسم:

1. **تغيير الاسم في composer.json:**
   ```json
   "name": "devmahmoudmustafa/laravel-imagekit-v2"
   ```
   أو
   ```json
   "name": "devmahmoudmustafa/laravel-imagekit"
   ```

2. **تحديث جميع المراجع في الكود**

3. **إنشاء مستودع جديد على GitHub**

4. **نشر الباكدج الجديد**

## الخطوات الموصى بها

بما أن الباكدج موجود بالفعل، الأفضل هو:

1. ✅ تأكد من أن الكود محدث على GitHub
2. ✅ تأكد من وجود tag v1.0.0
3. ✅ اذهب إلى صفحة الباكدج على Packagist
4. ✅ اضغط "Update" لتحديث الباكدج
5. ✅ أو استخدم API للتحديث

## رابط التحديث المباشر

https://packagist.org/packages/devmahmoudmustafa/laravel-imagekit/update

---

**ملاحظة:** إذا كنت تريد الاحتفاظ بنفس الاسم، يجب أن تكون مالك الباكدج الموجود على Packagist.

