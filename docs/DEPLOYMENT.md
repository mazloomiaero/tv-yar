# استقرار و Git — TV Yar (`field-service-system`)

راهنمای پایه برای تکرار همین فرایند: **پوش به GitHub** و **اتصال / استقرار روی هاست**.  
این فایل مرجع عملیاتی است؛ قوانین دامنه و اسکیما هنوز در `docs/ARCHITECTURE.md` و `docs/DATABASE.md` هستند.

> هیچ commit / push / دستور روی سرور بدون درخواست صریح مالک («پوش کن»، «منتشر کن»، «روی هاست بگذار»).

---

## ۱) محیط‌ها

| مورد | مقدار |
| --- | --- |
| پروژه محلی | `D:\2-My Projects\manage-service\field-service-system` |
| ریپازیتوری | GitHub خصوصی `mazloomiaero/tv-yar` — شاخهٔ `main` |
| Remote پیشنهادی | `https://github.com/mazloomiaero/tv-yar.git` |
| توسعه محلی | Windows + Cursor؛ `php artisan serve`؛ `.env` فقط محلی |
| هاست هدف | cPanel / هاست اشتراکی — کاربر SSH `tvyarcom` |
| سرور | `195.254.165.9` — پورت SSH `1396` — هاست‌نیم نمونه: `nikan.pws-dns.net` |
| PHP هاست | ۸.۳+ (تأیید شده: ۸.۳.۳۶) + Composer |
| Document Root وب | معمولاً `~/public_html` (یا دامنهٔ جدا در `~/domains/...`) |
| هستهٔ Laravel روی هاست (هدف) | خارج از وب‌روت، مثلاً `~/tv-yar` یا `~/field-service-system` |
| دامنهٔ نهایی | `https://app.tv-yar.com` |
| Document Root دامنه | `/home/tvyarcom/crm` (پوشهٔ cPanel به نام `crm`) |
| هسته روی سرور | `/home/tvyarcom/tv-yar` |
| دیتابیس پروداکشن | MySQL `tvyarcom_javad` (رمز فقط در `.env` سرور) |
| PHP وب/CLI | `ea-php84` (۸.۴) — اکستنشن `iconv` روی این هاست غیرفعال است |
| پرش SSH | فقط از IP `46.8.103.130` به هاست دامنه مجاز است |

---

## ۲) قوانین امنیتی (غیرقابل مذاکره)

1. `.env` هرگز commit / push نمی‌شود. فقط `.env.example` در Git است.
2. رمزها، کلید خصوصی SSH، توکن GitHub، dump دیتابیس واقعی در چت/ریپو/این سند نمی‌آیند.
3. روی پروداکشن: `APP_ENV=production` و `APP_DEBUG=false`.
4. `migrate --force` فقط بعد از خواندن migration جدید. هرگز `migrate:fresh` / `db:wipe` / seed کامل روی دادهٔ واقعی بدون تأیید صریح.
5. `composer install --no-dev --optimize-autoloader` (نه `composer update` روی سرور).
6. اگر Document Root از `public/` لاراول جدا است، `index.php` و assetهای وب‌روت را با الگوی cPanel نگه دارید؛ هسته بیرون از وب بماند (مثل BackOffice).
7. تاریخچهٔ مالی و درخواست خدمت را برای «تمیزکاری» فیزیکی پاک نکنید.

---

## ۳) Git — پوش روزمره به ریپو

### ۳.۱ پیش‌نیاز یک‌بار

```powershell
cd "D:\2-My Projects\manage-service\field-service-system"
git remote -v
# باید origin به tv-yar اشاره کند:
# origin  https://github.com/mazloomiaero/tv-yar.git
```

اگر remote نبود:

```powershell
git remote add origin https://github.com/mazloomiaero/tv-yar.git
```

نکته: روی این ماشین، **HTTPS برای push پایدارتر از SSH داخل Git for Windows** بوده است. اگر SSH خواستید، اول با `ssh -T git@github.com` تست کنید؛ در صورت خطا همان HTTPS بماند.

### ۳.۲ چک امن قبل از stage

```powershell
git status --short
```

هرگز stage نشود:

