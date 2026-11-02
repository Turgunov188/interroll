#!/usr/bin/env bash
# Inter Roll — one-time switch of a zip install to a git checkout (no SSH needed, runs from Cron).
#
# Before:  domains/<domain>/laravel        ← unpacked from interroll-hosting.zip
#          domains/<domain>/public_html    ← copy of public/
# After:   domains/<domain>/laravel        ← git clone of the repository (with the old .env and uploads)
#          domains/<domain>/public_html    → symlink to laravel/public
# The old folders are kept as laravel.old-<date> and public_html.old-<date>; delete them once the site works.
#
#   cd ~/domains/<domain> && git clone git@github.com:<owner>/<repo>.git laravel-git
#   PHP=/usr/local/php83/bin/php bash laravel-git/deploy/switch-to-git.sh

set -euo pipefail

NEW="$(cd "$(dirname "$0")/.." && pwd)"
SITE="$(dirname "$NEW")"
OLD="$SITE/laravel"
DONE="$SITE/.switched-to-git"
STAMP="$(date +%Y%m%d-%H%M%S)"
PHP="${PHP:-php}"

say() { printf '\n▶ %s\n' "$1"; }

[ -f "$DONE" ] && { echo "Already switched on $(cat "$DONE"), nothing to do."; exit 0; }
[ -d "$NEW/.git" ] || { echo "$NEW is not a git clone." >&2; exit 1; }
[ "$NEW" != "$OLD" ] || { echo "Run this from a fresh clone next to laravel/, not from laravel/ itself." >&2; exit 1; }
[ -f "$OLD/.env" ] || { echo "$OLD/.env not found: nothing to take the settings from." >&2; exit 1; }

say "composer"
if command -v composer >/dev/null 2>&1; then
    COMPOSER=(composer)
else
    if [ ! -f "$HOME/bin/composer" ]; then
        mkdir -p "$HOME/bin"
        curl -sS https://getcomposer.org/installer | "$PHP" -- --install-dir="$HOME/bin" --filename=composer
    fi
    COMPOSER=("$PHP" "$HOME/bin/composer")
fi
"${COMPOSER[@]}" --version

say "Settings and uploaded files from the old install"
cp "$OLD/.env" "$NEW/.env"
cp -a "$OLD/storage/app/." "$NEW/storage/app/"

say "Libraries (no dev packages)"
cd "$NEW"
"${COMPOSER[@]}" install --no-dev --optimize-autoloader --no-interaction --no-progress
chmod -R u+rwX,go+rX,go-w storage bootstrap/cache

say "Swap folders"
mv "$OLD" "$SITE/laravel.old-$STAMP"
mv "$NEW" "$OLD"
if [ -L "$SITE/public_html" ]; then
    rm "$SITE/public_html"
elif [ -e "$SITE/public_html" ]; then
    mv "$SITE/public_html" "$SITE/public_html.old-$STAMP"
fi
ln -s laravel/public "$SITE/public_html"

say "Database and caches"
cd "$OLD"
"$PHP" artisan migrate --force
[ -e public/storage ] || "$PHP" artisan storage:link
"$PHP" artisan optimize:clear
"$PHP" artisan optimize
"$PHP" artisan filament:optimize

date '+%F %T' > "$DONE"
echo
echo "Done: $(git log -1 --format='%h %s (%cr)')"
echo "Old copies: laravel.old-$STAMP, public_html.old-$STAMP — delete them once the site works."
