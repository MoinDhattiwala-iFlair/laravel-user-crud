<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    /**
     * App\User
     *
     * @var mixed
     */
    protected $user;

    public function __construct()
    {
        $this->user = new User;
    }

    public function all(): Collection
    {
        return $this->user->all();
    }

    public function create(array $input)
    {
        if ($this->user->create($input)) {
            return response()->json(['status' => 1, "msg" => "User created successfully."]);
        } else {
            return response()->json(['status' => 0, "msg" => "Failed to create user."]);
        }
    }

    public function update(User $user, $input)
    {
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->city_id = $input['city_id'];

        if (isset($input['password']) && $input['password'] != "") {
            $user->password = Hash::make($input['password']);
        }
        if (isset($input['photo']) && $input['photo'] != "") {
            $user->photo = $input['photo'];
        }
        if ($user->save()) {
            return response()->json(['status' => 1, "msg" => "User updated successfully."]);
        } else {
            return response()->json(['status' => 0, "msg" => "Failed to update user."]);
        }
    }

    public function delete(User $user)
    {
        if ($user->delete()) {
            return response()->json(['status' => 1, "msg" => "User deleted successfully."]);
        } else {
            return response()->json(['status' => 0, "msg" => "Failed to delete user."]);
        }
    }
}
