FROM php:8.4-cli

# dom, xml, xmlreader, mbstring already ship enabled in the official image.
# Only zip needs compiling (requires libzip).
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (pinned major from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
