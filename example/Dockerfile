FROM php:8.4-cli

# the library uses bcmath for money math; not bundled in official php images
RUN docker-php-ext-install bcmath
