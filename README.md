# User Module

A reusable User management module for Laravel applications powered by [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules).

## Installation

1. Install dependencies in your Laravel application:

```
composer require nwidart/laravel-modules
composer require joshbrw/laravel-module-installer
```

2. Require the module package:

```
composer require faysal0x1/user-module
```

3. Enable and publish assets:

```
php artisan module:enable User
php artisan vendor:publish --tag=user-views --force
php artisan migrate
```

4. Clear caches:

```
php artisan optimize:clear
```

## Development

- Service Provider: `App\\Modules\\User\\Providers\\UserServiceProvider`
- Publish tag for Inertia views: `user-views`
- Routes defined in `routes/admin.php`.

## License

MIT
