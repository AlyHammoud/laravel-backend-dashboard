<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GeneralInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class GeneralInfosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$general_info = GeneralInfo::find(2);
	
	//if(is_null($general_info)){
   		//return response('');
	//}

	$general_info = GeneralInfo::all();
	if(count($general_info)){
		$general_info = $general_info[0];
	}else{
		return response('');
	}

        $general_info['logo'] = URL::to('/images/general_info/' .  $general_info['logo']);
        $general_info['company_image'] = URL::to('/images/general_info/' .  $general_info['company_image']);
        $general_info['company_simage'] = URL::to('/images/general_info/' .  $general_info['company_simage']);
        return response($general_info);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'logo' => 'required|image',
            'address' => 'required|string',
            'phone_number' => 'required|string',
            'facebook' => 'required|string',
            'insta' => 'required|string',
            'company_image' => 'required|image',
            'company_simage' => 'required|image',
            'company_name' => 'required|string',
            'moto' => 'required|string',
        ]);

        $validatedData['logo'] = $this->storeImage($validatedData['logo'], 'general_info');
        $validatedData['company_image'] = $this->storeImage($validatedData['company_image'], 'general_info');
        $validatedData['company_simage'] = $this->storeImage($validatedData['company_simage'], 'general_info');

        GeneralInfo::create($validatedData);

        return response([
            'success' => true
        ]);
    }

    private function storeImage($image, $path)
    {
        $tmpImage = rand() . $path . time() . '.' . $image->extension();
        $image->move(public_path('images/' . $path), $tmpImage);
        return $tmpImage;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GeneralInfo $generalInfo)
    {
        $validatedData = $request->validate([
            'logo' => 'required|sometimes|image',
            'address' => 'required|sometimes|string',
            'phone_number' => 'required|sometimes|string',
            'facebook' => 'required|sometimes|string',
            'insta' => 'required|sometimes|string',
            'company_image' => 'required|sometimes|image',
            'company_simage' => 'required|sometimes|image',
            'company_name' => 'required|sometimes|string',
            'moto' => 'required|sometimes|string',
        ]);

        if (isset($validatedData['logo'])) {
            $validatedData['logo'] = $this->updateImage($validatedData['logo'], $generalInfo->logo, 'general_info');
        }
        if (isset($validatedData['company_image'])) {
            $validatedData['company_image'] = $this->updateImage($validatedData['company_image'], $generalInfo->company_image, 'general_info');
        }
        if (isset($validatedData['company_simage'])) {
            $validatedData['company_simage'] = $this->updateImage($validatedData['company_simage'], $generalInfo->company_simage, 'general_info');
        }

        $generalInfo->update($validatedData);

        return response([
            'success' => true
        ]);
    }

    private function updateImage($newImage, $oldImage, $path)
    {
        if (File::exists('images/general_info/' . $oldImage)) {
            File::delete('images/general_info/' . $oldImage);
        }

        $tmpImage = rand() . $path . time() . '.' . $newImage->extension();
        $newImage->move(public_path('images/' . $path), $tmpImage);
        return $tmpImage;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
