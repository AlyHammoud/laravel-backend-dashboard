<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SiteDataController extends Controller
{
    public function index()
    {
        $site_data = SiteData::get();
        $site_data[0]->visit()->hourlyIntervals()->withIp();
    }

    public function getAllSiteData()
    {
        $site_data = SiteData::get();
        $year = request()->query('year', date("Y"));

        $visits =  $site_data[0]->popularAllTime()->first()->visit_count_total;

        $visitPerMonth =  DB::table('laravisits')->select('*')
            ->where('visitable_type', 'App\Models\SiteData')->whereYear('created_at', $year)
            ->get()->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('M');
            });

        return ['siteVisitsPerMonth' => $visitPerMonth, 'allSiteVisists' => $visits];
    }
}
