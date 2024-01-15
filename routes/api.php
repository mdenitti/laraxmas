<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// import DB facade
use Illuminate\Support\Facades\DB;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/hello', function () {
   return DB::table('presents')->get();
});

Route::get('/login', function () {
    return 'acces denied... please contact administrator';
 })->name('login');


 // Routes and endpoints for products
 
 
 Route::get('/presents', function () {
  $presents = DB::table('presents')
      ->join('users', 'users.id', '=', 'presents.user_id')
      ->select(
          'presents.id',
          'presents.name',
          'presents.done',
          'presents.price',
          'presents.img',
          'users.name as owner',
          'users.remember_token as token',
          'users.id as userid'
      )->get();

  return response()->json($presents);
});


 
 Route::post('/presents', function (Request $request) {
   $name = $request->name;
   $price = $request->price;
   $owner = $request->owner;
   // Add other fields as necessary
 
   DB::insert('INSERT INTO presents (name, price, owner) VALUES (?, ?, ?)', [$name, $price, $owner]);
   return response()->json(['message' => 'Present created successfully'], 201);
 });
 
 Route::get('/presents/{id}', function ($id) {
   $present = DB::select('SELECT * FROM presents WHERE id = ?', [$id]);
   return response()->json($present);
 });
 
 Route::put('/presents/{id}', function (Request $request, $id) {
   $title = $request->title;
   $price = $request->price;
   // Add other fields as necessary
 
   DB::update('UPDATE presents SET title = ?, price = ? WHERE id = ?', [$title, $price, $id]);
 
   return response()->json(['message' => 'Present updated successfully']);
 });
 
 Route::delete('/presents/{id}', function ($id) {
   DB::delete('DELETE FROM presents WHERE id = ?', [$id]);
   return response()->json(['message' => 'Present deleted successfully'], 204);
 });
 
 

 // Routes and endpoints for users

 Route::get('/users', function () {
   $users = DB::table('users')->get();
   return response()->json($users);
 });

 Route::post('/users', function (Request $request) {
  $validatedData = $request->validate([
      'name' => 'required|max:255',
      'email' => 'required|email|unique:users',
      'password' => 'required',
  ]);

  $user = User::create([
      'name' => $validatedData['name'],
      'email' => $validatedData['email'],
      'password' => $validatedData['password'],
      'created_at' => now(),
      'updated_at' => now(),
  ]);

  // Ensure the User model is using the HasApiTokens trait
  $token = $user->createToken('auth_token')->plainTextToken;

  // Update the remember_token in the database with the new token
  DB::table('users')
      ->where('id', $user->id)
      ->update(['remember_token' => $token]);

  return response()->json(['id' => $user->id, 'token' => $token], 201);
});


 // temp token routes
 Route::post('/tokens/create', function (Request $request) {
    $user = User::find(1);
    //return $user;
    $token = $user->createToken('mynewtoken');
    return ['token' => $token->plainTextToken];

    // post the plaintexttoken to the user in the database
    $user:: where('id', 1)->update(['remember_token' => $token->plainTextToken]);
 });