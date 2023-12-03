<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraModel extends Model
{
    use HasFactory;

    protected $table = 'access_mitra';
    protected $keyType = 'string'; // Set the primary key type to 'string'
    public $incrementing = false; // Disable auto-incrementing primary key
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
        'owner',
        'name',
        'photo',
        'phone',
        'address',
        'employees',
        'is_motor',
        'is_mobil',
        'business_permit',
        'latitude',
        'longitude',
        'open_hours',
        'is_open',
    ];
    public $timestamps = false; // Disable the default timestamps for this model
    // Other methods and relationships as needed

    public function drivers()
    {
        return $this->hasMany(MitraDriverModel::class, 'mitra_id', 'user_id');
    }

    public function services()
    {
        return $this->hasMany(MitraServicesModel::class, 'user_id', 'user_id');
    }
    public function getDistance($request, $latitude, $longitude, $unit = 'K')
    {
        $lat1 = $latitude;
        $lon1 = $longitude;
        $lat2 = $request->latitude;
        $lon2 = $request->longitude;
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1))
            * sin(deg2rad($lat2))
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
        if ($unit == "K") {
            return round(($miles * 1.609344), 2) . ' km';
        } else if ($unit == "N") {
            return round(($miles * 0.8684), 2) . ' nm';
        } else {
            return round($miles, 2) . ' mi';
        }
    }

    //count rating star by user id
    public function getRating($rating)
    {
        $total = 0;
        $count = 0;
        foreach ($rating as $val) {
            $total += $val->rating;
            $count++;
        }
        //check devinsion by zero
        if ($count == 0) {
            return 0;
        }
        return round($total / $count, 1);
    }
}
