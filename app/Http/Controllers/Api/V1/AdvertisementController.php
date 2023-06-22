<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdsRequest;
use App\Http\Requests\UpdateAdsRequest;
use App\Http\Resources\V1\ProductResource;
use App\Models\Ads;
use App\Models\Product;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;

class AdvertisementController extends Controller
{
    public function index()
    {
        $all_ads = Ads::all();

        foreach ($all_ads as $key => $value) {
            $value['product_ids'] = explode(',', $value['product_ids'][0]);

            $all_ads[$key]['products'] = ProductResource::collection(Product::where("is_available", 1)->whereIn('id', $value['product_ids'])->paginate(50));
            $all_ads[$key]['image'] = URL::to('images/ads/' . $all_ads[$key]['image']);
        }


        return response(
            $all_ads
        );
    }

    public function show(Ads $ad)
    {
        $page = request()->query('page', '');
        $full = request()->query('full', '');

        $ad['product_ids'] = explode(',', $ad['product_ids'][0]);

        if (!$page && !$full) {
            $ad['products'] =  ProductResource::collection(
                Product::where("is_available", 1)
                    ->whereIn('id', $ad['product_ids'])
                    ->whereHas('item', function ($query) {
                        $query->where('is_available', 1);
                    })
                    ->whereHas('item.category', function ($query) {
                        $query->where('is_available', 1);
                    })
                    ->paginate(50)
            );

            $ad['image'] = URL::to('images/ads/' . $ad['image']);
            return response($ad);
        }

        if ($full) {
            $ad['image'] = URL::to('images/ads/' . $ad['image']);
            return response($ad);
        }

        $products =  ProductResource::collection(
            Product::where("is_available", 1)
                ->whereIn('id', $ad['product_ids'])
                ->whereHas('item', function ($query) {
                    $query->where('is_available', 1);
                })
                ->whereHas('item.category', function ($query) {
                    $query->where('is_available', 1);
                })
                ->paginate(20)
        );

        return $products;
    }

    public function update(UpdateAdsRequest $request, Ads $ad)
    {
        $data = $request->validated();

        if (isset($data['image'])) {
            if (File::exists('images/ads/' . $ad['image'])) {
                File::delete('images/ads/' . $ad['image']);
            }

            $image_path = rand() . 'ADS' . time() . '.' . $data['image']->extension();
            $data['image']->move(public_path('images/ads'), $image_path);
            $data['image'] = $image_path;
        }
        $ad->update($data);
        return response(['status' => true]);
    }

    public function destroy(Ads $ad)
    {
        if (File::exists('images/ads/' . $ad->image)) {
            File::delete('images/ads/' . $ad->image);
        }

        $ad->delete();

        return response([
            'status' => true
        ]);
    }



    public function store(StoreAdsRequest $request)
    {

        $image_path = rand() . 'ADS' . time() . '.' . $request->image->extension();
        $request->image->move(public_path('images/ads'), $image_path);

        Ads::create([
            'image' => $image_path,
            'description' => $request->description,
            'product_ids' => $request->product_ids
        ]);

        return response(['success' => true]);
    }
}
