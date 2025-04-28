FROM php:8.3-fpm

# Install required system packages
RUN apt-get update && apt-get install -y \
    git zip unzip curl wget libzip-dev libpq-dev libonig-dev \
    libjpeg-dev libpng-dev libfreetype6-dev \
    librdkafka-dev librdkafka1 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Install php-rdkafka via PECL and configure
RUN pecl install rdkafka \
    && echo "extension=rdkafka.so" > /usr/local/etc/php/conf.d/rdkafka.ini

# Add PHP configuration to php.ini
RUN echo "memory_limit=512M" >> /usr/local/etc/php/php.ini

# Create CLI configuration directory if it doesn't exist and link rdkafka.ini
RUN mkdir -p /usr/local/etc/php/cli/conf.d/ \
    && ln -sf /usr/local/etc/php/conf.d/rdkafka.ini /usr/local/etc/php/cli/conf.d/rdkafka.ini

# Copy composer from official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory to the root of your project
WORKDIR /var/www/log-ingestion

# Copy the entire project (including 'log-ingestion' folder and composer.json)
COPY log-ingestion /var/www/log-ingestion

# Install Symfony and other dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Install PHPUnit for testing
RUN composer require --dev phpunit/phpunit:^9.5
RUN composer require --dev symfony/phpunit-bridge

# Entrypoint setup
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
