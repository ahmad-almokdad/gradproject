<?php

use App\Services\FCMService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('noti',function (){
    $fcm_sender = new FCMService();
    $fcm_sender->sendNotification(
        //"cmsR8EUZRa2GF3_1j-1fnF:APA91bEVP9oF7hKLQV-Jxjo71839NE32VphWX96-NYLUtXg21-yiM4eo7NXbxd-xjBle28H4icpBvWQdqSr9ybsOmonVnUIBZ5WSRVdDIP5-yMF99g1Z-OtzHiUzzVO15EVQImeRlld_",
        "fgPimMTsSiWuR8SREnCFJo:APA91bFgBqcnkfsMRImsUmci7_po6NXnyWETJbfZaJOjcR_yGTkGwt7KJu6t52pfV5kv4bQQ_RyrOIxdluSEXcRmqtKKCDYN2PcgE98aAInqb1Ek_fLlM_x_1K7VoSUs9PubdNtWg6QP",
        "Home Care","Ahla abo hmeed");
    return "success";
});
