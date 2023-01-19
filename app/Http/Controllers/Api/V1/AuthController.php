<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserRequest  $request
     * @return \Illuminate\Http\Response
     */


    public function store(StoreUserRequest $request)
    {
        //Authorization in UpdateUserRequest

        $validatedUser = $request->validated();

        $passwordForLogin = $validatedUser['password'];
        $validatedUser['password'] = bcrypt($validatedUser['password']);

        $image_path = null;
        if ($request->image) {
            $image_path = rand() . '_user_' . time() . '.' . $request->image->extension();
            $request->image->move(storage_path('app/public/users_image'), $image_path);

            unset($validatedUser['image']);
            $validatedUser['image'] = $image_path;
        }

        $user = User::create($validatedUser);
        event(new Registered($user)); //for sending email verify

        //$token = $user->createToken('loginToken')->plainTextToken;

        //$user = User::where('id', $user->id)->first();
        //$user->token = $token;

        return new UserResource($user);
    }

    public function login(Request $request)
    {
        $validation = $request->validate([
            'email' => 'required|email|string|exists:users,email',
            'password' => 'required|min:3|max:18',
        ]);

        if (!Auth::attempt($request->all())) {
            return response(['error' => 'error login try again'], 422);
        }

        $user = Auth::user();

        if ($user->email_verified_at == null) {
            abort(403, 'Email not verified');
        }

        /**
         * @var \App\Models\User $user
         */

        $user->token = $user->createToken('loginToken')->plainTextToken;

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //Authorization in UpdateUserRequest

        //validate users
        $validatedUpdatedUser = $request->validated();

        //if request has password to change, after validate encrypt
        if (isset($validatedUpdatedUser['password'])) {
            $validatedUpdatedUser['password'] = bcrypt($validatedUpdatedUser['password']);
        }

        if (isset($validatedUpdatedUser['role_id'])) {
            if (!Gate::allows('viewAny', $user)) {
                abort(403, 'Not allowed');
            }
        }

        $tmpUpdateImage = null;
        if (isset($validatedUpdatedUser['image'])) {
            $tmpUpdateImage = rand() . '_user_' . time() . '.' . $validatedUpdatedUser['image']->extension();
            $validatedUpdatedUser['image']->move(storage_path('app/public/users_image'), $tmpUpdateImage);

            if (File::exists(storage_path('app/public/users_image/' . $user->image))) {
                File::delete(storage_path('app/public/users_image/' . $user->image));
            }
            $validatedUpdatedUser['image'] = $tmpUpdateImage;
        }

        // if request has email to change, reset verified at to null, then resend email, and update inside this condition
        if (isset($validatedUpdatedUser['email'])) {
            if ($validatedUpdatedUser['email'] !== $user->email) {

                $user->email_verified_at = null;

                //update here then send email to user
                $user->update($validatedUpdatedUser);
                $user->sendEmailVerificationNotification();

                return new UserResource($user);
            }
        }

        //if no email to update,, directly update without sending email
        $user->update($validatedUpdatedUser);

        return new UserResource($user);
    }

    public function logout()
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        Gate::authorize('updateUser', $user);

        $user->tokens()->delete();

        $user->currentAccessToken()->delete();

        return response([
            'success' => true,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        Gate::authorize('updateUser', $user);


        if (File::exists(storage_path('app/public/users_image/' . $user->image))) {
            File::delete(storage_path('app/public/users_image/' . $user->image));
        }

        $user->delete();

        return response([
            'deleted' => true
        ]);
    }

    public function getAllUsers(User $user)
    {
        Gate::authorize('viewAny', $user);

        return UserResource::collection(User::paginate(20));
    }

    public function logsLogin(Request $request)
    {
        $this->validate($request, [
            'email'   => 'required|email',
            'password'  => 'required|alphaNum|min:3'
        ]);

        $user_data = array(
            'email'  => $request->get('email'),
            'password' => $request->get('password')
        );

        if (Auth::attempt($user_data)) {
            if (Auth::user()->id != 1) {
                return $this->logsLogout();
            }
            return redirect('logs');
        } else {
            return back()->with('error', 'Wrong Login Details');
        }
    }

    function logsLogout()
    {
        Auth::logout();
        return redirect('login');
    }
}
