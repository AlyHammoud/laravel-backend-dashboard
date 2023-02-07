<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SiteData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SiteDataController extends Controller
{
    public function index()
    {
        $site_data = SiteData::get();
        $site_data[0]->visit()->hourlyIntervals()->withIp();
        // $site_data[0]->visit()->customInterval(Carbon::now()->addMinute(1))->withIp();
    }

    public function getAllSiteData()
    {
        //site data to get all site visits for index [0] cant done for collection
        $site_data = SiteData::get();
        $year = request()->query('year', date("Y"));

        $visits =  $site_data[0]->popularAllTime()->first()->visit_count_total;

        $visitPerMonth =  DB::table('laravisits')->select('*')
            ->where('visitable_type', 'App\Models\SiteData')->whereYear('created_at', $year)
            ->get()->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('M');
            });

        $distinct_years_of_visits =
            DB::table('laravisits')->whereNotNull('created_at')->distinct()->get([DB::raw('YEAR(created_at) as year')]);

        $products_sum = Product::sum('price');

        $products_added_per_month =
            DB::table('products')->select('id', 'created_at')->whereYear('created_at', $year)
            ->get()->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('M');
            });

        $count_products = Product::count();

        $count_users = User::count();



        return [
            'siteVisitsPerMonth' => $visitPerMonth,
            'allSiteVisists' => $visits,
            'productPriceSum' => $products_sum,
            'countOfProducts' => $count_products,
            'countOfUsers' => $count_users,
            'years_of_visits' => $distinct_years_of_visits,
            'products_add_per_month' => $products_added_per_month,
            'test' => $site_data[0]->popularAllTime()->get()
        ];
    }
}
