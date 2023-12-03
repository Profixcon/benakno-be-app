<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chats\ChatRequest;
use App\Http\Requests\Master\ListDataRequest;
use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\Chats\ChatResource;
use App\Http\Resources\Chats\ChatRoomResource;
use App\Http\Resources\Services\ServiceResource;
use App\Http\Resources\Users\AdminResource;
use App\Http\Resources\Users\CustomerResource;
use App\Http\Resources\Users\DriverResource;
use App\Http\Resources\Users\MitraResource;
use App\Models\Auth\AuthModel;
use App\Models\Chats\ChatModel;
use App\Models\Chats\ChatRoomModel;
use App\Models\Users\MitraDriverModel;
use App\Notifications\FirebaseNotification;
use App\Traits\FirebaseTrait;
use App\Traits\GlobalTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use FirebaseTrait, GlobalTrait;

    public function index(ListDataRequest $request)
    {
        $chat = ChatModel::all();

        $list = $chat->isEmpty() ? null : ChatResource::collection($chat);

        return response()->success($list, 200, 'All Chats retrieved successfully');
    }

    public function history(ListDataRequest $request, $user_id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }
        $user = AuthModel::where('user_id', $user_id)->first();

        if ($user->role == 2) {
            //get mitra_id by driver_id on mitradrivermodel
            $mitraDriver = MitraDriverModel::where('driver_id', $user_id)->first();
            $user_id = $mitraDriver->mitra_id;
        }

        //get all chat room where user is in participants
        //then USE with service table
        //then USE with sender according to sender type
        $chats = ChatRoomModel::where('participants', 'like', '%' . $user_id . '%')
            ->with('service')
            ->get();

        //order by updated_at
        $chats = $chats->sortByDesc('updated_at');

        //for each chat, get the user detail
        foreach ($chats as $chat) {
            // Check if the user exists using the email
            $dataUser = AuthModel::where('user_id', $chat->sender)->first();
            if (!$dataUser) {
                return response()->error('User not found', 404);
            }

            $userResource = $this->generateUserDetail($dataUser);
            $chat->sender = new UserResource($userResource->toArray(null));

            //check if participants is not null
            if (is_null($chat->participants)) {
                continue;
            }

            $chat->participants = explode(',', $chat->participants);

            $tempParticipants = [];

            foreach ($chat->participants as $key => $participant) {
                // Check if the user exists using the email
                $dataUser = AuthModel::where('user_id', $participant)->first();
                if (!$dataUser) {
                    return response()->error('User not found', 404);
                }

                $userResource = $this->generateUserDetail($dataUser);
                $tempParticipants[$key] = new UserResource($userResource->toArray(null));
            }

            $chat->participants = $tempParticipants;

            //set chat name according to sender name
            $chat->chat_name = $chat->sender['name'] ?? null;

            $chat->service = new ServiceResource($chat->service) ?? null;
        }

        //if chat empty then return null else return the chat list
        $chats = $chats->isEmpty() ? null : ChatRoomResource::collection($chats);

        return response()->success($chats, 200, 'All History Chats retrieved successfully');
    }

    public function store(ChatRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        //get user_id from jwt
        $user_id = auth('api')->user()->user_id;
        //$user_id = $request->sender_id;

        // check if chat room exist
        $chatRoom = ChatRoomModel::where('id', $request->room_id)->first();
        if (!$chatRoom) {
            return response()->error('Chat room not found', 404);
        }


        $user = AuthModel::where('user_id', $user_id)->first();

        if ($user->role == 2) {
            //get mitra_id by driver_id on mitradrivermodel
            $mitraDriver = MitraDriverModel::where('driver_id', $user_id)->first();
            $user_id = $mitraDriver->mitra_id;
        }

        //check if user id exist in participants of chat room
        $participants = explode(',', $chatRoom->participants);
        if (!in_array($user_id, $participants)) {
            return response()->error('User not found in participants of chat room', 404);
        }

        //replace sender id on request with user id
        $request->sender_id = $user_id;

        //create chat with custom payoad sender from user id
//$chat = ChatModel::create($request->validated());
        $chatsend = new ChatModel([
            'sender_id' => $user->user_id,
            'room_id' => $request->room_id,
            'message' => $request->message,
            'file' => null,
        ]);

        $chatsend->save();

        //if success update, updated_at of room chat by room_id
        $chatRoom->updated_at = $chatsend->created_at;
        $chatRoom->save();

        //get env fcm_active
        $fcm_active = env('FCM_ACTIVE', false);

        if ($fcm_active) {
            try {
                //get user detail from participants that not sender
                $participants = array_diff($participants, [$user_id]);
                $user = AuthModel::where('user_id', $participants)->first();

                //check user role, if role = 1 then mitra else customer
                $app = $user->role == 3 ? 'customer' : 'mitra';

                //if user role == 1 then get mitra_id by driver_id on mitradrivermodel
                if ($user->role == 1) {
                    //get mitra_id by driver_id on mitradrivermodel
                    $mitraDriver = MitraDriverModel::where('mitra_id', $user->user_id)->first();
                    $user = AuthModel::where('user_id', $mitraDriver->driver_id)->first();
                }

                $userSender = AuthModel::where('user_id', $chatsend->sender_id)->first();

                //get user name $user uset_id
                $users = $this->generateUserDetail($userSender);

                $users = $users->toArray(null);

                //ej($user['name']);

                //get title and body
                $title = 'You got new message';
                $body = $chatsend->message;

                //get custom data
                $customData = [
                    'title' => $title,
                    'body' => $body,
                    'chat_room_id' => $chatsend->room_id,
                    'sender_id' => $chatsend->sender_id,
                    'sender_name' => $users['name'],
                    'nama_customer' => $users['name'],
                    'nama_mitra' => $users['name'],
                    'nama_driver' => $users['name'],
                    'message' => $chatsend->message,
                    'file' => $chatsend->file,
                    'created_at' => $chatsend->created_at,
                ];

                //get target app from user -> device_id
                $targetApp = "user-".$user->user_id;
                //send notification to specific topic
                $notif = $this->sendNotificationToTopic($targetApp, null, null, $customData, $app);


                $this->sendToDiscord("Sending FCM of chat to ".$targetApp." with custom data:\n ".json_encode($customData)."\n\nResult: ".json_encode($notif));

            } catch (\Exception $e) {
                return response()->error($e->getMessage(), 422);
            }
        }

        $chatsend->is_me = true;
        return response()->success(new ChatResource($chatsend), 201, 'Message send successfully');
    }

    public function show(ChatModel $chat, $id)
    {
        //get user_id from jwt
        $user_id = auth('api')->user()->user_id;
        //get list of chats where chat room id = $id
        //then USE with sender according to sender type
        $chats = ChatModel::where('room_id', $id)->get();

        //order by created_at
        $chats = $chats->sortBy('created_at');

        //for each chat, get the user detail
        foreach ($chats as $chat) {
            // Check if the user exists using the email
            $dataUser = AuthModel::where('user_id', $chat->sender_id)->first();
            if (!$dataUser) {
                return response()->error('User not found', 404);
            }

            //if sender_id == user_id then set is_me = true, else set is_me = false
            $chat->is_me = $chat->sender_id == $user_id ? true : false;

            $userResource = $this->generateUserDetail($dataUser);
            $chat->sender = new UserResource($userResource->toArray(null));
        }

        //if chat empty then return null else return the chat list
        $chats = $chats->isEmpty() ? null : ChatResource::collection($chats);

        return response()->success($chats, 200, 'Cnats retrieved successfully');
    }

    public function update(ChatRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            $chatRoom = ChatModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Cnat room not found', 404);
        }

        $chatRoom->update($request->validated());
        return response()->success(new ChatResource($chatRoom), 200, 'Cnat room updated successfully');
    }

    public function destroy($id)
    {
        try {
            $chatRoom = ChatModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Cnat room not found', 404);
        }

        $chatRoom->delete(); // Soft delete
        return response()->success(null, 200, 'Cnat room deleted successfully');
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
}
