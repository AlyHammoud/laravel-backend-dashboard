<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Models\SiteData;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SiteDataController extends Controller
{
    public function index()
    {
        $site_data = SiteData::get();
        //$site_data[0]->visit()->hourlyIntervals()->withIp();
        $site_data[0]->visit()->customInterval(Carbon::now()->subSeconds(300))->withIp();
    }

    public function getMostViewedProducts()
    {
        $top_product_visits_ids_count =
            Product::join('laravisits', 'products.id', '=', 'laravisits.visitable_id')
            ->join('items', 'products.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where('items.is_available', 1)
            ->where('categories.is_available', 1)
            ->where('laravisits.visitable_type', 'App\Models\Product')
            ->select(DB::raw('COUNT(products.id) as count'), 'products.*')
            ->where('products.is_available', '1')
            ->groupBy('products.id', 'products.is_available', 'products.price', 'item_id', 'products.created_at', 'products.updated_at')
            // ->groupBy('products.id') // use when config/database.php mysql mode [allaw group by one column]
            ->orderBy(DB::raw('COUNT(products.id)'), 'desc')
            ->limit(6)
            ->get();

        return  ProductResource::collection($top_product_visits_ids_count);
    }

    public function getMostViewedProductsByCategory()
    {
        $cat = request()->query('cat', '');

        if ($cat) {
            $product = Category::with('products')->where('id', $cat)->get();
            $prds = $product[0]['products']->pluck('id');
            $top_product_visits_ids_count =
                Product::join('laravisits', 'products.id', '=', 'laravisits.visitable_id')
                ->join('items', 'products.item_id', '=', 'items.id')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->where('items.is_available', 1)
                ->where('categories.is_available', 1)
                ->where('laravisits.visitable_type', 'App\Models\Product')
                ->select(DB::raw('COUNT(products.id) as count'), 'products.*')
                ->where('products.is_available', '1')
                ->groupBy('products.id', 'products.is_available', 'products.price', 'item_id', 'products.created_at', 'products.updated_at')
                ->whereIn('products.id', $prds)
                // ->groupBy('products.id') // use when config/database.php mysql mode [allaw group by one column]
                ->orderBy(DB::raw('COUNT(products.id)'), 'desc')
                ->limit(6)
                ->get();


            //->where('item.category.id', $cat)
            return  ProductResource::collection($top_product_visits_ids_count);
        }

        abort(404, 'page not found');
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


        // only get available yeard
        $distinct_years_of_visits = DB::table('laravisits')
            ->whereNotNull('created_at')
            ->distinct()
            ->get([DB::raw('YEAR(created_at) as year')]);

        $products_sum = Product::sum('price');

        $products_added_per_month =
            DB::table('products')->select('id', 'created_at')->whereYear('created_at', $year)
            ->get()->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('M');
            });

        $count_products = Product::count();

        $count_users = User::count();

        $top_product_visits_ids_count =
            Product::join('laravisits', 'products.id', '=', 'laravisits.visitable_id')
            ->where('laravisits.visitable_type', 'App\Models\Product')
            ->join('items', 'products.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where('items.is_available', 1)
            ->where('categories.is_available', 1)
            ->select(DB::raw('COUNT(products.id) as count'), 'products.*')
            ->where('products.is_available', '1')
            ->groupBy('products.id', 'products.is_available', 'products.price', 'item_id', 'products.created_at', 'products.updated_at')
            // ->groupBy('products.id') // use when config/database.php mysql mode [allaw group by one column]
            ->orderBy(DB::raw('COUNT(products.id)'), 'desc')
            ->limit(4)
            ->get();


        return [
            'siteVisitsPerMonth' => $visitPerMonth,
            'allSiteVisists' => $visits,
            'productPriceSum' => $products_sum,
            'countOfProducts' => $count_products,
            'countOfUsers' => $count_users,
            'years_of_visits' => $distinct_years_of_visits,
            'products_add_per_month' => $products_added_per_month,
            // 'test' => $site_data[0]->popularAllTime()->get(),
            'most_visited_products' =>   ProductResource::collection($top_product_visits_ids_count)
        ];
    }
}
