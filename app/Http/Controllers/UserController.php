<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Services\UserService;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function create_driver(UserFormRequest $request){
        $result = $this->userService->create_driver($request->validated());

        return response()->json([
            'message' => $result['message'],
        ], $result['code']);
    }
}
