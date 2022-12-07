<p align="center">
	<img src="https://caspermember.com/images/logo.png" width="400">
</p>

# Casper Association Member Portal

The Casper Association's member portal.

This is the backend repo of the portal. To see the frontend repo, visit https://github.com/ledgerleapllc/CasperAssociationPortal

## Prerequisites

 - Apache/2.4.29+ (Ubuntu)
 - PHP 7.4+
 - MySql Ver 14.14 Distrib 5.7.37
 - Laravel Framework 8.47.0
 - Git Version 2+

## Setup

We generally would use the latest version of Ubuntu for testing installs. Example hosting server: AWS ec2 t2 medium with at least 10Gb SSD.

```bash
# Apache install
sudo apt -y install apache2

# Enable mods
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl

# Install required software
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt -y install software-properties-common
sudo apt-get install -y php7.4
sudo apt-get install -y php7.4-{bcmath,bz2,intl,gd,mbstring,mysql,zip,common,curl,xml,gmp}

# Install composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Load php_gmp extension
echo "extension=php_gmp.so" | sudo tee /etc/php/7.4/mods-available/ext_gmp.ini
sudo ln -s /etc/php/7.4/mods-available/ext_gmp.ini /etc/php/7.4/cli/conf.d/20-ext_gmp.ini

# Configure Horizon
php artisan queue:table
php artisan migrate
## Install Redis. Link: https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-redis-on-ubuntu-20-04
composer require predis/predis
composer require laravel/horizon
php artisan horizon:install
php artisan horizon:publish
## Configure Supervisor. Link: https://laravel.com/docs/9.x/horizon#installing-supervisor ( Only 'Installing Supervisor', 'Supervisor Configuration', 'Starting Supervisor' Sections )
```

Setup the repo according to our VHOST path. Note, the actual VHOST path in this case should be set to **/var/www/CasperAssociationPortalBackend/public**

```bash
cd /var/www/
git clone https://github.com/ledgerleapllc/CasperAssociationPortalBackend
cd CasperAssociationPortalBackend
```

Install packages and setup environment

```bash
composer install
composer update
cp .env.example .env
```

After adjusting .env with your variables, run Artisan to finish setup

```bash
php artisan key:generate
php artisan migrate
php artisan passport:install
php artisan config:clear
php artisan route:clear
php artisan cache:clear
(crontab -l 2>>/dev/null; echo "* * * * * cd /var/www/CasperAssociationPortalBackend && php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

You may also have to authorize Laravel to write to the storage directory

```bash
sudo chown -R www-data:www-data storage/
```

Last, you need to setup emailers to start using the portal and see it work. Visit the URL of the backend with the path **/install-emailer**. This will install these things for you.

```bash
php artisan config:clear
```

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
