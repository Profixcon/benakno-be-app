<?php

namespace App\Traits;

use App\Models\Auth\AuthModel;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

trait RecordSignature
{
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $authModel = self::getDataLoginModel();
            $model->updated_at = Carbon::now();
            $model->updated_by =  $authModel ? $authModel->user_id : null;
        });

        static::creating(function ($model) {
            $authModel = self::getDataLoginModel();
            $model->id = Uuid::uuid4()->toString();
            $model->created_at = Carbon::now();
            $model->created_by = $authModel ? $authModel->user_id : null;
        });

        static::deleting(function ($model) {
            $authModel = self::getDataLoginModel();
            $model->deleted_at = Carbon::now();
            $model->deleted_by = $authModel ? $authModel->user_id : null;
        });
    }


    protected static function getDataLoginModel()
    {
        $data = null;
        try {
            $data = auth("api")->user();
        } catch (\Throwable $th) {
//             $result = [
//                 "status" => false,
//                 "message" => "Unauthorized",
//                 "data" => null,
//             ];
//            echo json_encode($result);
//            http_response_code(402);
//            die();
        }
        return $data;
    }
}
