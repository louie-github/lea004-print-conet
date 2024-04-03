# PRINTER KIOSK WEB

https://laravel.com/docs/10.x

## Installation

First, clone this repository and check out the correct branch.
```bash
git clone git@github.com:louie-github/lea004-print-conet.git
git checkout alpha-testing
```

Then, install dependencies via Composer.
```bash
composer install
```
Note: If you are deploying this server to production, you can run the
alternative command:
```bash
composer install --optimize-autoloader --no-dev
```

Then, set up your .env file:
```bash
cp .env.example .env
```

Importantly, change the line below to match the URL at which you will be
hosting the web app:
```ini
KIOSK_URL=https://shiner-refined-subtly.ngrok-free.app/kiosk/process
```

Moreover, you should also change the lines below to match your database
setup:
```ini
DB_DATABASE=laravel
DB_USERNAME=
DB_PASSWORD=
```
Make sure that you have already created the necessary users and
databases beforehand.

Then, generate your app key and run the initial migrations and seeders:
```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

Then, install the necessary Node modules:
```bash
npm install
```

### Development server
In a development environment, please run **both** the Vite hot reload
server and a web server.

To run the Vite hot reload server, run:
```bash
npm run dev
```

To run the development web server, run:
```bash
php artisan serve
```


By default, this will expose your web app at http://localhost:8000.

## Production deployment
If you are running this server in production, you do not need to run the
Vite hot reload server. Instead, run:
```bash
npm run build
```

Please follow Laravel's [deployment guide](https://laravel.com/docs/10.x/deployment#optimization)
to optimize your web application. As a starting point, you can run these
commands to cache some important values:
```bash
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

Then, serve your PHP web application using your web server of choice,
such as Apache, NGINX, or Caddy.

Alternatively, you can also make use of Laravel Octane. By default,
frankenphp is configured as the default web server. Ensure that it is
installed on your system, then run:
```bash
php artisan octane:start
```

## Initial Account
By default, an admin account and test account will be created.

Administrator account:
```
email: admin@test.com
pass: secret
```

Test account:
```
email: test@test.com
pass: password