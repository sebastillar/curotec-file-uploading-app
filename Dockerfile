FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Create non-root user
RUN useradd -u 1000 -m laraveluser \
    && mkdir -p /var/www/html \
    && chown -R laraveluser:laraveluser /var/www/html \
    && mkdir -p /home/laraveluser/.npm \
    && chown -R laraveluser:laraveluser /home/laraveluser

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Switch to non-root user
USER laraveluser