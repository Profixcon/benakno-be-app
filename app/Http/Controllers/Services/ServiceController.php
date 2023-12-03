<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ListDataRequest;
use App\Http\Requests\Services\ServiceRequest;
use App\Http\Resources\Location\DriverResource;
use App\Http\Resources\Services\ServiceResource;
use App\Models\Auth\AuthModel;
use App\Models\Chats\ChatRoomModel;
use App\Models\Location\DriverModel;
use App\Models\Services\ServiceDetailModel;
use App\Models\Services\ServiceModel;
use App\Models\Users\MitraDriverModel;
use App\Traits\FirebaseTrait;
use App\Traits\GlobalTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceController extends Controller
{
    use FirebaseTrait, GlobalTrait;

    public function index(ListDataRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $query = ServiceModel::with('customer', 'mitra', 'service', 'rating', 'bill', 'chat_room', 'chat_room.chats');

        // Apply search filters if search query is provided
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('mitra', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply sorting if sort_by and order query parameters are provided
        $sortBy = $request->query('sort_by');
        $order = $request->query('order');
        if ($sortBy && $order) {
            // Determine if the sorting column belongs to the parent table or related tables
            $isSortInRelatedTable = str_contains($sortBy, '.');
            $parentTable = getParentTable("ServiceModel");
            if ($isSortInRelatedTable) {

                list($relation, $sortColumn) = explode('.', $sortBy);
                // Determine the related table name based on the parent table
                $relatedTable = getRelatedTable($relation);

                // Determine the dynamic user_id column for the related table
                $relatedUserIdColumn = getRelatedColumn($relatedTable);

                // Join the related table and apply dynamic sorting
                $query->join($relatedTable, "$parentTable.$relatedUserIdColumn", '=', "$relatedTable.$relatedUserIdColumn")
                    ->orderBy("$relatedTable.$sortColumn", $order);
            } else {
                // Sort by a column in the parent table
                $query->orderBy("$parentTable.$sortBy", $order);
            }
        }

        $services = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        //check if bill is empty, then set it to null
        foreach ($services as $key => $val) {
            $val->bill = $val->bill == "[]" || empty($val->bill) ? null : $val->bill;
            $val->rating = $val->rating == "[]" || empty($val->rating) ? null : $val->rating;

            //check if rating not empty, then add new field called is_rated and set it to true
            $val->is_rated = !($val->rating == "[]" || empty($val->rating));

            if(!is_null($val->chat_room)){
                $val->chat_room->chats = $val->chat_room->chats == "[]" || empty($val->chat_room->chats) ? null : $val->chat_room->chats;
            }
        }

        $list = $services->isEmpty() ? null : ServiceResource::collection($services);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $services->currentPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
                'last_page' => $services->lastPage(),
            ],
        ], 200, 'Success get All services');
    }

    public function myServices(ListDataRequest $request, $user_id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        if (!$request->query('type')) {
            return response()->error('type is required', 400);
        }

        // Check if the user exists using the email
        $dataUser = AuthModel::where('user_id', $user_id)->first();
        if (!$dataUser) {
            return response()->error('User not found', 404);
        }

        if ($dataUser->role == 2) {
            //get mitra_id by driver_id on mitradrivermodel
            $mitraDriver = MitraDriverModel::where('driver_id', $user_id)->first();
            $user_id = $mitraDriver->mitra_id;
        }

        $query = ServiceModel::with('customer', 'vehicle', 'vehicle.brand', 'vehicle.type', 'mitra', 'service', 'service.service', 'rating', 'bill', 'chat_room', 'chat_room.chats');

        if ($request->query('type') == "customer") {
            $query->where('t_services.user_id', $user_id);
        } else {
            $query->where('mitra_id', $user_id);
        }

        // Apply search filters if search query is provided
        if ($request->has("status") && !is_null($request->query('status'))) {
            $query->where('status', $request->query('status'));
        }
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('mitra', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply sorting if sort_by and order query parameters are provided
        $sortBy = $request->query('sort_by');
        $order = $request->query('order');
        if ($sortBy && $order) {
            // Determine if the sorting column belongs to the parent table or related tables
            $isSortInRelatedTable = str_contains($sortBy, '.');
            $parentTable = $this->getParentTable("ServiceModel");
            if ($isSortInRelatedTable) {

                list($relation, $sortColumn) = explode('.', $sortBy);
                // Determine the related table name based on the parent table
                $relatedTable = $this->getRelatedTable($relation);

                // Determine the dynamic user_id column for the related t`able
                $relatedUserIdColumn = $this->getRelatedColumn($relatedTable);

                // Join the related table and apply dynamic sorting
                $query->join($relatedTable, "$parentTable.$relatedUserIdColumn", '=', "$relatedTable.$relatedUserIdColumn")
                    ->orderBy("$relatedTable.$sortColumn", $order);
            } else {
                // Sort by a column in the parent table
                $query->orderBy("$parentTable.$sortBy", $order);
            }
        }

        $services = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        //check if bill is empty, then set it to null
        foreach ($services as $key => $val) {
            $val->bill = $val->bill == "[]" || empty($val->bill) ? null : $val->bill;
            $val->rating = $val->rating == "[]" || empty($val->rating) ? null : $val->rating;

            //filter by request status if not same remove data from service
            if ($request->has("status") && !is_null($request->query('status'))) {
                if ($request->query('status') && $val->status != $request->query('status')) {
                    $services->forget($key);
                }
            }

            //get driver id by mitra id on mitradrivermodel
            $mitraDriver = MitraDriverModel::where('mitra_id', $val->mitra_id)->first();
            $driver_id = $mitraDriver->driver_id ?? null;

            //get data driver
            $driver = DriverModel::where('user_id', $driver_id)->first();

            $val->driver = new DriverResource($driver) ?? null;

            //check if rating not empty, then add new field called is_rated and set it to true
            $val->is_rated = !($val->rating == "[]" || empty($val->rating));

            if(!is_null($val->chat_room)){
                $val->chat_room->chats = $val->chat_room->chats == "[]" || empty($val->chat_room->chats) ? null : $val->chat_room->chats;
            }
        }

        $list = $services->isEmpty() ? null : ServiceResource::collection($services);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $services->currentPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
                'last_page' => $services->lastPage(),
            ],
        ], 200, 'Success get All Customer services');
    }

    public function store(ServiceRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $service = ServiceModel::create($request->validated());

            // Set the default status to 0 after creating the record
            $service->room_chat_id = Uuid::uuid4()->toString();
            $service->status = 0;
            $service->save();

            //if success, check if there is a service detail then loop it to insert on service detail model and set the service_id to the current service id
            if ($request->has('bill') && is_array($request->bill)) {
                foreach ($request->bill as $key => $val) {
                    // Create a new MitraServicesModel record for each service
                    $serviceDetail = new ServiceDetailModel([
                        'id' => Uuid::uuid4()->toString(), // Generate a new UUID for each record
                        'service_id' => $service->id,
                        'name' => $val['name'],
                        'cost' => $val['cost'],
                    ]);

                    $serviceDetail->save();
                }
            }

            //create chat room
            //set the sender to customer
            //set the participants to mitra and customer id
            //set the status to 0
            //set the service_id to the current service id
            $chatRoom = new ChatRoomModel([
                'id' => Uuid::uuid4()->toString(), // Generate a new UUID for each record
                'service_id' => $service->id,
                'sender' => $service->user_id,
                'participants' => $service->user_id . "," . $service->mitra_id,
                'status' => 0,
            ]);

            $chatRoom->save();

            DB::commit(); // Commit the transaction if all operations are successful

            $service = ServiceModel::with('customer', 'mitra', 'service', 'bill')->findOrFail($service->id);


            $service->bill = $service->bill == "[]" ? null : $service->bill;
            $service->rating = $service->rating == "[]" ? null : $service->rating;

            //get env fcm_active
            $fcm_active = env('FCM_ACTIVE', false);

            if ($fcm_active) {
                try {
                    //get user detail from participants that not sender
                    $user = AuthModel::where('user_id', $service->mitra_id)->first();



                    if ($user->role == 1) {
                        //get mitra_id by driver_id on mitradrivermodel
                        $mitraDriver = MitraDriverModel::where('mitra_id', $user->user_id)->first();
                        $driver_id = $mitraDriver->driver_id;
                    }

                    //check user role, if role = 1 then mitra else customer
                    $app = $user->role == 3 ? 'customer' : 'mitra';

                    //get title and body
                    $title = "Pesanan Baru";
                    $body = "Anda mendapatkan pesanan baru dari " . $service->customer->name ?? null;

                    //get custom data
                    $customData = [
                        'title' => $title,
                        'body' => $body,
                        'service_id' => $service->id,
                    ];

                    //get target app from user -> device_id
                    $targetApp = "user-".$driver_id;

                    //send notification to specific topic
                    $this->sendToDiscord("Sending FCM of new service order to ".$targetApp." with custom data:\n ".json_encode($customData));

                    $this->sendNotificationToTopic($targetApp, null, null, $customData, $app);

                    //get target app from user -> device_id
                    $targetApp = "user-".$user->user_id;

                    //send notification to specific topic
                    $this->sendToDiscord("Sending FCM of new service order to ".$targetApp." with custom data:\n ".json_encode($customData));

                    $this->sendNotificationToTopic($targetApp, null, null, $customData, $app);
                } catch (\Exception $e) {
                    return response()->error($e->getMessage(), 422);
                }
            }

            return response()->success(new ServiceResource($service), 201, 'Service created successfully');
        } catch (\Exception $e) {
            // return not valid token
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }

    public function show(ServiceModel $service, $id)
    {

        try {
            $service = ServiceModel::with('customer', 'vehicle', 'vehicle.brand', 'vehicle.type', 'mitra', 'service', 'service.service', 'rating', 'bill', 'chat_room', 'chat_room.chats')->findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Service not found', 404);
        }

        //check if rating not empty, then add new field called is_rated and set it to true
        $service->is_rated = !($service->rating == "[]" || empty($service->rating));

        return response()->success(new ServiceResource($service), 200, 'Service retrieved successfully');
    }

    public function update(ServiceRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            $service = ServiceModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Service / Transaction not found', 404);
        }

        try {
            DB::beginTransaction();

            $service->update($request->validated());

            // Assuming you have successfully saved the Mitra and AccessAuth records as mentioned in your code
            if ($request->has('bill') && is_array($request->bill)) {

                //delete all the service detail first, then insert the new one from the request bill
//                ServiceDetailModel::where('service_id', $service->id)->delete();

                //if success, check if there is a service detail then loop it to insert on service detail model and set the service_id to the current service id
                if ($request->has('bill')) {
                    foreach ($request->bill as $key => $val) {
                        // Create a new MitraServicesModel record for each service
                        $serviceDetail = new ServiceDetailModel([
                            'id' => Uuid::uuid4()->toString(), // Generate a new UUID for each record
                            'service_id' => $id,
                            'name' => $val['name'],
                            'cost' => $val['cost'],
                        ]);

                        $serviceDetail->save();
                    }
                }
            }

            DB::commit(); // Commit the transaction if all operations are successful

            $service = ServiceModel::with('customer', 'mitra', 'service', 'bill')->findOrFail($id);

            //get detail service with bill, then loop through bill if not empty then add the total cost to the sub_total
            $sub_total = 0;
            if (!empty($service->bill)) {
                foreach ($service->bill as $key => $val) {
                    $sub_total += $val->cost;
                }
            }

            //update total of services
            $service->update([
                'sub_total' => $sub_total,
                'total' => $sub_total + $service->service_fee,
            ]);

            $service->bill = $service->bill == "[]" || empty($service->bill == "[]") ? null : $service->bill;
            $service->rating = $service->rating == "[]" || empty($service->rating == "[]") ? null : $service->rating;


            return response()->success(new ServiceResource($service), 200, 'Service updated successfully');
        } catch (\Exception $e) {
            // return not valid token
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $service = ServiceModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Service not found', 404);
        }

        $service->delete(); // Soft delete
        return response()->success(null, 200, 'Service deleted successfully');
    }

    /**
     * Get the related table name based on the parent table name.
     *
     * @param string $parentTable
     * @return string
     */
    function getRelatedTable($parentTable)
    {
        switch ($parentTable) {
            case 'customer':
                return 'access_user';
            case 'mitra':
                return 'access_mitra';
            case 'services':
                return 'm_services';
            // Add more cases for other parent tables and related tables if needed
            default:
                return '';
        }
    }

    /**
     * Get the parent table name based on the model type input.
     *
     * @param string $modelType
     * @return string
     */
    function getParentTable($modelType)
    {
        switch ($modelType) {
            case 'AuthModel':
                return 'access_auth';
            case 'ServiceModel':
                return 't_services';
            case 'VehicleModel':
                return 't_vehicle';
            // Add more cases for other model types if needed
            default:
                return '';
        }
    }

    /**
     * Get the dynamic user_id column name for the related table.
     *
     * @param string $relatedTable
     * @return string
     */
    function getRelatedColumn($relatedTable)
    {
        switch ($relatedTable) {
            case 'access_customer':
                return 'user_id';
            case 'access_services':
                return 'user_id';
            case 'm_services':
                return 'id';
            // Add more cases for other related tables if needed
            default:
                return 'user_id';
        }
    }
}
