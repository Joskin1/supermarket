#!/usr/bin/env bash
set -euo pipefail
cat <<'INNEREOF' >.ddev/web-build/Dockerfile.laravel
ARG COMPOSER_HOME=/usr/local/composer
RUN composer global require laravel/installer
RUN ln -s $COMPOSER_HOME/vendor/bin/laravel /usr/local/bin/laravel
INNEREOF

ddev start -y

# SQLite is used here as other database types would fail due to
# the .env file not being ready, which DDEV will fix on 'ddev restart'
ddev exec laravel new temp --database=sqlite

ddev exec 'rsync -rltgopD temp/ ./ && rm -rf temp'
rm -f .ddev/web-build/Dockerfile.laravel .env
ddev restart
ddev composer run-script post-root-package-install && ddev composer run-script post-create-project-cmd && echo 'Completed "laravel new" script successfully'
