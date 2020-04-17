<p>This is a simple code bringing to you solutions which are not readily available.</p>
<h2>How it works</h2>
<div>
    Instead of using the default email verification of laravel,the set of codes in this application<br/>
    let you have control over your application by importing classes.
   
    install the jwt auth using the composer i.e composer require tymon/jwt-auth:dev-develop --prefer-source
    (require Tymon\JWTAuth\Providers\LaravelServiceProvider::class,) into the config/app.php in the providers array.
    
    require the aliases in the same directory file i.e config/app.php 
    remember these are facades
    'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class, 
    'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class,
    
    
    Finally generate an auth secret:from your terminal cd /projectDirectory type :php artisan jwt:secret
    <br/>The key will be set in the .env file
    
    Set your database and create your application
    
    Goodluck!!!!!!!!!!!!
