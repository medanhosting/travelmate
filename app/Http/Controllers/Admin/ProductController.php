<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 30/08/2017
 * Time: 12:01
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\libs\Utilities;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductProperty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Webpatser\Uuid\Uuid;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user_admins');
    }

    public function index(){
        $products = null;

        if(!empty(request()->category) && !empty(request()->status)){
            $products = Product::where('category_id', request()->category)
                ->where('status_id', request()->status)
                ->orderByDesc('created_on')
                ->get();
        }
        else if(!empty(request()->category) && empty(request()->status)){
            $products = Product::where('category_id', request()->category)
                ->orderByDesc('created_on')
                ->get();
        }
        else if(empty(request()->category) && !empty(request()->status)){
            $products = Product::where('status_id', intval(request()->status))
                ->orderByDesc('created_on')
                ->get();
        }
        else if(empty(request()->category && empty(request()->status))){
            $products = Product::all()->sortByDesc('created_on');
        }

        // Get all categories
        $categories = Category::all();

        $data = [
            'products'          => $products,
            'categories'        => $categories,
            'filterCategory'    => request()->category ?? null,
            'filterStatus'      => request()->status ?? null,
        ];

        return View('admin.show-products')->with($data);
    }

    public function create(){
        $categories = Category::all();

        return View('admin.create-product', compact('categories'));
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'category'              => 'required|option_not_default',
                'name'                  => 'required',
                'price'                 => 'required',
                'weight'                => 'required',
                'product-featured'      => 'required|image|mimes:jpeg,jpg,png'
            ],[
                'option_not_default'    => 'Select a category'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            else{
                $price = $request->input('price');
                $priceDouble = (double) str_replace('.','', $price);
                $weight = (double) str_replace('.','', Input::get('weight'));

                $dateTimeNow = Carbon::now('Asia/Jakarta');

                $product = Product::create([
                    'id'            => Uuid::generate(),
                    'category_id'   => Input::get('category'),
                    'name'          => Input::get('name'),
                    'price'         => $priceDouble,
                    'weight'        => $weight,
                    'quantity'      => 0,
                    'created_on'    => $dateTimeNow->toDateTimeString(),
                    'status_id'     => 1
                ]);

                if(Input::get('options') == 'percent'){
                    $discountPercent = (double) Input::get('discount-percent');
                    $product->discount = $discountPercent;

                    $discountAmount = $priceDouble / 100 * $discountPercent;
                    $product->price_discounted = $priceDouble - $discountAmount;
                }
                else if(Input::get('options') == 'flat'){
                    $discountFlat = (double) str_replace('.','', Input::get('discount-flat'));
                    $product->discount_flat = $discountFlat;

                    $product->price_discounted = $priceDouble - $discountFlat;
                }
                else{
                    $product->price_discounted = $priceDouble;
                }

                if(!empty(Input::get('description'))){
                    $product->description = Input::get('description');
                }

                $product->save();
                $savedId = $product->id;

                // Check color & size
                if(Input::get('color-options') == 'yes'){
                    foreach(Input::get('color') as $color){
                        if(!empty($color)){
                            ProductProperty::create([
                                'product_id'    => $savedId,
                                'name'          => 'color',
                                'description'   => $color
                            ]);
                        }
                    }
                }

                if(Input::get('size-options') == 'yes'){
                    foreach(Input::get('size') as $size){
                        if(!empty($size)){
                            ProductProperty::create([
                                'product_id'    => $savedId,
                                'name'          => 'size',
                                'description'   => $size
                            ]);
                        }
                    }
                }

                if(!empty($request->file('product-featured'))){
                    $img = Image::make($request->file('product-featured'));

                    // Get image extension
                    $extStr = $img->mime();
                    $ext = explode('/', $extStr, 2);

                    $filename = $savedId.'_'. Carbon::now('Asia/Jakarta')->format('Ymdhms'). '_0.'. $ext[1];

                    $img->save(public_path('storage/product/'. $filename), 75);

                    $productImgFeatured = ProductImage::create([
                        'product_id'    => $savedId,
                        'path'          => $filename,
                        'featured'      => 1
                    ]);

                    $productImgFeatured->save();
                }

                if(!empty($request->file('product-photos'))){
                    $idx = 1;
                    foreach($request->file('product-photos') as $img){
                        error_log('index: '. $idx);
                        $photo = Image::make($img);

                        // Get image extension
                        $extStr = $photo->mime();
                        $ext = explode('/', $extStr, 2);

                        $filename = $savedId.'_'. Carbon::now('Asia/Jakarta')->format('Ymdhms'). '_'. $idx. '.'. $ext[1];


                        $photo->save(public_path('storage/product/'. $filename), 60);

                        $productPhoto = ProductImage::create([
                            'product_id'    => $savedId,
                            'path'          => $filename,
                            'featured'      => 0
                        ]);

                        $productPhoto->save();
                        $idx++;
                    }
                }
                return redirect::route('product-list');
            }
        }
        catch(\Exception $ex){
            Utilities::ExceptionLog($ex);
        }

    }

    public function edit($id){
        $product = Product::findorFail($id);

        $imgFeatured = null;
        if($product->product_image->count() > 0){
            $imgFeatured = $product->product_image()->where('featured', 1)->first()->path;
        }
        $imgPhotos = $product->product_image()->where('featured', 0)->get();
        $categories = Category::all();

        $data = [
            'product'       => $product,
            'imgFeatured'   => $imgFeatured,
            'imgPhotos'     => $imgPhotos,
            'categories'    => $categories
        ];

        return view('admin.edit-product')->with($data);
    }

    public function update(Request $request, $id){

        try{
            $validator = Validator::make($request->all(),[
                'category'              => 'required|option_not_default',
                'name'                  => 'required',
                'price'                 => 'required',
                'weight'                => 'required',
                'qty'                   => 'required'
            ],[
                'option_not_default'    => 'Select a category'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            else{
                $product = Product::find($id);
                $product->name = Input::get('name');
                $product->category_id = Input::get('category');

                $status = Input::get('status');
                $product->status_id = $status === '1' ? 1 : 2;

                $price = $request->input('price');
                $priceDouble = (double) str_replace('.','', $price);
                $weight = (double) str_replace('.','', Input::get('weight'));

                $product->price = $priceDouble;
                $product->weight = $weight;
                $product->quantity = Input::get('qty');

                if(Input::get('options') == 'percent'){
                    $discountPercent = (double) Input::get('discount-percent');
                    $product->discount = $discountPercent;

                    $discountAmount = $priceDouble / 100 * $discountPercent;
                    $product->price_discounted = $priceDouble - $discountAmount;

                    // Set other null
                    $product->discount_flat = null;
                }
                else if(Input::get('options') == 'flat'){
                    $discountFlat = (double) str_replace('.','', Input::get('discount-flat'));
                    $product->discount_flat = $discountFlat;

                    $product->price_discounted = $priceDouble - $discountFlat;

                    // Set other null
                    $product->discount_flat = null;
                }
                else if(Input::get('options') == 'none'){
                    // Set all null
                    $product->discount = null;
                    $product->discount_flat = null;
                    $product->price_discounted = $priceDouble;
                }

                if(!empty(Input::get('description'))){
                    $product->description = Input::get('description');
                }
                else{
                    $product->description = null;
                }

                $product->save();

                // Image handling
                $savedId = $product->id;

                if(!empty(Input::get('img_featured_changed') && Input::get('img_featured_changed') === 'new')){
                    // Change old value of featured image

                    if($product->product_image->count() > 0){
                        $currentImgFeatured = $product->product_image()->where('featured',1)->first();
                        $currentImgFeatured->featured = 0;
                        $currentImgFeatured->save();
                    }

                    $img = Image::make($request->file('product-featured'));

                    // Get image extension
                    $extStr = $img->mime();
                    $ext = explode('/', $extStr, 2);

                    $filename = $savedId.'_'. Carbon::now('Asia/Jakarta')->format('Ymdhms'). '_0.'. $ext[1];

                    $img->save(public_path('storage/product/'. $filename), 75);

                    $productImgFeatured = ProductImage::create([
                        'product_id'    => $savedId,
                        'path'          => $filename,
                        'featured'      => 1
                    ]);

                    $productImgFeatured->save();
                }

                error_log("Deleted: ". Input::get('deleted_img_id'));

                // Delete product images
                if(!empty(Input::get('deleted_img_id'))){
                    $deletedIdTmp = Input::get('deleted_img_id');

                    if(strpos($deletedIdTmp,',')){
                        $deletedIdList = explode(',', $deletedIdTmp);
                        foreach($deletedIdList as $deletedId){
                            $productImage = ProductImage::find($deletedId);

                            $deletedPath = storage_path('app/public/product/'. $productImage->path);
                            if(file_exists($deletedPath)) unlink($deletedPath);

                            $productImage->delete();
                        }
                    }
                    else{

                        $productImage = ProductImage::find($deletedIdTmp);

                        $deletedPath = storage_path('app/public/product/'. $productImage->path);
                        if(file_exists($deletedPath)) unlink($deletedPath);

                        $productImage->delete();
                    }
                }

                // Change featured value of existing product images
                if(!empty(Input::get('img_featured_changed') && Input::get('img_featured_changed') != 'new')){
                    // Change old value of featured image

                    if($product->product_image->count() > 0){
                        $currentImgFeatured = $product->product_image()->where('featured',1)->first();
                        $currentImgFeatured->featured = 0;
                        $currentImgFeatured->save();
                    }

                    $image = ProductImage::find(Input::get('img_featured_changed'));
                    $image->featured = 1;
                    $image->save();
                }

                if(!empty($request->file('product-photos'))){
                    $idx = 1;
                    foreach($request->file('product-photos') as $img){
                        error_log('index: '. $idx);
                        $photo = Image::make($img);

                        // Get image extension
                        $extStr = $photo->mime();
                        $ext = explode('/', $extStr, 2);

                        $filename = $savedId.'_'. Carbon::now('Asia/Jakarta')->format('Ymdhms'). '_'. $idx. '.'. $ext[1];


                        $photo->save(public_path('storage/product/'. $filename), 60);

                        $productPhoto = ProductImage::create([
                            'product_id'    => $savedId,
                            'path'          => $filename,
                            'featured'      => 0
                        ]);

                        $productPhoto->save();
                        $idx++;
                    }
                }

                return redirect::route('product-list');
            }
        }
        catch(\Exception $ex){
            Utilities::ExceptionLog($ex);
        }
    }
}