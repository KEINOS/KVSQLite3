#!/bin/bash

echo '- Downloading installer of composer ...'
EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
then
    >&2 echo '❌  ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet
rm composer-setup.php
mv ./composer.phar $(dirname $(which php))/composer && chmod +x $(dirname $(which php))/composer && \
echo '✅  MOVED: composer.phar successfully moved to ENV PATH.'

composer --version
