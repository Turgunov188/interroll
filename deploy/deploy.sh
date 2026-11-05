#!/usr/bin/env bash
# Inter Roll — update the site on the server from GitHub.
#   cd ~/domains/interroll.uz/laravel && bash deploy/deploy.sh
# Steps: maintenance mode → git pull → composer → migrations → caches → back online.
# If anything fails the site is put back online and the error is shown.

set -euo pipefail
cd "$(dirname "$0")/.."

PHP="${PHP:-php}"
# composer is always run by $PHP: a bare `composer` picks the server's default php (8.5 on this hosting),
# and the lock file is resolved for the version the site runs on
if [ -f "$HOME/bin/composer" ]; then
    COMPOSER=("$PHP" "$HOME/bin/composer")
elif command -v composer >/dev/null 2>&1; then
    COMPOSER=("$PHP" "$(command -v composer)")
else
    echo "composer topilmadi. O'rnatish: mkdir -p ~/bin && curl -sS https://getcomposer.org/installer | $PHP -- --install-dir=\$HOME/bin --filename=composer" >&2
    exit 1
fi

step() { printf '\n▶ %s\n' "$1"; }
back_online() { "$PHP" artisan up >/dev/null 2>&1 || true; }
# stale caches built by the previous code would break pages after a half-done deploy
on_error() { echo; echo "XATO — sayt qayta yoqildi, yuqoridagi xabarni o'qing." >&2; "$PHP" artisan optimize:clear >/dev/null 2>&1 || true; back_online; }
trap on_error ERR

step "Texnik rejim (tashrif buyuruvchiga «tez orada qaytamiz» sahifasi)"
"$PHP" artisan down --retry=15 || true

step "PHP: $("$PHP" -r 'echo PHP_VERSION;') · composer: $("${COMPOSER[@]}" --version 2>/dev/null | head -n 1)"

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
