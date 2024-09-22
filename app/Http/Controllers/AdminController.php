<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Http\Request;
Use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index(){
        return view('admin.index');
    }
    public function brands(){
        $brands = Brand::orderBy('id','DESC')->paginate(10);
        return view('admin.brands',compact('brands'));
    }
    public function add_brand(){
        return view('admin.brand-add');
    }
    public function brand_store(Request $request) {
        $request->validate([
            'name' =>'required',
            'slug' =>'required|unique:brands,slug',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);
    
        $brand = new Brand();
        $brand->name = $request->name;
        
        // Slug তৈরি করার সময় $request->name থেকে জেনারেট করুন
        $brand->slug = Str::slug($request->name);
    
        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
    
        // ফাইল মুভ করুন সঠিক ডিরেক্টরিতে
        $image->move(public_path('uploads/brands'), $file_name);
    
        // ইমেজ ফাইল সেভ
        $brand->image = $file_name;
    
        // ব্র্যান্ড সেভ
        $brand->save();
    
        // রিডিরেক্ট এবং সাকসেস মেসেজ
        return redirect()->route('admin.brands')->with('status', 'Brand Has Been Added Successfully!');
    }
    
    public function GenerateBrandThumbnailsImage($image, $imageName) {
        $destinationPath = public_path('uploads/brands');
        $img = Image::make($image->path());  // Image::make() ব্যবহার করুন
        
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();  // aspectRation-এর ভুল সংশোধন
        })->save($destinationPath . '/' . $imageName);
    }
    
    
}
