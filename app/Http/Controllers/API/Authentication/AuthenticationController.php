<?php

namespace App\Http\Controllers\API\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Authentication\RegisterRequest;
use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Services\Authentication\AuthenticationService;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use DB;

class AuthenticationController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    protected $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  RegisterRequest  $request
     * @return \Illuminate\Http\Response
     */
    protected function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = $this->authService->register($request->name, $request->email, $request->password);
            $response = new UserResource($user);

            DB::commit();

            return $this->successResponse($response, "User account created successfully", Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollback();
            return $this->fatalErrorResponse($e);
        }
    }

    /**
     * Login
     *
     * @param  LoginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $response['type'] = 'Bearer';
            $response['token'] =  $user->createToken('BackupApp')-> accessToken;
            $response['user'] =  new UserResource($user);

            return $this->successResponse($response, 'User login successfully.', Response::HTTP_OK);
        } else {
            return $this->errorResponse(null, 'Invalid email or password', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
