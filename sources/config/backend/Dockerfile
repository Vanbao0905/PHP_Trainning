FROM php:8.3.9-apache

# Declare composer version
ENV COMPOSER_VERSION=2.7.7

# Update và cài dependencies
RUN apt-get update \
    && apt-get install -y \
        curl \
        zip \
        unzip \
        git \
        supervisor \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        libexif-dev \
        gnupg \
        tzdata \
    && docker-php-ext-install \
        bcmath \
        gd \
        opcache \
        sockets \
        zip \
        exif \
        pdo_mysql \
        mysqli \
    && docker-php-ext-enable pdo_mysql mysqli

# ✅ Cài Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# ✅ Cài Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Thiết lập DocumentRoot của Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# ✅ Cài Composer
RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=$COMPOSER_VERSION

# Bật Apache mod_rewrite
RUN a2enmod rewrite

# Tạo user non-root (tránh chạy Apache bằng root)
RUN useradd -ms /bin/bash -u 1000 app

# Copy script start nếu bạn có
# COPY start /usr/local/bin/
# CMD ["start"]

# Mặc định khởi động Apache foreground
CMD ["apache2-foreground"]
