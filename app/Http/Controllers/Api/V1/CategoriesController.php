<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search = Request::query('search', '');

        if (!$search) {
            $categories = Category::orderBy('created_at', 'desc')->paginate(20);

            return CategoryResource::collection($categories);
        }
        $categories = Category::orderBy('created_at', 'desc')
            ->whereTranslationLike('name', '%' . $search . '%')
            ->paginate(15);

        return CategoryResource::collection($categories);
    }

    public function getCategoriesNames()
    {
        $categories = DB::table('category_translations')
            ->join('categories', 'category_translations.category_id', 'categories.id')
            ->select('categories.id', 'category_translations.name')->orderBy('name', 'asc')->get();

        return $categories;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request)
    {
        $name_desc_translation = [];
        if ($request->name_translation || $request->description_translation) {
            //
            // foreach $key is en / ar / fr
            // (array)$request->name  if $request->name is null, then it converts it to emty array
            // to avaoid error in loopin through null
            // or instead use if() statement
            foreach ((array)$request->name_translation as $key => $name) {

                if ($name != "") {
                    $name_desc_translation =
                        [
                            ...$name_desc_translation, $key => ["name" => $name]
                        ];
                }
            }

            foreach ((array)$request->description_translation as $key => $description) {
                if ($description != '') {
                    //sometimes, transaltion doesnt have en, or ar for names, so it will give error
                    //since of no existence of a key, so create it instead of pushing into it
                    if (array_key_exists($key, $name_desc_translation)) {
                        $name_desc_translation[$key] = [
                            ...$name_desc_translation[$key], "description" => $description
                        ];
                    } else {
                        $name_desc_translation =
                            [
                                ...$name_desc_translation, $key => ["description" => $description]
                            ];
                    }
                }
            }
        }
        //storing images public

        $image_path = null;
        if ($request->image_url) {
            $image_path = rand() . '_category_' . time() . '.' . $request->image_url->extension();
            $request->image_url->move(public_path('images'), $image_path);
        }

        $request->user()->category()->create([
            'is_available' => $request->is_available,
            ...$name_desc_translation,
            'user_id' => $request->user()->id,
            'image_url' => $image_path
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //to show in dashboard, different from showing to clients
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoryRequest  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $image_path = null;
        if (!isset($data['image_url'])) {
            // use old image when no new image uploaded
            $image_path = $category->image_url;
        }
        //using sometimes validation: if sent from user a new image delete old and upload the new
        if (isset($data['image_url'])) {

            //delete old image from public path
            //validation of mime type goes here
            File::exists(public_path('images/' . $category->image_url)) && $category->image_url
                ? unlink(public_path('images/' . $category->image_url))
                : null;

            // if request has a new image to update then: it might be null, since user deleted it

            if ($data['image_url']) {
                $allowedfileExtension = ['pdf', 'jpg', 'png', 'docx'];

                if (in_array($data['image_url']->extension(), $allowedfileExtension)) {
                    $image_path = time() . '_category_' . '.' . $data['image_url']->extension();
                    $data['image_url']->move(public_path('images'), $image_path);
                    $data['image_url'] = $image_path;
                } else {
                    return response(['error' => 'file type not allowed'], 422);
                }
            }
            unset($data['image_url']);
        }

        $name_desc_translation = [];
        if (isset($data['name_translation'])) {
            foreach ($data['name_translation'] as $key => $name) {
                $name_desc_translation = [
                    ...$name_desc_translation, $key => ['name' => $name]
                ];
            }
            unset($data['name_translation']);
        }

        if (isset($data['description_translation'])) {
            foreach ($data['description_translation'] as $key => $description) {
                if (array_key_exists($key, $name_desc_translation)) {
                    $name_desc_translation[$key] = [
                        ...$name_desc_translation[$key], "description" => $description
                    ];
                } else {
                    $name_desc_translation =
                        [

                            ...$name_desc_translation, $key => ["description" => $description]
                        ];
                }
            }
            unset($data['description_translation']);
        }


        $category->update([
            ...$data,
            "image_url" => $image_path,
            ...$name_desc_translation
        ]);
        return new CategoryResource($category);
        return response(["susccess" => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        Gate::authorize('manageCategory', $category);

        $category->load('items', 'products');

        if ($category->image_url) {
            if (File::exists('images/' . $category->image_url)) {
                File::delete('images/' . $category->image_url);
            }
        }

        foreach ($category->items as $item) {
            foreach ($item->itemImages as $image) {
                if (File::exists('images/' . $image->image_url)) {
                    File::delete('images/' . $image->image_url);
                }
            }
        }

        foreach ($category->products as $product) {
            foreach ($product->productImages as $image) {
                if (File::exists('images/' . $image->image_url)) {
                    File::delete('images/' . $image->image_url);
                }
            }
        }

        $category->delete();

        return response(['success' => true]);
    }

    public function showOneForClients(Category $category)
    {
        if ($category->is_available) {
            $category->visit()->withIp();
            return new CategoryResource($category);
        }

        abort(404, 'Category not available');
    }

    public function allAvailable()
    {
        $categories = Category::where('is_available', 1)->get();

        return CategoryResource::collection($categories);
    }
}
