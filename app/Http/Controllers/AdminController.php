<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
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
        // Validate the input including making image required
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'
        ], [
            // Custom error message for image
            'image.required' => 'Please upload an image for the brand.',
            'image.mimes' => 'The image must be a file of type: png, jpg, jpeg.',
            'image.max' => 'The image size must not exceed 2MB.'
        ]);
    
        $brand = new Brand();
        $brand->name = $request->name;
    
        // Generate the slug from $request->name
        $brand->slug = Str::slug($request->name);
    
        // Image Upload
        $image = $request->file('image');
        $file_extension= $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extension;
        $this->GenerateBrandThumbnailsImage($image,$file_name);
        $brand->image =$file_name;
        $brand->save();
    
        // Redirect and display success message
        return redirect()->route('admin.brands')->with('status', 'Brand Has Been Added Successfully!');
    }
    
    public function brand_edit($id){
        $brand = Brand::find($id);
        return view('admin.brand-edit',compact('brand'));
    }
    public function brand_update(Request $request) {
        // Validate input and set conditions for the image
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'
        ], [
            'image.mimes' => 'Image must be a file of type: png, jpg, jpeg.',
            'image.max' => 'Image size must not exceed 2MB.',
        ]);
    
        $brand = Brand::find($request->id);
    
        // Update the name and slug only if they are provided
        if ($request->filled('name')) {
            $brand->name = $request->name;
            $brand->slug = Str::slug($request->name); // Use $request->name to update the slug
        }
    
        // Check if an image file is uploaded
        if ($request->hasFile('image')) {
            // Delete the old image if exists
            if (File::exists(public_path('uploads/brands/' . $brand->image))) {
                File::delete(public_path('uploads/brands/' . $brand->image));
            }
    
            // Image Upload
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateBrandThumbnailsImage($image, $file_name);
            $brand->image = $file_name;
        }
    
        // Save changes if any field is updated
        $brand->save();
    
        // Redirect and display success message
        return redirect()->route('admin.brands')->with('status', 'Brand Has Been Updated Successfully!');
    }
    
    public function GenerateBrandThumbnailsImage($image, $imageName) {
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function brand_delete($id) {
        $brand = Brand::find($id);
        
        // Delete the old image
        if (File::exists(public_path('uploads/brands/'. $brand->image))) {
            File::delete(public_path('uploads/brands/'. $brand->image));
        }
        
        // Delete the brand
        $brand->delete();

        // Redirect and display success message
        return redirect()->route('admin.brands')->with('status', 'Brand Has Been Deleted Successfully!');
    }
    // ----------------------------------------------------------------

    //Category 
    public function categories(){
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view('admin.categories',compact('categories'));

    }
    public function add_category(){
        return view('admin.category-add');
    }
    public function category_store(Request $request) {
       // Validate the input including making image required
       $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:categories,slug',
        'image' => 'required|mimes:png,jpg,jpeg|max:2048'
    ], [
        // Custom error message for image
        'image.required' => 'Please upload an image for the brand.',
        'image.mimes' => 'The image must be a file of type: png, jpg, jpeg.',
        'image.max' => 'The image size must not exceed 2MB.'
    ]);

    $category = new Category();
    $category->name = $request->name;

    // Generate the slug from $request->name
    $category->slug = Str::slug($request->name);

    // Image Upload
    $image = $request->file('image');
    $file_extension= $request->file('image')->extension();
    $file_name = Carbon::now()->timestamp.'.'.$file_extension;
    $this->GenerateCategoryThumbnailsImage($image,$file_name);
    $category->image =$file_name;
    $category->save();

    // Redirect and display success message
    return redirect()->route('admin.categories')->with('status', 'category Has Been Added Successfully!');
        
    }

    public function GenerateCategoryThumbnailsImage($image, $imageName) {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }
    public function category_edit($id){
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }

    public function category_update(Request $request) {
        // Validate input and set conditions for the image
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'
        ], [
            'image.mimes' => 'Image must be a file of type: png, jpg, jpeg.',
            'image.max' => 'Image size must not exceed 2MB.',
        ]);
    
        $category = Category::find($request->id);
    
        // Update the name and slug only if they are provided
        if ($request->filled('name')) {
            $category->name = $request->name;
            $category->slug = Str::slug($request->name); // Update the slug based on the name
        }
    
        // Check if an image file is uploaded
        if ($request->hasFile('image')) {
            // Delete the old image if exists
            if (File::exists(public_path('uploads/categories/' . $category->image))) {
                File::delete(public_path('uploads/categories/' . $category->image));
            }
    
            // Image Upload
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateCategoryThumbnailsImage($image, $file_name);
            $category->image = $file_name;
        }
    
        // Save changes if any field is updated
        $category->save();
    
        // Redirect and display success message
        return redirect()->route('admin.categories')->with('status', 'Category Has Been Updated Successfully!');
    }

    public function category_delete($id) {
        $category = Category::find($id);
        
        // Delete the old image
        if (File::exists(public_path('uploads/categories/'. $category->image))) {
            File::delete(public_path('uploads/categories/'. $category->image));
        }
        
        // Delete the brand
        $category->delete();

        // Redirect and display success message
        return redirect()->route('admin.categories')->with('status', 'category Has Been Deleted Successfully!');
    }

    // ----------------------------------------------------------------


    //Product
    public function products(){
        $products = Product::orderBy('created_at','DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }

    public function add_product(){
        $brands = Brand::select('id','name')->orderBy('name')->get();
        $categories = Category::select('id','name')->orderBy('name')->get();
        return view('admin.product-add',compact('brands','categories'));
    }

    public function product_store(Request $request) {
        // Validate the input including making image required
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:products,slug',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'required|mimes:png,jpg,jpeg|max:2048',
            'category_id'=>'required',
            'brand_id'=>'required'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image')){
            $image = $request->file('image');
            $image_name = $current_timestamp. '.'. $image->extension();
            $this->GenerateProductThumbnailsImage($image, $image_name);
            $product->image =$image_name;
        }

        $gallery_arr=array();
        $gallery_image="";
        $counter = 1;
        if ($request->hasFile('images'))
        {
            $allowedfileExtion =['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) 
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck= in_array($gextension,$allowedfileExtion);
                if($gcheck) 
                {
                    $gfileName = $current_timestamp.'-'. $counter.'.'.$gextension;
                    $this->GenerateProductThumbnailsImage($file, $gfileName);
                    array_push($gallery_arr,$gfileName);
                    $counter=$counter+1;
                }
            }
            $gallery_images = implode(",",$gallery_arr);
        }
        $product->images = $gallery_images;
        $product->save();

        // Redirect and display success message
        return redirect()->route('admin.products')->with('status', 'Product Has Been Added Successfully!');

    }

    public function GenerateProductThumbnailsImage($image, $imageName) {
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());

        $img->cover(540,689,"top");
        $img->resize(540,689,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);

        $img->resize(104,104,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail.'/'.$imageName);

    } 

    public function product_edit($id){
        $product = Product::find($id);
        $brands = Brand::select('id','name')->orderBy('name')->get();
        $categories = Category::select('id','name')->orderBy('name')->get();
        return view('admin.product-edit',compact('product','brands','categories'));
    }

    public function product_update(Request $request){
        // Validate the input including making image required
        $request->validate([
            'name'=>'required',
           'slug'=>'required|unique:products,slug,'.$request->id,
           'short_description'=>'required',
            'description'=>'required',
           'regular_price'=>'required',
           'sale_price'=>'required',
            'SKU'=>'required',
           'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'category_id'=>'required',
            'brand_id'=>'required'
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image'))
        {
            if(File::exists(public_path('uploads/products').'/'.$product->image)){
                File::delete(public_path('uploads/products').'/'.$product->image);
            }
            if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)){
                File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
            }
            $image = $request->file('image');
            $image_name = $current_timestamp. '.'. $image->extension();
            $this->GenerateProductThumbnailsImage($image, $image_name);
            $product->image =$image_name;
        }

        $gallery_arr=array();
        $gallery_image="";
        $counter = 1;
        if ($request->hasFile('images'))
        {
            foreach(explode(',',$product->images) as $ofile){
                if(File::exists(public_path('uploads/products').'/'.$ofile)){
                    File::delete(public_path('uploads/products').'/'.$ofile);
                }
                if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)){
                    File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
                }
            }

            $allowedfileExtion =['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) 
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck= in_array($gextension,$allowedfileExtion);
                if($gcheck) 
                {
                    $gfileName = $current_timestamp.'-'. $counter.'.'.$gextension;
                    $this->GenerateProductThumbnailsImage($file, $gfileName);
                    array_push($gallery_arr,$gfileName);
                    $counter=$counter+1;
                }
            }
            $gallery_images = implode(",",$gallery_arr);
            $product->images = $gallery_images;
        }
        
        $product->save();

        // Redirect and display success message
        return redirect()->route('admin.products')->with('status', 'Product Has Been Updated Successfully!');
    }

    public function product_delete($id){
        $product=Product::find($id);
        
        if(File::exists(public_path('uploads/products').'/'.$product->image)){
            File::delete(public_path('uploads/products').'/'.$product->image);
        }
        if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)){
            File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
        }
        foreach(explode(',',$product->images) as $ofile){
            if(File::exists(public_path('uploads/products').'/'.$ofile)){
                File::delete(public_path('uploads/products').'/'.$ofile);
            }
            if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)){
                File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'Product Deleted Successfully!'); 
    }
    // ----------------------------------------------------------------

    //Coupon Product
    public function coupons(){
        $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(12);
        return view('admin.coupons',compact('coupons'));
    }
    public function coupon_add(){
        return view('admin.coupon-add');
    }

    public function coupon_store(Request $request){
        // Validate the input including making image required
        $request->validate([
            'code'=>'required',
            'type'=>'required',
            'value'=>'required|numeric',
            'cart_value'=>'required|numeric',
            'expiry_date'=>'required|date',
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();

        // Redirect and display success message
        return redirect()->route('admin.coupons')->with('status', 'Coupon Has Been Added Successfully!');
    }
    


}
