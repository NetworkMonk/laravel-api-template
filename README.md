# Laravel API Template

This is a simple template that is a good starting point for a Laravel REST API.

## Configuration

Check the `.env.example` file for an example configuration and ensure you populate the JWT and RECAPTCHA configurations if they are going to be used.

To generate a new random key that can be used for the `JWT_KEY` and `JWT_REFRESH_KEY` values you can use the following command.

```sh
php artisan key:generate --show
```

Install composer and node dependancies by using the following two commands.

```sh
composer install
npm install
```

## Running a Test Server

Use the following command to launch the API locally using the internal PHP server.

```sh
php artisan serve
```

You will get prompted with the path the application is serving on.

## JWT Middleware

The following middleware is available to use.

### jwt

This simply checks for existince of a valid JWT token. Which means the request is authenticated. If no JWT has been included in the Authorization header of the request, or the JWT is invalid a 401 response will be returned.

### jwt.refresh

This checks for the existence of a valid JWT refresh token. This is retrieved from a HTTP only cookie. This token should only be used for the lifetime of a session and is only used to generate a new JWT for client.

If no refresh token is present, or it is invalid, a 401 response will be returned.

### role:[rolename]

This checks that the JWT contains a set role specified by `rolename`. If the role exists on the users token, the request continues. Otherwise a 403 `Access Denied` response is returned to the client.

### recaptcha

The request must contain valid data for a recaptcha verification (v3 recaptha supported).

## Creating Endpoints

You only need to add routes to the `routes/api.php` file for this project.

To make a new API controller to direct routes to, you can use the following command.

```sh
php artisan make:controller MyController --api
```

Following are some examples of routes using available middleware.

### Single authenticated request

```php
Route::middleware(['api', 'jwt'])
    ->get('/user', 'User@show');
```

### A group of authenticated requests

```php
Route::group(['middleware' => ['api', 'jwt']], function() {
    Route::get('/user/{username}', 'User@show');
    Route::put('/user/{username}', 'User@update');
});
```

### A request from a user that must be a global administrator

```php
Route::group(['middleware' => ['api', 'jwt', 'role:Global Administrator']], function() {
    Route::get('/users', 'User@index');
});
```

### Unathenticated requests that are protected using recaptha

```php
Route::group(['middleware' => ['api', 'recaptcha']], function() {
    Route::post('/new', 'Create@store');
    Route::post('/auth', 'Auth@store');
});
```

### A request that must have a valid refresh token stored in a cookie

```php
Route::group(['middleware' => ['api', 'jwt.refresh']], function() {
    Route::post('/auth/refresh', 'Refresh@update');
});
```

## Controllers

Included is a basic set of controllers and database schema that would allow you to create and handle users and authentication.

## Deployment

You would mostly deploy using git, but if you are hosting on a server that does not have this facility you can use the built in deployment script.

If you want you can use the deployment script to deploy your application files via FTP. Ensure you have a `deploy.json` file configured using `deploy.example.json` as a base.

Run the following command to deploy your API.

```sh
node deploy.json
```

You need to make sure that you have terminal access to the destination server so that you can execute database migrations etc.

To migrate the database to the latest version make sure you run.

```sh
php artisan migrate
```
