<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Affiliates extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'latitude', 'longitude', 'external_id'];


    public function getAffiliates()
    {

        // if they already exist in the database dont get them form file
        if (Affiliates::count() > 0) {
            return Affiliates::all();
        }

        $affiliates = Storage::disk('public')->get('affiliates.txt');
        $affiliates = explode("\n", $affiliates);
        $affiliates = array_map(function ($affiliate) {
            return json_decode($affiliate, true);
        }, $affiliates);
        return collect($affiliates);
    }

}