- `.env` / `.env.*` (به‌جز `.env.example`)
- `/vendor` / `/node_modules`
- `database/*.sqlite`
- لاگ‌ها، کلیدها (`*.pem`، `id_rsa`، …)، dumpها (`*.sql`، `*.dump`)

### ۳.۳ commit و push (فقط بعد از درخواست مالک)

```powershell
git add <فایل‌های مشخص>          # ترجیحاً نه git add -A کور
git status --short
git diff --cached --stat
git commit -m "پیام روشن دربارهٔ چرا"
git push origin main
```

اگر remote تاریخچهٔ متفاوت داشت: **بدون force push**. وضعیت را گزارش دهید و از مالک بپرسید.

### ۳.۴ اگر ریپو از صفر ساخته می‌شود (الگو)

1. `git init -b main` (اگر `.git` نبود)
2. `.gitignore` لاراول + استثناها (همین ریپو نمونه است)
3. audit امنیتی
4. `Initial commit ...`
5. ساخت ریپوی **private** روی GitHub با نام توافق‌شده
6. `git remote add origin ...` سپس `git push -u origin main`

---

## ۴) اتصال به هاست (SSH)

### ۴.۱ واقعیت شبکه

- SSH مستقیم از بعضی IPها به `195.254.165.9:1396` بسته است.
- فایروال هاست معمولاً فقط IP مجاز (مثلاً سرور واسط) را قبول می‌کند.
- کاربر `tvyarcom` **sudo ندارد** — نصب سطح سیستم ممکن نیست؛ همه چیز در فضای کاربر / cPanel است.

### ۴.۲ کلید و alias محلی (پیشنهادی)

کلید deploy محلی قبلاً برای این هاست ساخته شده (نام نمونه روی دیسک: `tvyarcom_195`).  
در `~/.ssh/config` یک Host **جدا از BackOffice** بگذارید تا با `roham-server` قاطی نشود:

```sshconfig
Host tv-yar-server
    HostName 195.254.165.9
    Port 1396
    User tvyarcom
    IdentityFile ~/.ssh/tvyarcom_195
    IdentitiesOnly yes
```

اگر از این سیستم مستقیم timeout می‌گیرید، یا IP خروجی را در فایروال هاست مجاز کنید، یا از **ProxyJump / سرور واسط** استفاده کنید (جزئیات واسط و رمز آن در این سند ذخیره نمی‌شود).

تست:

```powershell
ssh -o BatchMode=yes -o ConnectTimeout=15 tv-yar-server "echo OK; whoami; hostname; php -v | Select-Object -First 1"
```

اگر alias نبود:

```powershell
ssh -i $env:USERPROFILE\.ssh\tvyarcom_195 -p 1396 -o IdentitiesOnly=yes tvyarcom@195.254.165.9 "echo OK"
```

کلید عمومی باید در `~/.ssh/authorized_keys` کاربر `tvyarcom` باشد.

### ۴.۳ چه چیزهایی از پنل هاست لازم است (نصب اول)

قبل از استقرار اول، مالک باید بدهد یا در پنل بسازد:

| مورد | توضیح |
| --- | --- |
| دامنه | `APP_URL` نهایی؛ آیا روت همان `public_html` است یا ساب‌دامین؟ |
| MySQL | نام DB، یوزر، رمز، هاست (معمولاً `localhost`) |
| SSL | ترجیحاً از cPanel / Let’s Encrypt |
| خالی بودن وب‌روت | اگر سایت دیگری در `public_html` است، مسیر جدا توافق شود |

---

## ۵) استقرار روی هاست (الگوی هدف — مثل BackOffice)

هنوز نصب اول کامل نشده؛ وقتی دامنه + DB آماده شد، همین چک‌لیست اجرا می‌شود.

### ۵.۱ چیدمان پیشنهادی روی cPanel

```text
/home/tvyarcom/tv-yar/          ← هسته Laravel (git clone/pull اینجا)
/home/tvyarcom/public_html/     ← فقط محتوای قابل وب از public/ (یا symlink/کپی کنترل‌شده)
```

