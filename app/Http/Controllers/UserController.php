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

    public function create_driver(UserFormRequest $request)
    {
        $result = $this->userService->create_driver($request->validated());

        return response()->json([
            'message' => $result['message'],
        ], $result['code']);
    }

    public function get_profile()
    {

        $result = $this->userService->get_profile();
        return response()->json($result);
    }

    public function edit_profile(UserFormRequest $request)
    {
        $result = $this->userService->edit_profile($request->validated());

        return response()->json([
            'message' => 'تم تعديل الملف الشخصي بنجاح',
        ]);
    }
    public function get_sub_admins()
    {
        $result = $this->userService->get_sub_admins();
        return response()->json($result);
    }
    public function add_sub_admin(UserFormRequest $request)
    {
        $this->userService->add_sub_admin($request->validated());

        return response()->json([
            'message' => 'تم إضافة الموظف بنجاح',
        ]);
    }
    public function update_sub_admin(UserFormRequest $request, $id)
    {
        $result = $this->userService->update_sub_admin($request->validated(), $id);

        return response()->json([
            'message' => $result['message'],
        ], $result['code']);
    }
    public function block($id){

    }
}
