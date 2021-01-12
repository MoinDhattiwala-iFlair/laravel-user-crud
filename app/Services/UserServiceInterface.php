<?php
namespace App\Services;

use App\Models\User;

interface UserServiceInterface
{
    public function getAll();
    public function createUser(array $input);
    public function updateUser(User $user, array $input);
}
