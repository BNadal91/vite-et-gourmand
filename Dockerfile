# Image de production : PHP 8.3 + Apache, extensions PDO MySQL et MongoDB
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends libssl-dev libcurl4-openssl-dev pkg-config ca-certificates \
    && docker-php-ext-install pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# Configuration PHP de production (erreurs masquées, sessions durcies)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { echo 'expose_php=Off'; echo 'upload_max_filesize=2M'; echo 'post_max_size=8M'; echo 'session.cookie_httponly=1'; echo 'session.use_strict_mode=1'; } > "$PHP_INI_DIR/conf.d/security.ini"

# Seul le dossier public est exposé ; Fly.io écoute sur le port 8080
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!Listen 80!Listen 8080!' /etc/apache2/ports.conf \
    && sed -ri 's!<VirtualHost \*:80>!<VirtualHost *:8080>!' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n  AllowOverride All\n  Require all granted\n</Directory>\nServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data storage public/uploads

EXPOSE 8080
