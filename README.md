# PRINTER KIOSK WEB (BETA)

https://laravel.com/docs/10.x

## Docker deployment
A work-in-progress Docker deployment can be started by running:
```
docker-compose up -d
```
This starts up a MariaDB container as well as a container for the main application.

Note that this is not yet suitable for development work. If you change
the contents of files, it will not be reflected.

## Installation (Frontend)

First, clone this repository and check out the correct branch.
```bash
git clone git@github.com:louie-github/lea004-print-conet.git
git checkout beta-testing
```

Then, install dependencies via Composer.
```bash
composer install
```
Note: If you are deploying this server to production, you can run the
alternative command:
```bash
composer install --optimize-autoloader
```

Then, set up your .env file:
```bash
cp .env.example .env
```

Importantly, change the line below to match the URL at which you will be
hosting the web app:
```ini
APP_URL="https://print-conet.947825.xyz"
```
The KIOSK_URL field will then be filled automatically (see the
[example file](.env.example)).

You can also change the line below to match the URL at which the
backend APIs will be hosted:
```ini
BACKEND_URL="http://127.0.0.1:48250"
```
This is especially useful if you are running the frontend in WSL while
the backend is running on Windows. In that case, please change the IP
address to the value detailed in the [WSL docs](https://learn.microsoft.com/en-us/windows/wsl/networking)
to access the backend API properly.

Moreover, you should also change the lines below to match your database
setup:
```ini
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password
```
Make sure that you have already created the necessary users and
databases beforehand.

Then, generate your app key and run the initial migrations and seeders:
```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

Also, enable and link the public storage folder:
```bash
php artisan storage:link
```
On Windows, this will require either administrator permissions or for
Developer Mode to be enabled.


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

## Installation (Backend)
### Print API
To run the backend printing and document conversion APIs, first install
[Python](https://www.python.org/) version 3.10 or higher as well as
[Poetry](https://python-poetry.org/) for dependency management.

For the document conversion APIs, please ensure as well that Microsoft
Word and Microsoft Excel have been installed.

Then, navigate to the [backend](backend/) directory and install the
project dependencies:
```bash
poetry install
```

Then, start the API, which by default will listen on all IP addresses
(0.0.0.0):
```bash
poetry run cli api
```
Press Ctrl+C to stop the server.

### Coin slot API
To run the backend coin slot interface, ensure that [Node](https://nodejs.org/en)
is installed, as well as npm. Ensure as well that your Arduino board is
properly set up and connected.

#### Arduino code

Connect your Arduino to your computer and make sure that you have
uploaded the corresponding sketch to your board. If not, open
[Arduino IDE](https://www.arduino.cc/en/software/), open the sketch
[coinslot.ino](backend/coinslot/coinslot.ino), and upload it to your
board.

To connect your coin slot to the board, you can follow a similar circuit
diagram to the tutorial
[How to Control CH-926 Coin Acceptor With Arduino](https://www.instructables.com/How-to-Control-CH-926-Coin-Acceptor-With-Arduino/).

Make sure that you set `INTERRUPT_PIN` in the sketch to the pin to which
you connect your coin slot pulse wire. By default, this is set to
**pin 2**.

Insert coins while checking the Serial Monitor in Arduino IDE to make
sure that your coin slot is correctly being detected.

#### JavaScript backend

Then, navigate to the [backend/coinslot](backend/coinslot/) directory
and install the project dependencies:
```bash
npm install
```

Then, start the coinslot API, which will automatically search for the
Arduino serial port and start listening for matching values.
```bash
node index.js
```

You can optionally adjust the code to match the pulse payment route in
the frontend web app:
```js
const PULSE_ENDPOINT = "http://localhost:8000/pulsePayment";
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
```