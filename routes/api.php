<?php

use App\Http\Controllers\CloudStorageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
//
//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


//Route::middleware('auth:api')->name('api.')->group(static function () {
//    Route::prefix('storage')->name('storage.')->group(static function () {
//        Route::any('/getPath', [CloudStorageController::class, 'getPath'])->name('getPath');
//    });
//});

// 使用 middleware('auth:sanctum') 包裹的就会自定验证用户登录与将用户信息放到 $request->user() 中
// 获取的令牌放在 Authorization 标头中传递，格式为 `Bearer ${token}`, 其中 token 为获取到的登录 token 字符串，如`Bearer 6|Qyl8iYbMVf1eJOgPvfskxinNC8MnOwrnEf66RgoG`
//Route::middleware(['throttle:6000,1','auth:sanctum'])->name('api.')->group(function () {
//Route::get('t/{id}',static function(){
//    dd(route('ttt','123'));
//
//})->name('ttt');


Route::middleware('auth:sanctum')->name('api.')->group(function () {
    //user
    Route::prefix("/user")->name('user.')->group(function () {
        Route::post('/test', static function () {
            return Auth::user()->getAuthIdentifier();
        })->name('test');

        Route::post('/user', static function (Request $request) {
            return [
                // 所有token
                // $request->user()->tokens
                //
                'id' => $request->user()->id,
                'name' => $request->user()->name
            ];
        })->name('user');

        Route::post('/login_out_all', static function (Request $request) {
            return $request->user()->tokens()->delete();
        })->name('login-out-all');
    });

    //storage
    Route::prefix("/storage")->name('storage.')->group(function () {
        Route::post('/get-path', [CloudStorageController::class, 'getPath'])->name('get-path');
        Route::post('/create-folder', [CloudStorageController::class, 'createFolder'])->name('create-folder');
        Route::post('/delete-folder', [CloudStorageController::class, 'deleteFolder'])->name('delete-folder');
        Route::post('/make-file', [CloudStorageController::class, 'makeFile'])->name('make-file');
        Route::post('/upload-file', [CloudStorageController::class, 'uploadFile'])->name('upload-file');
        Route::post('/download-file-get', [CloudStorageController::class, 'downloadFileGet'])->name('download-file-get');
        Route::get('/download-file/{id}', [CloudStorageController::class, 'downloadFile'])->whereUuid('id')->name('download-file');
    });
});

// 用户登录
Route::post('/user/login', static function (Request $request) {
    // // 密码版验证
    $email = $request->input("email");
    // password 为未加密的用户密码，如：123456
    $password = $request->input("password");
    // 可以添加自定义认证字段，如：active=1
    // if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1])) {
    if (!Auth::attempt(['email' => $email, 'password' => $password])) {
        throw new InvalidArgumentException("用户验证失败");
    }
    // 通过验证后就会自动登录，$request->user() 就可以获取到用户信息了。
    return [
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'token' => $request->user()->createToken("USER_TOKEN")->plainTextToken
    ];
    // // 密码版验证 结束


    // // 小程序版验证
    // $user = User::where("email", $request->input("email"))->firstOrFail();
    // Auth::login($user);
    // $token = $request->user()->createToken("USER_TOKEN");
    // return [
    //     'token' => $token->plainTextToken,
    //     'user'=> $request->user()
    // ];
    // // 小程序版验证 结束
})->name('api.user.login');

