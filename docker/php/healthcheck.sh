#!/usr/bin/env sh
set -eu

php-fpm -t >/dev/null 2>&1
php -r 'exit(extension_loaded("pdo_mysql") && extension_loaded("zip") && extension_loaded("intl") ? 0 : 1);'
