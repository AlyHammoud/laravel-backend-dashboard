<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Coderflex\Laravisit\Concerns\CanVisit;
use Coderflex\Laravisit\Concerns\HasVisits;

class SiteData extends Model implements CanVisit
{
    use HasFactory;
    use HasVisits;

    protected $fillable = ['site_visits'];
}
