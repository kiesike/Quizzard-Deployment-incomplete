FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions needed by Laravel
RUN apt-get install -y libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer (PHP package manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP settings (upload size, etc.)
COPY local.ini /usr/local/etc/php/conf.d/local.ini

# Set working directory
WORKDIR /var/www

# Copy everything from our backend folder into the container
COPY . /var/www

# Give the web server permission to write to storage folders
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

EXPOSE 9000
CMD ["php-fpm"]