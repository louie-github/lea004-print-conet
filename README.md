# PRINTER KIOSK WEB

https://laravel.com/docs/10.x

## Installation

First, clone this repository and checkout the correct branch.
```
git clone git@github.com:louie-github/lea004-print-conet.git
git checkout kioskweb
```

Then, install dependencies via Composer.
```
composer install
```

Then, set up your .env file:
```
cp .env.example .env
```

Importantly, change the line below to match the URL at which you will be hosting the web app:
```
KIOSK_URL=https://shiner-refined-subtly.ngrok-free.app/kiosk/process
```
**Do not forget the `/kiosk/process`!**

Moreover, you should also change the lines to match your database setup:
```
DB_DATABASE=laravel
DB_USERNAME=
DB_PASSWORD=
```
Make sure that you have already created the necessary users and databases beforehand.

Then, run the initial migrations and seeders.

```
php artisan migrate:fresh --seed
php artisan key:generate
```

Then, install Node modules and run the Vite hot reload server.
```
npm install
npm run dev
```

Finally, run the main web app using the command:
```
php artisan serve
```
By default, this will expose your web app at http://localhost:8000.

## Initial Account
Initial Account

```
email: admin@test.com
pass: secret
```