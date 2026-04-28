<?php

use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {

    app(\App\Repositories\UserRepository::class)->delete_unverified_users();
})->hourly();

Schedule::call(function () {
    app(\App\Services\UserService::class)->process_expired_bans();
})->daily();
