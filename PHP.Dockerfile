FROM php:fpm

# Install system dependencies and Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

RUN echo "#!/bin/bash\n\
service mariadb start\n\
sleep 10\n\
mysql -u root -e \"CREATE DATABASE IF NOT EXISTS HaarlemFestival;\"\n\
mysql -u root -e \"CREATE USER IF NOT EXISTS 'haarlemfestival'@'%' IDENTIFIED BY '!HaarlemFestival2025'\"\n\
mysql -u root -e \"GRANT ALL PRIVILEGES ON HaarlemFestival.* TO 'haarlemfestival'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;\"\n\
php artisan migrate --force\n\
php artisan db:seed --force\n\
exit 0" > /var/www/setup.sh && chmod +x /var/www/setup.sh

# Allow running Composer as root within the container
ENV COMPOSER_ALLOW_SUPERUSER=1

# On container start, install dependencies if vendor is missing, then start php-fpm
CMD ["sh", "-lc", "[ -f vendor/autoload.php ] || composer install --no-interaction --no-progress; exec php-fpm"]