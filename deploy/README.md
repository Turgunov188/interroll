# Inter Roll — DirectAdmin hostingga joylash

Hostingda tuzilma shunday bo'ladi (`domains/<domen>/` ichida):

```
laravel/        ← loyiha (kod, vendor, storage, .env) — internetdan ko'rinmaydi
public_html/    ← faqat css, js, shriftlar, index.php — sayt shu yerdan ochiladi
```

Sayt CSS/JS i (`public/build`) kompyuterda `npm run build` bilan yig'iladi va gitga kiradi — serverda Node.js kerak emas. Frontendni o'zgartirsangiz: `npm run build` → commit → push → serverda `bash deploy/deploy.sh`.

`bootstrap/app.php` buni o'zi taniydi: `laravel/public` yo'q va yonida `public_html` bor bo'lsa, `public_html` ni ishlatadi. Lokalda hech narsa o'zgarmaydi.

## A. SSH va git orqali (asosiy yo'l)

Bunda serverdagi `laravel/` papka GitHub'dagi repozitoriyning nusxasi bo'ladi. `public_html` esa `laravel/public` ga havola (symlink) qilinadi.

### Birinchi marta

```bash
# 1. Serverga ulanish (hosting bergan login va port bilan)
ssh <user>@interroll.uz -p <port>

# 2. PHP versiyasini tekshirish: kamida 8.2 bo'lishi kerak
php -v
#    eski versiya chiqsa, kerakli PHP'ni topish:
ls /usr/local/php8*/bin/php
#    va undan keyin buyruqlarni shunday ishlatish: PHP=/usr/local/php83/bin/php bash deploy/deploy.sh

# 3. composer (bir marta)
mkdir -p ~/bin && curl -sS https://getcomposer.org/installer | php -- --install-dir=$HOME/bin --filename=composer

# 4. GitHub uchun kalit: repozitoriy private bo'lgani uchun kerak
ssh-keygen -t ed25519 -C "interroll-server" -f ~/.ssh/github_interroll -N ""
cat ~/.ssh/github_interroll.pub
#    Chiqqan qatorni nusxalang: GitHub → repozitoriy → Settings → Deploy keys → Add deploy key
#    («Allow write access» belgilanmasin)
cat >> ~/.ssh/config <<'CFG'
Host github.com
  HostName ssh.github.com
  Port 443
  User git
  IdentityFile ~/.ssh/github_interroll
CFG
chmod 600 ~/.ssh/config
ssh -T git@github.com          # «Hi …! You've successfully authenticated» chiqishi kerak

# 5. Kodni olish
cd ~/domains/interroll.uz
git clone git@github.com:<github-login>/interroll.git laravel
cd laravel
cp deploy/env.production.txt .env
nano .env                      # APP_KEY, APP_URL, DB_ qatorlarini to'ldiring (Ctrl+O saqlash, Ctrl+X chiqish)
chmod -R 775 storage bootstrap/cache

# 6. public_html → laravel/public
cd ~/domains/interroll.uz
mv public_html public_html.old
ln -s laravel/public public_html
```

**7. Baza.** DirectAdmin'da MySQL baza yarating. Lokal bazaning `.sql` eksportini phpMyAdmin orqali import qiling. SSH'da quyidagicha ham bo'ladi:

```bash
mysql -u <db_user> -p <db_name> < interroll.sql
```

**8. Yuklangan rasmlar.** Ular git'da yo'q. Lokal `storage/app/public/` ichidagilarni File Manager yoki FTP orqali serverdagi `laravel/storage/app/public/` ga ko'chiring.

**9. Birinchi deploy:**

```bash
cd ~/domains/interroll.uz/laravel && bash deploy/deploy.sh
```

### Har yangilanishda

```bash
# kompyuteringizda
git add . && git commit -m "nima o'zgardi" && git push

# serverda
ssh <user>@interroll.uz -p <port>
cd ~/domains/interroll.uz/laravel && bash deploy/deploy.sh
```

`deploy.sh` quyidagilarni bajaradi:

1. Saytni texnik rejimga o'tkazadi.
2. `git pull` bilan yangi kodni oladi.
3. `composer install` bilan kutubxonalarni o'rnatadi.
4. Baza migratsiyalarini (`migrate`) bajaradi.
5. Keshlarni yangilaydi.
6. Saytni qayta yoqadi.

Xato chiqsa ham sayt qayta yoqiladi.

### Bazaga kompyuterdan ulanish

HeidiSQL yoki DBeaver'da **SSH tunnel** yorlig'ini oching:

- **SSH:** host `interroll.uz`, port — SSH porti, login va parol — SSH'niki;
- **MySQL:** host `127.0.0.1`, port `3306`, login va parol — bazaniki.

---

## B. SSH bo'lmasa: zip arxiv va Cron

### 0. Hostingni tekshirish (bir marta)

DirectAdmin → **Select PHP version** (yoki *PHP Settings*):

