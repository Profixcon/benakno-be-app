<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ListDataRequest;
use App\Http\Requests\Users\UpdateMitraRequest;
use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\Users\MitraResource;
use App\Models\Auth\AuthModel;
use App\Models\Users\MitraDriverModel;
use App\Models\Users\MitraModel;
use App\Models\Users\MitraServicesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $query = AuthModel::with('mitra', 'mitra.drivers', 'mitra.drivers.auth', 'mitra.drivers.user', 'ratingMitra')->withRole(1);

        // Apply search filters if search query is provided
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('mitra', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Get latitude and longitude from request query params
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');

        // Search within a radius of latitude and longitude 10km with has from mitra
        if ($latitude && $longitude) {
            $query->whereHas('mitra', function ($q) use ($latitude, $longitude) {
                $q->select('user_id', 'name', 'photo', 'phone', 'address', 'latitude', 'longitude', 'open_hours', 'is_open')
                    ->selectRaw('( 6371 * acos( cos( radians(?) ) *
                cos( radians( latitude ) )
                * cos( radians( longitude ) - radians(?)
                ) + sin( radians(?) ) *
                sin( radians( latitude ) ) )
                ) AS distance', [$latitude, $longitude, $latitude])
                    ->havingRaw('distance < ?', [60]);
            });
        } else {
            // Apply sorting if sort_by and order query parameters are provided
            $sortBy = $request->input('sort_by');
            $order = $request->input('order');
            if ($sortBy && $order) {
                // Determine if the sorting column belongs to 'AuthModel' or 'customer' relationship
                $isSortInCustomer = str_starts_with($sortBy, 'mitra.');
                $sortColumn = $isSortInCustomer ? str_replace('mitra.', '', $sortBy) : $sortBy;

                // If sorting by a column in the 'customer' relationship, join the 'customer' table
                if ($isSortInCustomer) {
                    $query->join('access_mitra', 'access_auth.user_id', '=', 'access_mitra.user_id');
                }

                // Apply dynamic sorting
                $query->orderBy($isSortInCustomer ? 'access_mitra.' . $sortColumn : 'access_auth.' . $sortColumn, $order);
            }
        }

        //check if mitra is_mobile or is_motor
        $is_motor = $request->query('is_motor');
        if ($request->has("is_motor")) {
            $query->whereHas('mitra', function ($q) use ($is_motor) {
                if ($is_motor == 1) {
                    $q->where('is_motor', 1);
                } else {
                    $q->where('is_mobil', 1);
                }
            });
        }

        $status = $request->query('status');
        if ($request->has("status")) {
            $query->whereHas('mitra', function ($q) use ($status) {
                $q->where('is_open', $status);
            });
        }

        //add filter by mitra services
        $service_id = $request->query('service_id');
        if ($service_id) {
            $query->whereHas('mitra.services', function ($q) use ($service_id) {
                $q->where('services_id', $service_id);
            });
        }


        $mitras = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        //check if open_hours from $mitras is available 05:00 - 17:00
        foreach ($mitras as $key => $value) {
            if (!empty($value->mitra->open_hours)) {
                $open_hours = explode(" - ", $value->mitra->open_hours);
                $open_hours_start = strtotime($open_hours[0]);
                $open_hours_end = strtotime($open_hours[1]);
                $now = strtotime(date("H:i"));
                if ($now < $open_hours_start || $now > $open_hours_end) {
                    unset($mitras[$key]);
                }
            }
        }

        foreach ($mitras as $key => $value) {
            if ($value->ratingMitra == "[]") {
                $mitras[$key]->ratingMitra = null;
            }
        }

        $list = $mitras->isEmpty() ? null : MitraResource::collection($mitras);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $mitras->currentPage(),
                'per_page' => $mitras->perPage(),
                'total' => $mitras->total(),
                'last_page' => $mitras->lastPage(),
            ],
        ], 200, 'Success get all mitras');
    }


    public function show($id)
    {

        // Check if the user exists using the email
        $dataUser = AuthModel::where('user_id', $id)->first();
        if (!$dataUser) {
            return response()->error('User not found', 404);
        }

        if ($dataUser->role == 2) {
            //get mitra_id by driver_id on mitradrivermodel
            $mitraDriver = MitraDriverModel::where('driver_id', $id)->first();
            $id = $mitraDriver->mitra_id;
        }

        $mitra = AuthModel::with('mitra', 'ratingMitra')->find($id);

        if (!$mitra) {
            return response()->error('Mitra not found', 404);
        }

        return response()->success(new MitraResource($mitra), 200, 'Success get mitra details');
    }

    public function update(UpdateMitraRequest $request, $user_id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            // Assuming you have a MitraModel record for the $id you want to update
            $mitra = MitraModel::find($user_id);

            if (!$mitra) {
                return response()->error('Mitra not found', 404);
            }

            // Update the mitra information if provided
            $mitra->owner = $request->owner ?? $mitra->owner;
            $mitra->name = $request->name ?? $mitra->name;
            $mitra->address = $request->address ?? $mitra->address;
            $mitra->phone = $request->phone ?? $mitra->phone;
            $mitra->employees = $request->employees ?? $mitra->employees;
            $mitra->is_motor = $request->is_motor ?? $mitra->is_motor;
            $mitra->is_mobil = $request->is_mobil ?? $mitra->is_mobil;
            $mitra->latitude = $request->latitude ?? $mitra->latitude;
            $mitra->longitude = $request->longitude ?? $mitra->longitude;
            $mitra->open_hours = $request->open_hours ?? $mitra->open_hours;
            $mitra->is_open = $request->is_open ?? $mitra->is_open;

            // Upload and update photo if provided
            if (!is_null($request->photo)) {
                $photo = UploadFile($request, 'photos', 'photo', ['jpg', 'jpeg', 'png']);

                if (!$photo['status']) {
                    return response()->error($photo['message'], 400);
                }

                // Update the photo path
                $mitra->photo = $photo['data']['path'];
            }

            // Upload and update business permit if provided
            if (!is_null($request->business_permit)) {
                $business_permit = UploadFile($request, 'business_permits', 'business_permit', ['pdf']);

                if (!$business_permit['status']) {
                    return response()->error($business_permit['message'], 400);
                }

                // Update the business permit path
                $mitra->business_permit = $business_permit['data']['path'];
            }

            $mitra->save();

            // Assuming you have successfully updated the MitraModel record

            $dataUser = AuthModel::where('user_id', $user_id)
                ->with([
                    'mitra.services.service',
                    'mitra.drivers.user',
                    'mitra.drivers.auth',
                ])
                ->first();

            $userResource = new MitraResource($dataUser);

            DB::commit(); // Commit the transaction if all operations are successful

            return response()->success($userResource, 200, 'Mitra updated successfully');
        } catch (\Exception $e) {
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }

    public function servicesMitra($id)
    {
        //find from mitra services model where column user_id = $userid
        $mitraServices = MitraServicesModel::with('service')
            ->where('user_id', $id)
            ->get();

        if (!$mitraServices) {
            return response()->error('Mitra not found', 404);
        }

        $services = null;
        if (!empty($mitraServices)) {
            foreach ($mitraServices as $val) {
                $services[] = [
                    'id' => $val->id,
                    'name' => $val->service->name ?? null,
                    'price' => $val->price,
                    'description' => $val->service->description ?? null,
                    'status' => $val->status,
                ];
            }
        }

        return response()->success($services, 200, 'Success get mitra services');
    }


    //update services mitra
    public function updateServicesMitra(Request $request, $user_id)
    {

        //find from mitra services model where column user_id = $userid
        $mitraServices = MitraServicesModel::with('service')
            ->where('user_id', $user_id)
            ->get();

        if (!$mitraServices) {
            return response()->error('Mitra not found', 404);
        }

        try {
            DB::beginTransaction();

            //update is_motor and is_mobile

            // Assuming you have a MitraModel record for the $id you want to update
            $mitra = MitraModel::find($user_id);

            if (!$mitra) {
                return response()->error('Mitra not found', 404);
            }

            $mitra->is_motor = $request->is_motor ?? $mitra->is_motor;
            $mitra->is_mobil = $request->is_mobil ?? $mitra->is_mobil;
            $mitra->save();

            // Assuming you have successfully saved the Mitra and AccessAuth records as mentioned in your code
            if ($request->has('services') && is_array($request->services)) {

                // Get the existing service IDs associated with the Mitra
                $existingServiceIds = MitraServicesModel::where('user_id', $user_id)
                    ->pluck('id')
                    ->toArray();
                foreach ($request->services as $key => $val) {
                    // Check if the service ID exists in the existing service IDs
                    if (in_array($val['id'], $existingServiceIds)) {
                        // Update the existing record
                        MitraServicesModel::where('user_id', $user_id)
                            ->where('id', $val['id'])
                            ->update([
                                'price' => $val['price'] ?? 0, // Set the default price to 0
                                'status' => $val['status'] ?? 0, // Set the default status to 0
                            ]);
                    } else {
                        // Create a new MitraServicesModel record for the new service
                        $mitraService = new MitraServicesModel([
                            'id' => Uuid::uuid4()->toString(), // Generate a new UUID for each record
                            'user_id' => $user_id, // Use the user_id from AccessAuth
                            'services_id' => $val['service_id'], // Assign the service_id from the request
                            'price' => $val['price'] ?? 0, // Set the default price to 0
                            'status' => $val['status'] ?? 0, // Set the default status to 0
                        ]);

                        $mitraService->save();
                    }
                }

                //find from mitra services model where column user_id = $userid
                $mitraServices = MitraServicesModel::with('service')
                    ->where('user_id', $user_id)
                    ->get();

                $servicess = null;
                if (!empty($mitraServices)) {
                    foreach ($mitraServices as $val) {
                        $servicess[] = [
                            'id' => $val->id ?? null,
                            'name' => $val->service->name ?? null,
                            'price' => $val->price,
                            'description' => $val->service->description ?? null,
                            'status' => $val->status,
                        ];
                    }
                }
            }

            DB::commit(); // Commit the transaction if all operations are successful
            return response()->success($servicess, 200, 'Success update mitra services');
        } catch (\Exception $e) {
            // return not valid token
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }

    //delete mitra from access_auth and access_mitra
    public function destroy($user_id)
    {
        try {
            DB::beginTransaction();

            // Assuming you have a MitraModel record for the $id you want to update
            $mitra = MitraModel::find($user_id);

            if (!$mitra) {
                return response()->error('Mitra not found', 404);
            }

            //delete from access_auth
            $auth = AuthModel::where('user_id', $user_id)->first();
            $auth->delete();

            //delete from access_mitra
            $mitra->delete();

            DB::commit(); // Commit the transaction if all operations are successful
            return response()->success(null, 200, 'Success delete mitra');
        } catch (\Exception $e) {
            // return not valid token
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }
}
