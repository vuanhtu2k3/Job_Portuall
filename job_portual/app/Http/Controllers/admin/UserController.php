<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.user.list', [
            'users' => $users
        ]);
    }

    public function edit($id)
    {
        $users = User::find($id);

        return view('admin.user.edit', [
            'user' => $users
        ]);
    }
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->passes()) {

            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->save();

            Session::flash('success', 'User update successfully');
            return response()->json([
                'status' => true,
                'message' => 'User update successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function deleteUser(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);

        if ($user == null) {
            Session::flash('success', 'User not found');
            return response()->json([
                'status' => false,
                'message' => 'User  not found'
            ]);
        }

        User::where('id', $id)->delete();
        Session::flash('success', ' User deleted successfully');
        return response()->json([
            'status' => true,
            'message' => ' User deleted successfully'
        ]);
    }
}
