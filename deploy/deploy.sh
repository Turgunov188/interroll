#!/usr/bin/env bash
# Inter Roll — update the site on the server from GitHub.
#   cd ~/domains/interroll.uz/laravel && bash deploy/deploy.sh
# Steps: maintenance mode → git pull → composer → migrations → caches → back online.
# If anything fails the site is put back online and the error is shown.

set -euo pipefail
cd "$(dirname "$0")/.."

PHP="${PHP:-php}"
if command -v composer >/dev/null 2>&1; then
    COMPOSER=(composer)
elif [ -f "$HOME/bin/composer" ]; then
    COMPOSER=("$PHP" "$HOME/bin/composer")
else
    echo "composer topilmadi. O'rnatish: mkdir -p ~/bin && curl -sS https://getcomposer.org/installer | $PHP -- --install-dir=\$HOME/bin --filename=composer" >&2
    exit 1
fi

step() { printf '\n▶ %s\n' "$1"; }
back_online() { "$PHP" artisan up >/dev/null 2>&1 || true; }
on_error() { echo; echo "XATO — sayt qayta yoqildi, yuqoridagi xabarni o'qing." >&2; back_online; }
trap on_error ERR

step "Texnik rejim (tashrif buyuruvchiga «tez orada qaytamiz» sahifasi)"
"$PHP" artisan down --retry=15 || true

step "GitHub'dan yangi kod"
git pull --ff-only

step "Kutubxonalar (composer, dev'siz)"
"${COMPOSER[@]}" install --no-dev --optimize-autoloader --no-interaction --no-progress

step "Baza migratsiyalari"
"$PHP" artisan migrate --force

step "Keshlar"
"$PHP" artisan optimize:clear
"$PHP" artisan optimize
"$PHP" artisan filament:optimize
[ -e public/storage ] || "$PHP" artisan storage:link

step "Sayt yoqildi"
back_online
trap - ERR
echo "Tayyor: $(git log -1 --format='%h %s (%cr)')"
