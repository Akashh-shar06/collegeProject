FROM php:8.2-apache

# Enable the PHP extensions required to connect to MySQL.
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Serve the application from its public/ directory.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!\${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/college-erp.conf \
    && a2enconf college-erp

WORKDIR /var/www/html
COPY . /var/www/html/

EXPOSE 80
