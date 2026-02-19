FROM php:8.4-fpm

# Install system dependencies, Composer and (optionally) Microsoft SQL drivers
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git unzip libzip-dev gnupg2 apt-transport-https curl build-essential unixodbc-dev ca-certificates; \
    # Install MySQL PDO driver
    docker-php-ext-install pdo pdo_mysql; \
    # Register Microsoft package repository for ODBC and SQLSRV (for connecting to Azure SQL)
    curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /usr/share/keyrings/microsoft.gpg; \
    echo "deb [signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/11/prod bullseye main" > /etc/apt/sources.list.d/mssql-release.list; \
    apt-get update; \
    ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql17; \
    # Install PHP sqlsrv/pdo_sqlsrv via PECL
    pecl install sqlsrv pdo_sqlsrv || true; \
    docker-php-ext-enable sqlsrv pdo_sqlsrv || true; \
    # Install Composer
    curl -sS https://getcomposer.org/installer -o composer-setup.php; \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
    rm composer-setup.php; \
    rm -rf /var/lib/apt/lists/*
    

WORKDIR /app
RUN echo "upload_max_filesize=20M\npost_max_size=20M" > /usr/local/etc/php/conf.d/uploads.ini




# Allow running Composer as root within the container
ENV COMPOSER_ALLOW_SUPERUSER=1

# Allow passing a DB connection string at build/runtime. Runtime env from docker-compose will override this.
ARG DB_CONNECTION
ENV DB_CONNECTION=${DB_CONNECTION}

# On container start, install dependencies if vendor is missing, then start php-fpm
CMD ["sh", "-lc", "mkdir -p public/assets/img/profiles; chmod -R 777 public/assets/img || true; [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress; exec php-fpm"]