- **PHP 8.3 yoki 8.4.** Kamida 8.2 kerak, lekin lokalda qaysi versiya bo'lsa, shunisi yaxshi.
- **Kengaytmalar:** `intl`, `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `zip`, `gd`. `intl` siz admin panel ishlamaydi.
- **Limitlar:** `upload_max_filesize` va `post_max_size` kamida 64M (video uchun 200M), `memory_limit` 256M.

### 1. Arxivni yig'ish (o'z kompyuteringizda)

Loyiha papkasida PowerShell oching:

```
powershell -ExecutionPolicy Bypass -File deploy\make-package.ps1
```

Natija: `deploy\interroll-hosting.zip`. Ichida `laravel/`, `public_html/` va `env.production.txt` bor. Arxivga `vendor` faqat kerakli paketlar bilan kiradi; `.env`, testlar va eski sahifa fayllari kirmaydi.

### 2. Baza

1. DirectAdmin → **MySQL Management** → **Create new Database**. Nom, foydalanuvchi va parolni yozib oling. Ular `user_interroll` ko'rinishida bo'ladi.
2. **Lokal bazani ko'chiring.** Shunda admin foydalanuvchilar, katalog, sozlamalar va kontent tayyor bo'ladi:
   - OSPanel'da phpMyAdmin yoki HeidiSQL → `interroll` bazasi → **Export** → SQL.
   - Hostingda: DirectAdmin → **phpMyAdmin** → yangi baza → **Import** → shu `.sql` fayl.

### 3. Fayllarni yuklash

1. DirectAdmin → **File Manager** → `domains/<domen>/`.
2. `public_html` ichidagi hostingning standart fayllarini (`index.html`, `logo.png`) o'chiring.
3. `interroll-hosting.zip` ni `domains/<domen>/` ga yuklang → **Extract**. Natijada `laravel/` va `public_html/` papkalari to'ladi.
4. `env.production.txt` ni `laravel/` ichiga ko'chirib, nomini `.env` qiling. Ichidagi `<…>` joylarni to'ldiring:
   - `APP_KEY` — lokal `.env` dagi qatorni aynan ko'chiring;
   - `APP_URL` — `https://domen.uz`;
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — 2-qadamdagilar.
5. `laravel/storage` va `laravel/bootstrap/cache` papkalariga yozish huquqi bering: **Permissions → 755**, ichidagilar bilan birga.

### 4. Bir martalik buyruqlar

**SSH bo'lsa:**

```
cd ~/domains/<domen>/laravel
php artisan storage:link
php artisan migrate --force
php artisan optimize
php artisan filament:optimize
```

**SSH bo'lmasa — Cron orqali:** DirectAdmin → **Cron Jobs**, vaqtni har daqiqa (`* * * * *`) qilib, buyruqni kiriting:

```
cd /home/<user>/domains/<domen>/laravel && (/usr/local/bin/php artisan storage:link; /usr/local/bin/php artisan migrate --force; /usr/local/bin/php artisan optimize; /usr/local/bin/php artisan filament:optimize) >> storage/logs/deploy.log 2>&1
```

1–2 daqiqadan keyin `laravel/storage/logs/deploy.log` ni oching. Xato yo'q bo'lsa, **cron'ni o'chiring**.

- PHP yo'li hostingga qarab boshqacha bo'lishi mumkin, masalan `/usr/local/php83/bin/php`. Hosting yordamidan so'rasa bo'ladi.
- `storage:link` yuklangan rasmlar saytda ko'rinishi uchun `public_html/storage` havolasini yaratadi.

### 5. SSL

DirectAdmin → **SSL Certificates** → **Let's Encrypt** → domen va `www.domen` ni belgilang. `.htaccess` keyin hammani `https://` va www'siz manzilga yo'naltiradi.

### 6. Tekshirish

- [ ] `https://domen.uz` — bosh sahifa, 4 til, katalog, mahsulot sahifasi ochiladi.
- [ ] `https://domen.uz/admin` — lokal login va parol bilan kiriladi.
- [ ] Saytdan sinov arizasi yuborilsa, admin → «Заявки» da paydo bo'ladi.
- [ ] Adminda mahsulotga foto yuklansa, saytda ko'rinadi. Ko'rinmasa → pastdagi «Muammolar».
- [ ] «Настройки сайта» da telefon va manzilni to'ldiring.

### Keyingi yangilanishlar

1. `make-package.ps1` ni qayta ishga tushiring.
2. Arxivdan faqat `laravel/` ichidagilarni (`.env` va `storage/` dan tashqari) hamda `public_html/` ni yuklab, ustidan yozing.
3. Bir martalik buyruq (SSH yoki cron):
   ```
   php artisan migrate --force && php artisan optimize:clear && php artisan optimize && php artisan filament:optimize
   ```

### Muammolar

| Belgi | Sabab va yechim |
|---|---|
| **500 xato, oq sahifa** | `laravel/storage/logs/laravel-*.log` ni o'qing. Ko'pincha: `.env` dagi baza ma'lumotlari, `APP_KEY` bo'sh, `storage` ga yozish huquqi yo'q, yoki PHP versiyasi past |
| **«could not find driver»** | PHP'da `pdo_mysql` yoqilmagan |
| **Admin panel ochilmaydi, `intl` xatosi** | PHP'da `intl` ni yoqing |
| **Rasmlar 404** | `storage:link` ishlamagan yoki hosting symlink'ga ruxsat bermaydi. Hosting yordamidan `FollowSymLinks` ni so'rang |
| **Katta video yuklanmaydi** | `upload_max_filesize` / `post_max_size` ni oshiring yoki videoni YouTube havolasi bilan qo'shing |
| **https'da «Mixed content»** | `.env` da `APP_URL=https://…` ekanini tekshiring, so'ng `php artisan optimize` |
