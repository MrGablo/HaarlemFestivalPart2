FROM php:8.3-fpm

# Install system dependencies, Microsoft ODBC driver, and Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        curl \
        gnupg \
        apt-transport-https \
        ca-certificates \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && sed -i 's|https://packages.microsoft.com|https://packages.microsoft.com|' /etc/apt/sources.list.d/mssql-release.list \
    && echo "deb [arch=amd64,arm64,armhf signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/11/prod bullseye main" > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
        msodbcsql18 \
        unixodbc-dev \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install pdo pdo_mysql \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Allow running Composer as root within the container
ENV COMPOSER_ALLOW_SUPERUSER=1

# On container start, install dependencies if vendor is missing, then start php-fpm
CMD ["sh", "-lc", "[ -f vendor/autoload.php ] || composer install --no-interaction --no-progress; exec php-fpm"]