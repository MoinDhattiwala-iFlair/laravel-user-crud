<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use MoinDhattiwala\CountryStateCity\Models\Country;
use MoinDhattiwala\CountryStateCity\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $login = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (!Auth::attempt($login)) {
            return response(['message' => 'Invalid credentials']);
        }
        $token = Auth::user()->createToken('AuthToken')->accessToken;

        return response(['user' => Auth::user(), 'token' => $token]);

    }

    public function users()
    {        
        return User::query()
            ->join('cities', 'cities.id', 'users.city_id', 'left')
            ->join('states', 'cities.state_id', 'states.id', 'left')
            ->join('countries', 'states.country_id', 'countries.id', 'left')
            ->select([
                'users.*',                
                'cities.name as city_name',
                'states.id as state_id',
                'states.name as state_name',
                'countries.id as country_id',
                'countries.name as country_name',
            ])->latest()->get();
    }    

    public function country()
    {
        return Country::get();
    }

    public function state(Country $country)
    {        
        return $country->state;
    }

    public function city(State $state)
    {        
        return $state->city;
    }

    public function storeUser(Request $request)
    {        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:8048'],
            'city_id' => ['required', 'numeric', 'exists:cities,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'city_id' => $request->city_id,
            'password' => \Hash::make($request->password),
            'photo' => $request->has('photo') ? uploadImage(public_path('/images/users'), $request->photo, [75, 75]) : null,
        ]);
        $user->forceDelete();
        return response()->json(['msg' => 'User Created successfully', 'user' => $user]);
    }

    public function updateUser(Request $request, User $user)
    {        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:8048'],
            'city_id' => ['required', 'numeric', 'exists:cities,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        if ($request->has('password') && $request->password != "") {
            $user->password = \Hash::make($request->password);
        }        
        if ($request->has('photo')) {
            $user->photo = uploadImage(public_path('/images/users'), $request->photo, [75, 75], $user->photo);
        }
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'city_id' => $request->city_id,
        ]);        
        return response()->json(['msg' => 'User Updated successfully', 'user' => $user]);
    }

    public function deleteUser(User $user)
    {
        if ($user->delete()) {
            if (request()->user()->id == $user->id) {
                request()->user()->token()->delete();
            }
            return response()->json(['msg' => 'User Deleted successfully']);
        }
        return response()->json(['msg' => 'Failed to delete user.']);
    }

    public function logout()
    {
        if(request()->user()->token()->delete()) {            
            return response()->json(['msg' => 'Logout successfully']);
        }
        return response()->json(['msg' => 'Failed to logout.']);
    }
}
