FROM php:8.2-apache

# Installer les dépendances + MongoDB
RUN apt-get update && apt-get install -y libzip-dev unzip && docker-php-ext-install pdo pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb


# Copier les fichiers du projet dans le conteneur
COPY . /var/www/html/


# Définir le DocumentRoot sur public et mettre à jour la configuration d'Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf && \
    echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf



# Donner les droits à Apache
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/
RUN a2enmod rewrite

# Exposer le port 80
EXPOSE 80
