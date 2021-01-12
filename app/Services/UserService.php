<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    /**
     * UserRepositoryInterface userRepository
     *
     * @var mixed
     */
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAll()
    {
        return $this->userRepository->all();
    }

    public function createUser(array $input)
    {
        return $this->userRepository->create($input);
    }

    public function updateUser(User $user, array $input)
    {
        return $this->userRepository->update($user, $input);
    }
}
