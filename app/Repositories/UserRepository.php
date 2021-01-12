<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * App\User
     *
     * @var mixed
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
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
        if (isset($input['password']) && $input['password'] != "") {
            $user->password = Hash::make($input['password']);
        }
        if ($user->save()) {
            return response()->json(['status' => 1, "msg" => "User updated successfully."]);
        } else {
            return response()->json(['status' => 0, "msg" => "Failed to update user."]);
        }
    }
}