- `storage` و `bootstrap/cache` قابل نوشتن توسط PHP
- `php artisan storage:link` در صورت نیاز به فایل‌های عمومی
- `.env` فقط روی سرور؛ از روی `.env.example` ساخته می‌شود

### ۵.۲ به‌روزرسانی روزمره (بعد از اینکه Git روی سرور راه افتاد)

از ویندوز، بعد از تأیید مالک و بعد از `git push origin main` محلی:

```bash
cd ~/tv-yar
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force          # فقط اگر migration جدید بررسی شده
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
# در صورت جدا بودن Document Root: کپی/همگام‌سازی assetهای لازم از public/ — نه دست زدن بی‌مورد به index.php
```

اگر سرور به GitHub با SSH روی پورت ۲۲ دسترسی ندارد، مثل BackOffice از پورت ۴۴۳ و کلید deploy روی خود سرور استفاده شود:

```bash
GIT_SSH_COMMAND='ssh -p 443 -o Hostname=ssh.github.com -o IdentitiesOnly=yes -i ~/.ssh/github_deploy_auto' git pull origin main
```

### ۵.۳ نصب از صفر (یک‌بار)

1. اتصال SSH پایدار (`tv-yar-server` یا تونل)
2. `git clone` ریپوی `tv-yar` در مسیر هسته
3. `composer install --no-dev --optimize-autoloader`
4. ساخت `.env` پروداکشن + `php artisan key:generate`
5. تنظیم DB و `APP_URL`
6. `php artisan migrate --force`
7. seed فقط با تأیید (نقش‌ها/وضعیت‌ها — نه دادهٔ ساختگی خطرناک روی مشتری واقعی بدون هماهنگی)
8. اتصال Document Root به `public`
9. `npm ci && npm run build` محلی یا روی سرور (اگر Node روی هاست هست)؛ در غیر این صورت `public/build` از CI/لوکال امن منتقل شود
10. کش و تست ورود پنل

---

## ۶) چک‌لیست عامل (Agent playbook)

وقتی مالک گفت «پوش کن» یا «منتشر کن»:

1. `git status` / diff — مطمئن شو `.env` داخل stage نیست
2. فهرست کارهای پیشنهادی را به مالک بگو و تأیید بگیر
3. commit (در صورت نیاز) → `git push origin main`
4. اگر استقرار هاست هم خواسته شد: SSH → pull → composer در صورت تغییر lock → migrate بررسی‌شده → cache
5. نتیجه: لینک ریپو / هش کامیت / (در صورت استقرار) وضعیت migrate و یک تست دودویی ورود

وقتی فقط «وصل شو به هاست» گفته شد: بدون تغییر کد؛ فقط اتصال و گزارش وضعیت دیسک/PHP/مسیر وب.

---

## ۷) وضعیت فعلی (به‌روز شود بعد از هر فاز)

| فاز | وضعیت |
| --- | --- |
| Git init + initial commit | انجام شد |
| GitHub `mazloomiaero/tv-yar` + push `main` | انجام شد |
| Alias SSH پایدار از این ویندوز | از طریق ProxyJump به `46.8.103.130` |
| دامنهٔ پروداکشن | `https://app.tv-yar.com` → `~/crm` |
| MySQL پروداکشن | `tvyarcom_javad` روی سرور تنظیم شد |
| نصب اول روی هاست | انجام شد (migrate + ادمین `roham`) |

### مسیر اتصال عملی از ویندوز

```powershell
# Jump (رمز در این سند ذخیره نمی‌شود) → هاست دامنه با کلید tvyarcom_195
ssh -i $env:USERPROFILE\.ssh\tvyarcom_195 -p 1396 -o IdentitiesOnly=yes `
  -o ProxyCommand="$env:USERPROFILE\plink.exe -batch -ssh -P 22 -l root -pw <JUMP_PASS> 46.8.103.130 -nc %h:%p" `
  tvyarcom@195.254.165.9
```

به‌روزرسانی بعدی: push به GitHub، سپس روی سرور در `~/tv-yar` کد را همگام کنید، `composer install --no-dev` در صورت تغییر lock، `migrate --force` در صورت migration جدید، همگام‌سازی `public/` به `~/crm` (بدون خراب کردن `index.php` سفارشی و `.well-known`).
