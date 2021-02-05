<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class UserService
{
    /**
     * UserRepositoryInterface userRepository
     *
     * @var mixed
     */
    protected $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository;
    }

    public function getAll()
    {
        return $this->userRepository->all();
    }

    public function getDatatableData(array $input)
    {
        $users = User::query()
            ->join('cities', 'cities.id', 'users.city_id', 'left')
            ->join('states', 'cities.state_id', 'states.id', 'left')
            ->join('countries', 'states.country_id', 'countries.id', 'left')
            ->select([
                'users.*',
                'cities.name as city_name',
                'states.name as state_name',
                'countries.name as country_name',
            ]);

        return DataTables::eloquent($users)
            ->addIndexColumn()
            ->addColumn('photo', function ($row) {
                $photo = '<img src="' . $row->avtar . '" class="rounded-circle" alt="' . $row->name . '" />';
                return $photo;
            })
            ->addColumn('city', function ($row) {
                return $row->city_name;
            })
            ->addColumn('state', function ($row) {
                return $row->state_name;
            })
            ->addColumn('country', function ($row) {
                return $row->country_name;
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at->diffForHumans();
            })
            ->addColumn('updated_at', function ($row) {
                return $row->updated_at->diffForHumans();
            })
            ->addColumn('action', function ($row) {
                $actionBtn = '<button class="btn btn-warning" onclick="showBasicModal(\'Edit User\', \'' . route('user.edit', $row->id) . '\');">Edit</button>';
                $actionBtn .= '<button class="btn btn-danger ml-1" onclick="deleteRecord(\'' . route('user.destroy', $row->id) . '\')">Delete</button>';
                return $actionBtn;
            })
            ->rawColumns(['action', 'photo'])->make(true);

    }

    public function createUser(array $input)
    {
        if (isset($input['photo']) && !empty($input['photo'])) {
            $input['photo'] = uploadImage(public_path('/images/users'), $input['photo'], [75, 75]);
        }
        return $this->userRepository->create($input);
    }

    public function updateUser(User $user, array $input)
    {
        if (isset($input['photo']) && !empty($input['photo'])) {
            $input['photo'] = uploadImage(public_path('/images/users'), $input['photo'], [75, 75], $user->photo ?? null);
        }
        return $this->userRepository->update($user, $input);
    }

    public function deleteUser(User $user)
    {
        return $this->userRepository->delete($user);
    }
}
