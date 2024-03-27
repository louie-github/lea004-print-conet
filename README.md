# PRINTER KIOSK WEB

https://laravel.com/docs/10.x

## Installation

First clone this repository, install the dependencies, and setup your .env file.

```
git clone git@github.com:louie-github/lea004-print-conet.git
composer install
cp .env.example .env
```

Then create the necessary database.

```
ex. ThesisLaravel
```

And run the initial migrations and seeders.

```
php artisan migrate:fresh --seed

```
Install Node modules and run.

```
npm install
npm run dev

```

## Initial Account
Initial Account

```
email: admin@test.com
pass: secret

```

# Backend printing API
Basic usage:
 - /print - POST request; print files with specified settings
 - /status - GET request; get current printer status
 - /docs - auto-generated API docs using Swagger and FastAPI

## Installation
First, install [Python 3.10](https://www.python.org/) or greater and
[Poetry](https://python-poetry.org/docs/#installation) for
dependency management.

Then, navigate to [print_api](/print_api/) and set up the project by
running:
```
poetry install
```

To start the server, run:
```
poetry run cli api
```

A FastAPI server should start running on localhost port 48250. To view
the auto-generated API documentation, navigate to
http://localhost:48520/docs.

To stop the server, press Ctrl+C.