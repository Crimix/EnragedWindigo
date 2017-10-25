# Enraged Windigo

***(Temporary instructions. Needs more detail.)***

## Prerequisites

* `Composer` installation
* `Node.js` (with `npm`) installation
* PHP 7.1 installation
  * Ensure that all three are within `PATH`
* **Either:**
  * A working Homestead installation
  * A MySQL/Postgres installation that you know how to configure

## Installation

* Copy `.env.example` to `.env`
* Execute `composer install`
* Execute `php artisan key:generate`
* Execute `php artisan migrate:fresh --seed`

