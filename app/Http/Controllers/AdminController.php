<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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


    public function brand_edit($id){
        $brand = Brand::find($id);
        return view('admin.brand-edit',compact('brand'));
    }
    public function brand_update(Request $request) {
        // Validate করার সময় ইমেজের জন্য শর্ত দিন
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ], [
            // ইমেজের জন্য কাস্টম এরর ম্যাসেজ
            'image.mimes' => 'Image must be a file of type: png, jpg, jpeg.',
            'image.max' => 'Image size must not exceed 2MB.',
        ]);
    
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
    
        // Slug তৈরি করার সময় $request->name থেকে জেনারেট করুন
        $brand->slug = Str::slug($request->name);
    
        // চেক করুন ইমেজ ফাইল এসেছে কিনা
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // আগের ইমেজ ডিলিট করা হচ্ছে
            if (File::exists(public_path('uploads/brands/' . $brand->image))) {
                File::delete(public_path('uploads/brands/' . $brand->image));
            }
    
            // নতুন ফাইলের নাম এবং লোকেশন সেট করা হচ্ছে
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
    
            // ফাইল মুভ করুন সঠিক ডিরেক্টরিতে
            $image->move(public_path('uploads/brands'), $file_name);
    
            // নতুন ইমেজ ফাইল সেভ
            $brand->image = $file_name;
        } else {
            // যদি ইমেজ না থাকে, তাহলে একটি error message দেখানো হবে
            return redirect()->back()->withErrors(['image' => 'Please upload an image for the brand.']);
        }
    
        // ব্র্যান্ড সেভ
        $brand->save();
    
        // রিডিরেক্ট এবং সাকসেস মেসেজ
        return redirect()->route('admin.brands')->with('status', 'Brand Has Been Updated Successfully!');
    }
    
    
    public function GenerateBrandThumbnailsImage($image, $imageName) {
        $destinationPath = public_path('uploads/brands');
        $img = Image::make($image->path());  // Image::make() ব্যবহার করুন
        
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();  // aspectRation-এর ভুল সংশোধন
        })->save($destinationPath . '/' . $imageName);
    }
    
    
}
