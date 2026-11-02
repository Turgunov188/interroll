#!/usr/bin/env bash
# Inter Roll — pull-based deploy for hosting without SSH. Put in Cron every 5 minutes:
#
#   */5 * * * *  cd /home/<user>/domains/<domain>/laravel && PHP=/usr/local/php83/bin/php bash deploy/auto-deploy.sh >> storage/logs/deploy.log 2>&1
#
# Does nothing (and prints nothing) until GitHub has a commit the server does not; then runs deploy.sh.
# A lock keeps two runs from overlapping when a deploy takes longer than the Cron interval.

set -euo pipefail
cd "$(dirname "$0")/.."

exec 9>storage/framework/deploy.lock
flock -n 9 || exit 0

git fetch --quiet origin
[ "$(git rev-parse HEAD)" = "$(git rev-parse '@{u}')" ] && exit 0

echo "=== $(date '+%F %T')  $(git log -1 --format='%h %s' '@{u}') ==="
bash deploy/deploy.sh
