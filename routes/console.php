<?php

use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {

    app(\App\Repositories\UserRepository::class)->delete_unverified_users();

})->hourly();