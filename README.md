# PRINTER KIOSK WEB

https://laravel.com/docs/10.x

## Installation

First, clone this repository and checkout the correct branch.
```
git clone git@github.com:louie-github/lea004-print-conet.git
git checkout alpha-testing
```

Then, install dependencies via Composer.
```
composer install
```
Note: If you are deploying this server to production, you can run the
alternative command:
```
composer install --optimize-autoloader --no-dev
```

Then, set up your .env file:
```
cp .env.example .env
```

Importantly, change the line below to match the URL at which you will be hosting the web app:
```
KIOSK_URL=https://shiner-refined-subtly.ngrok-free.app/kiosk/process
```

Moreover, you should also change the lines below to match your database
setup:
```
DB_DATABASE=laravel
DB_USERNAME=
DB_PASSWORD=
```
Make sure that you have already created the necessary users and
databases beforehand.

Then, run the initial migrations and seeders:
```
php artisan key:generate
php artisan migrate:fresh --seed
```

Then, install the necessary Node modules:
```
npm install
```

### Development server
In a development environment, please run **both** the Vite hot reload
server and a web server.

To run the Vite hot reload server, run:
```
npm run dev
```

To use the built-in PHP web server, run:
```
php artisan serve
```

To use frankenphp via Laravel Octane, run:
```
php artisan octane:start
```
Ensure that frankenphp is installed on your system.

Alternatively, you may also make use of Apache, NGINX, Caddy, or another
web server to host your PHP application.

By default, this will expose your web app at http://localhost:8000.

## Production deployment
If you are running this server in production, you do not need to run the
Vite hot reload server. Instead, run:
```
npm run build
```

Please follow Laravel's [deployment guide](https://laravel.com/docs/10.x/deployment#optimization)
to optimize the web application. As a starting point, you can run these
commands:
```
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

Then, serve your PHP web application using your web server of choice.

## Initial Account
Initial Account

```
email: admin@test.com
pass: secret
```