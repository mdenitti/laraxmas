# Secure your Laravel API endpoints using Laravel Sanctum with a bearer token (no hair pulling :)

![Manage refresh Token and acces Token with Laravel Sanctum | by BOKO Marc  Uriel | Dec, 2023 | Medium](https://miro.medium.com/v2/resize:fit:1200/1*wKARgLrJbytGkHHJJt3f-w.png)

1. Install Laravel Sanctum via Composer. In your Laravel project directory, run:
   ```bash
   composer require laravel/sanctum
   ```
2. Publish the Sanctum's configuration and migration files. Run:
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```
   This command will generate a `sanctum.php` configuration file and a `create_personal_access_tokens_table.php` migration file in your Laravel application
   
3. Update your `User` model (App/Models) to use the `Laravel\Sanctum\HasApiTokens` trait:
   ```php
   use Laravel\Sanctum\HasApiTokens;

   class User extends Authenticatable
   {
       use HasApiTokens, HasFactory, Notifiable;
   }
   ```
   This trait provides the `createToken` method to your User model 

4. Create an endpoint for generating tokens. Use the `createToken` method to generate a token for a user. This method returns a `Laravel\Sanctum\NewAccessToken` instance. You can access the plain text value of the token using the `plainTextToken` property or store it inside the users table. This method required tweaking... duh!

```php
Route::post('/tokens/create', function  (Request  $request) {
	$user  =  User::find(1);
	//return $user;
	$token  =  $user->createToken('mynewtoken');
	return ['token'  =>  $token->plainTextToken];
});
```
	#### More advanced routing method with user validation ;)
	
   ```php
	use App\Models\User;  
	use Illuminate\Support\Facades\Hash;  
	use Illuminate\Validation\ValidationException;
	use Illuminate\Http\Request;

   Route::post('/tokens/create', function (Request $request) {
       $request->validate([
           'username' => 'required',
           'password' => 'required',
       ]);

       $user = User::where('username', $request->username)->first();

       if (!$user || !Hash::check($request->password, $user->password)) {
           throw ValidationException::withMessages([
               'username' => ['The provided credentials are incorrect.'],
           ]);
       }

       $token = $user->createToken($request->token_name);
       
       return ['token' => $token->plainTextToken];
   });
   ````
   
5. Protect your API routes using the `auth:sanctum` middleware. This ensures that only authenticated users with a valid Sanctum API token can access the routes:

   ```php
   Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
       return $request->user();
   });
   ```

6. When making requests to your protected routes, include the token in the `Authorization` header as a `Bearer` token. **Remember: Before implementing always perform an insomnia test**

Remember, Laravel Sanctum is a powerful tool for securing API authentication and authorization in Laravel. It provides a user-friendly interface and straightforward methods for token-based authentication. It also offers CSRF protection. 

Sanctum is designed for  **stateless authentication**, where the client application (e.g., a frontend) is responsible for storing and sending the bearer token with each API request.

Instead, when a user logs in using Sanctum, a new API token is generated and returned as the response.  **The client application should then store this token securely on the client-side (e.g., in local storage or a cookie) and include it in subsequent API requests as an authorization header**  (`Authorization: Bearer <API_TOKEN>`).

By following this approach, the client application maintains the responsibility for handling and sending the bearer token with each request, allowing for stateless authentication and decoupling the server-side from managing token storage and retrieval.

## Do not forget to install CORS into your Laravel API

1.  Laravel 7 and above include CORS support out-of-the-box. So, you don't need to install a separate package. To configure CORS, go to the  `config/cors.php`  file. If this file does not exist, you can create it.

2.  Configure the CORS settings to allow all origins, methods, and headers. Update the  `cors.php`  configuration file as follows:
 ```php
 'paths'  =>  ['api/*'],   
 'allowed_methods'  =>  ['*'],   
 'allowed_origins'  =>  ['*'],   
 'allowed_origins_patterns'  =>  [],   
 'allowed_headers'  =>  ['*'],   
 'exposed_headers'  =>  false,   
 'max_age'  =>  false,   
 'supports_credentials'  =>  false,
````

This configuration allows all origins, methods, and headers, meaning your API will be accessible from any domain, using any HTTP method, and accepting any HTTP headers. This is suitable for development, but for production, you should limit the origins, methods, and headers to only what is necessary for your application.

For the changes to take effect, you should clear the configuration cache by running  `php artisan config:clear`.

Remember, CORS is a security feature that allows web applications to make cross-domain calls. It's important to configure it properly to ensure that your API is secure and works correctly with client-side applications.

## oAuth2 flow

**Authorization Code Flow for a Web Application**

2.  **Resource Owner Authorization:** The user visits a web page of the third-party application, which redirects them to the authorization server to authenticate and authorize the application to access their resources.
    
3.  **Authorization Code Grant:** The user logs in to the authorization server and approves the application's request for access. The authorization server redirects the user back to the third-party application with an authorization code.
    
4.  **Access Token Issuance:** The third-party application sends the authorization code to the authorization server. In exchange, the authorization server issues an access token and an optional refresh token to the third-party application.
    
5.  **Resource Access:** The third-party application sends the access token to the resource server along with a request for protected resources. The resource server validates the access token and grants the third-party application access to the requested resources.

### 10 Step flow: https://www.eraser.io/diagramgpt

1.  User visits a third-party application (client) that wants to access their resources on another service (resource server).
2.  The client redirects the user to the authorization server (AS) to authenticate and grant access.
3.  The AS redirects the user back to the client with an authorization code.
4.  The client sends the authorization code to the AS to get an access token.
5.  The AS validates the authorization code and sends the client an access token.
6.  The client sends the access token to the resource server to access protected resources.
7.  The resource server validates the access token and grants access to the requested resources.
8.  The client uses the protected resources.
9.  The access token expires and the client needs to get a new one from the AS.
10.  The client sends the refresh token to the AS to get a new access token.

https://brokercloud-docs.readthedocs.io/en/latest/restapi.html#get-v2

```javascript
(C) private SyntraPXL = (2023) => "De Nittis Massimo"
```
![Opleidingen voor ondernemende professionals | Syntra Bizz](https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQZ4L7nJyZQ8s6Vj8bAlsAQjZF9H_wtlkjmhZAPL0AYRSRyM_Yd7CPVdJOpuAToi0iI4w&usqp=CAU)
