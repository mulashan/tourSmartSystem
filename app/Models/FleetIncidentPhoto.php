<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetIncidentPhoto extends Model
{
    protected $table = 'tbl_fleet_incident_photos';
    protected $fillable = ['incident_id', 'path'];
}