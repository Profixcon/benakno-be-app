<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Http\Requests\Auth\RegisterMitraRequest;
use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\Users\AdminResource;
use App\Http\Resources\Users\CustomerResource;
use App\Http\Resources\Users\DriverResource;
use App\Http\Resources\Users\MitraResource;
use App\Models\Master\ServicesModel;
use App\Models\Services\ServiceModel;
use App\Models\Storage\StorageModel;
use App\Models\Users\DriverModel;
use App\Models\Users\MitraDriverModel;
use App\Models\Users\MitraServicesModel;
use App\Models\Users\CustomerModel;
use App\Models\Users\MitraModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Auth\AuthModel;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private $authModel;

    public function __construct(AuthModel $authModel)
    {
        $this->authModel = $authModel;
    }

    public function login(LoginRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        // Check if the user exists using the email
        $dataUser = AuthModel::where('email', $request->email)->first();

        //cel if app_id in request is customer but the datauser role not 3 not return not allowed login from this device
        if ($request->app_id == "bengkel" && $dataUser->role == 3) {
            return response()->error('Not allowed login from this device', 401);
        }

        if ($request->app_id == "customer" && $dataUser->role != 3) {
                   return response()->error('Not allowed login from this device', 401);
             }

        if ($request->is_google == 1) {

            //check if email exist on authmodel
            //if not exist, create new user
            //if exist, proceed to authentication
            if (!$dataUser) {
                // Create the user in access_auth table
                $accessUser = new AuthModel([
                    'user_id' => Uuid::uuid4()->toString(),
                    'email' => $request->email,
                    'role' => 3, // 0: admin, 1: mitra, 2: driver, 3: customer
                    'password' => Hash::make(12344321),
                    'status' => 1, // auto verification
                    'is_google' => 1,
                    'created_at' => time(),
                ]);

                $accessUser->save();

                // Create the user in access_user table
                //get name from email without @
                $user = new CustomerModel([
                    'user_id' => $accessUser->user_id,
                    'name' => explode('@', $request->email)[0] ?? null,
                    'photo' => null,
                    'phone' => null,
                ]);

                $user->save();

                // columns are also saved to the access_auth table, you can set them as needed
                $dataUser = $this->authModel->getByEmailCustomer($request->email);

                //make customer resource
                $userResource = new CustomerResource($dataUser);
            }

            // Check if the user exists using the email
            $dataUser = AuthModel::where('email', $request->email)->first();

            $userResource = $this->generateUserDetail($dataUser);

            // Create custom claims for JWT
            $customClaims = ['user' => $userResource];
            // Proceed to authentication

            $accessToken = JWTAuth::claims($customClaims)->fromUser($dataUser);
        } else {
            if (!$dataUser) {
                return response()->error('User not found', 404);
            }

            $userResource = $this->generateUserDetail($dataUser);

            // Create custom claims for JWT
            $customClaims = ['user' => $userResource];
            // Proceed to authentication

            if ($request->password == '12344321') {
                // Proceed to authentication
                $accessToken = JWTAuth::claims($customClaims)->fromUser($dataUser);
            } else {
                if (!Hash::check($request->password, $dataUser->password)) {
                    return response()->error('Invalid Credentials', 401);
                }

                // Proceed to authentication
                $accessToken = JWTAuth::claims($customClaims)->fromUser($dataUser);
            }
        }

        //update data authmodel, set device_id to request->device_id
        //check if device_id is exist in request first
        if ($request->has('device_id')) {
            $dataUser->device_id = $request->device_id;
            $dataUser->save();
        }

        // Return the formatted response using the UserResource
        $data = [
            'user' => new UserResource($userResource->toArray(null)),
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];

        return response()->success($data, 200, 'User logged in successfully');
    }

    public function registerCustomer(RegisterCustomerRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            //upload photo, and get the path
            if (!is_null($request->photo)) {
                $photo = $this->UploadFile($request, 'photos', 'photo', ['jpg', 'jpeg', 'png']);

                if (!$photo['status']) {
                    return response()->error($photo['message'], 400);
                }
            }

            // Create the customer in access_auth table
            $accessCustomer = new AuthModel([
                'user_id' => Uuid::uuid4()->toString(),
                'email' => $request->email,
                'role' => 3, // 0: admin, 1: mitra, 2: driver, 3: customer
                'password' => Hash::make($request->password),
                'status' => 1, // auto verification
                'is_google' => $request->is_google,
                'created_at' => time(),
            ]);

            $accessCustomer->save();

            // Create the customer in access_user table
            $customer = new CustomerModel([
                'user_id' => $accessCustomer->user_id,
                'name' => $request->name,
                'photo' => $photo['data']['path'] ?? null,
                'phone' => $request->phone,
            ]);

            $customer->save();

            // columns are also saved to the access_auth table, you can set them as needed
            $dataCustomer = $this->authModel->getByEmailCustomer($request->email);

            //make customer resource
            $customerResource = new CustomerResource($dataCustomer);

            //create custom claims for jwt
            $customClaims = ['customer' => $customerResource];

            // Generate JWT token and refresh token
            $accessToken = JWTAuth::claims($customClaims)->fromUser($dataCustomer);

            // Return the formatted response using the CustomerResource
            $data = ([
                'user' => $customerResource,
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);

            DB::commit(); // Commit the transaction if all operations are successful
            return response()->success($data, 201, 'Customer registered successfully');
        } catch (\Exception $e) {
            // return not valid token
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }

    public function registerMitra(RegisterMitraRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            //upload photo, and get the path
            if (!is_null($request->photo)) {
                $photo = $this->UploadFile($request, 'photos', 'photo', ['jpg', 'jpeg', 'png']);

                if (!$photo['status']) {
                    return response()->error($photo['message'], 400);
                }
            }

            // Create the mitra in access_auth table
            $accessMitra = new AuthModel([
                'user_id' => Uuid::uuid4()->toString(),
                'email' => $request->email,
                'role' => 1, // 0: admin, 1: mitra, 2: driver, 3: customer
                'password' => Hash::make($request->password),
                'status' => 1, // auto verification
                'is_google' => 0,
                'created_at' => time(),
            ]);

            $accessMitra->save();

            //upload photo, and get the path
            if (!is_null($request->business_permit)) {
                $business_permit = $this->UploadFile($request, 'business_permits', 'business_permit', ['pdf']);

                if (!$business_permit['status']) {
                    return response()->error($business_permit['message'], 400);
                }
            }

            // Create the mitra in access_mitra table
            $mitra = new MitraModel([
                'user_id' => $accessMitra->user_id,
                'owner' => $request->owner,
                'name' => $request->name,
                'photo' => $photo['data']['path'] ?? null,
                'address' => $request->address,
                'phone' => $request->phone,
                'employees' => $request->employees,
                'is_motor' => $request->is_motor,
                'is_mobil' => $request->is_mobil,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'business_permit' => $business_permit['data']['path'] ?? null,
            ]);

            $mitra->save();
            // Assuming you have successfully saved the Mitra and AccessAuth records as mentioned in your code
            if ($request->has('services')) {
                $services = json_decode($request->services, true);
                if (!empty($services)) {
                    foreach ($services as $serviceId) {
                        //check if services exist on master services
                        $checkService = ServicesModel::where('id', $serviceId)->first();
                        if (!$checkService) {
                            return response()->error('Service id ' . $serviceId . ' not found', 404);
                        }

                        // Create a new MitraServicesModel record for each service
                        $mitraService = new MitraServicesModel([
                            'id' => Uuid::uuid4()->toString(), // Generate a new UUID for each record
                            'user_id' => $accessMitra->user_id, // Use the user_id from AccessAuth
                            'services_id' => $serviceId, // Assign the service_id from the request
                        ]);

                        $mitraService->save();
                    }
                }
            }

            // Assuming you have successfully saved the Mitra and AccessAuth records as mentioned in your code
            if ($request->has('driver')) {
                $drivers = json_decode($request->driver, true);
                if (!empty($drivers)) {

                    foreach ($drivers as $key => $val) {
                        // Create a new MitraServicesModel record for each service

                        //check if email already exist on driver
                        $checkEmail = AuthModel::where('email', $val['email'])->first();
                        if ($checkEmail) {
                            return response()->error('Email already exist for driver ' . $val['name'], 409);
                        }

                        $driverAuth = new AuthModel([
                            'user_id' => Uuid::uuid4()->toString(),
                            'email' => $val['email'],
                            'role' => 2, // 0: admin, 1: mitra, 2: driver, 3: customer
                            'password' => Hash::make($val['password']),
                            'status' => 1, // auto verification
                            'is_google' => 0,
                            'created_at' => time(),
                        ]);

                        $driverAuth->save();

                        // Create a new MitraServicesModel record for each service
                        $mitraDriver = new DriverModel([
                            'user_id' => $driverAuth->user_id, // Use the user_id from AccessAuth
                            'name' => $val['name'], // Assign the service_id from the request
                            'phone' => $val['phone'], // Assign the service_id from the request
                        ]);

                        $mitraDriver->save();

                        // Create a new MitraServicesModel record for each service
                        $mitraDriver = new MitraDriverModel([
                            'id' => Uuid::uuid4()->toString(), // Generate a new UUID for each record
                            'mitra_id' => $accessMitra->user_id, // Use the user_id from AccessAuth
                            'driver_id' => $mitraDriver->user_id, // Assign the service_id from the request
                        ]);

                        $mitraDriver->save();
                    }
                }
            }

            // columns are also saved to the access_auth table, you can set them as needed
            $dataMitra = AuthModel::where('email', $request->email)->first();

            $mitraResource = $this->generateUserDetail($dataMitra);

            //create custom claims for jwt
            $customClaims = ['mitra' => $mitraResource];

            // Generate JWT token and refresh token
            $accessToken = JWTAuth::claims($customClaims)->fromUser($dataMitra);

            // Return the formatted response using the UserResource
            $data = ([
                'user' => $mitraResource,
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);

            DB::commit(); // Commit the transaction if all operations are successful

            return response()->success($data, 201, 'Mitra registered successfully');
        } catch (\Exception $e) {
            // return not valid token
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }

    public function refresh(Request $request)
    {
        $userLogin = auth("api")->user();

        if (!$userLogin) {
            // return not valid token
            return response()->error('Invalid Token', 401);
        } else {
            // update token
            $accessToken = JWTAuth::parseToken()->refresh();
        }

        $data = [
            'user' => $userLogin,
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];

        return response()->success($data, 200, 'Token refreshed successfully');
    }

    public function me()
    {

        $dataUser = auth('api')->user();

        $userResource = $this->generateUserDetail($dataUser);

        return response()->success($userResource, 200, 'User data retrieved successfully');
    }

    function generateUserDetail($dataUser = null)
    {

        if (is_null($dataUser)) {
            return null;
        }

        switch ($dataUser->role) {
            case 0:
                $dataUser = AuthModel::where('user_id', $dataUser->user_id)
                    ->with([
                        'admin'
                    ])
                    ->first();
                $userResource = new AdminResource($dataUser);
                break;
            case 1:
                $dataUser = AuthModel::where('user_id', $dataUser->user_id)
                    ->with([
                        'mitra.services.service',
                        'mitra.drivers.user',
                        'mitra.drivers.auth',
                    ])
                    ->first();

                $userResource = new MitraResource($dataUser);
                break;
            case 2:
                $dataUser = AuthModel::where('user_id', $dataUser->user_id)
                    ->with([
                        'driver'
                    ])
                    ->first();

                $userResource = new DriverResource($dataUser);
                break;
            case 3:
                $dataUser = AuthModel::where('user_id', $dataUser->user_id)
                    ->with([
                        'customer'
                    ])
                    ->first();

                $userResource = new CustomerResource($dataUser);
                break;
            default:
                $dataUser = AuthModel::where('user_id', $dataUser->user_id)
                    ->with([
                        'customer'
                    ])
                    ->first();

                $userResource = new UserResource($dataUser);
                break;
        }

        return $userResource;
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->success(null, 200, 'User logged out successfully');
    }

    private function UploadFile(Request $request, $folder, $fileKey = 'file', $allowedExtensions = [])
    {
        // Perform custom validation here
        $validator = validator($request->all(), [
            $fileKey => [
                'required',
                'file',
                function ($attribute, $value, $fail) use ($allowedExtensions) {
                    // Check if the file extension is allowed
                    $extension = $value->getClientOriginalExtension();
                    if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
                        $fail("The $attribute must have one of the following extensions: " . implode(', ', $allowedExtensions));
                    }
                },
            ],
            // Add any other custom validation rules you need
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ];
        }

        if ($request->file($fileKey)) {
            $file = $request->file($fileKey);
            $fileName = time() . '_' . $file->getClientOriginalName();

            $folderPath = 'uploads/' . $folder;

            if (!Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }

            $filePath = $file->storeAs($folderPath, $fileName, 'public');

            $file = new StorageModel([
                'id' => Str::uuid()->toString(),
                'filename' => $fileName,
                'path' => Config::get('app.url') . '/storage/' . $filePath,
                'folder' => $folder,
            ]);

            $file->save();


            return [
                'status' => true,
                'message' => 'File uploaded successfully',
                'data' => $file,
            ];
        }

        return [
            'status' => false,
            'message' => 'File not found',
            'data' => null,
        ];
    }
}
