<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About this project

This API base project is built using Laravel version 10 with Laravel Sail. By default, you should be able to have all the basic structures including: 

- Mysql 
- Authentication with Laravel Sanctum implemented
    - Register with Email verification 
    - Login
    - Logout
- Role based authorization 
- More...

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## To get started

#### For Windows users:

1. Make sure you have Docker, Docker Compose, and WSL2 installed and configure to work together. 

2. Login to your WSL Windows Terminal. 

3. Clone the project. Make sure to place it in ~/ directory to ensure fast access. Then cd to your project directory.

4. Run the following command `docker run --rm -v $(pwd):/app composer install`

5. Add Laravel Sail command to ~/.bashrc. `alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'`

6. Run command `sail up -d`. When done, access the project at http://localhost


## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
