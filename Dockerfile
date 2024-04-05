FROM dunglas/frankenphp

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN mkdir /node
RUN curl 'https://nodejs.org/dist/v20.12.1/node-v20.12.1-linux-x64.tar.xz' | tar -xvJf - -C /node
RUN mv -v /node/node-*/bin/*     '/usr/local/bin' && \
    mv -v /node/node-*/include/* '/usr/local/include' && \
    mv -v /node/node-*/lib/*     '/usr/local/lib' && \
    mv -v /node/node-*/share/*   '/usr/local/share'

RUN install-php-extensions \
    pcntl \
    zip \
    gd \
    pdo_mysql

WORKDIR /app
COPY . /app

RUN mv .env.docker.example .env

RUN composer install --optimize-autoloader

RUN npm install
RUN npm run build

ENTRYPOINT ["sh", "docker-start.sh"]