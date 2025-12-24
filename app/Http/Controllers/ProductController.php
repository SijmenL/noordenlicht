<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPrice; // Added: Pivot model
use App\Models\Price;        // Added: Price model
use App\Models\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    // --- Public Frontend Functions ---

    public function shop()
    {
        $search = request('search');
        $type = request('type');

        $query = Product::query();

        // Exclude 'Supplementen bij accommodatie' (type 0)
        $query->where('type', '!=', '0');

        // Search Logic
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Category Filter
        if ($type) {
            $query->where('type', $type);
        }

        // Sort by Category first, then Name
        $products = $query->orderBy('type')
            ->orderBy('name')
            ->paginate(25);

        return view('shop.list', ['products' => $products, 'search' => $search]);
    }

    public function details($id)
    {
        try {
            $product = Product::with(['prices.price', 'images'])->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('shop')->with('error', 'Dit product bestaat niet.');
        }

        // Logic to fetch supplements if the product is an 'Overnachting' (Type 2)
        $supplements = collect();
        if ($product->type == '2') {
            $supplements = Product::where('type', '0')->get();
        }

        return view('shop.details', ['product' => $product, 'supplements' => $supplements]);
    }

    // --- Admin Functions ---

    public function index()
    {
        $user = Auth::user();
        $search = request('search');

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })->orderBy('name')->paginate(25);

        return view('admin.products.list', compact('user', 'products', 'search'));
    }

    public function create()
    {
        $user = Auth::user();
        $tempImages = ProductImage::whereNull('product_id')->get();

        return view('admin.products.new', compact('user', 'tempImages'));
    }

    public function productDetails($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $product = Product::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('admin.products')->with('error', 'Dit product bestaat niet.');
        }

        $productTypeLabel = "";

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'View product', 'Products dashboard', $product->name, '');

        return view('admin.products.details', ['user' => $user, 'roles' => $roles, 'product' => $product, 'productTypeLabel' => $productTypeLabel]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'required|string|max:65535',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'prices_to_add' => 'nullable|string', // Validating the price JSON
            'temp_image_ids' => 'nullable|string',
        ]);

        $tempImageIds = array_filter(explode(',', $request->input('temp_image_ids') ?? ''));

        DB::beginTransaction();
        try {
            if($request->input('image')) {
                $mainImageFile = $request->file('image');
                $mainImageName = time() . '_main.' . $mainImageFile->extension();
                $mainImagePath = public_path('files/products/images');
                $mainImageFile->move($mainImagePath, $mainImageName);
            } else {
                $mainImageName = null;
            }

            // Removed 'price' => 0 because the column no longer exists
            $product = Product::create([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'description' => $request->input('description'),
                'image' => $mainImageName,
                'user_id' => Auth::id(),
            ]);

            // 1. Process Prices (Create Price model first, then link)
            if (!empty($request->input('prices_to_add'))) {
                $prices = json_decode($request->input('prices_to_add'), true);
                if (is_array($prices)) {
                    foreach ($prices as $priceData) {
                        // Create the generic Price record
                        $price = Price::create([
                            'name' => $priceData['name'],
                            'amount' => $priceData['amount'],
                            'type' => $priceData['type'],
                        ]);

                        // Link it to the Product
                        ProductPrice::create([
                            'product_id' => $product->id,
                            'price_id' => $price->id,
                        ]);
                    }
                }
            }

            // 2. Move and link temporary carousel images
            if (!empty($tempImageIds)) {
                $tempImages = ProductImage::whereIn('id', $tempImageIds)->whereNull('product_id')->get();
                foreach ($tempImages as $tempImage) {
                    $oldPath = public_path('uploads/products/temp/images/' . $tempImage->image);
                    $newPathDir = public_path('files/products/carousel');
                    File::ensureDirectoryExists($newPathDir);
                    $newPath = $newPathDir . '/' . $tempImage->image;

                    if (File::exists($oldPath)) {
                        File::move($oldPath, $newPath);
                    }
                    $tempImage->product_id = $product->id;
                    $tempImage->save();
                }
            }


            DB::commit();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Create product', 'product', 'Product id: ' . $product->id, '');

            return redirect()->route('admin.products')->with('success', 'Product aangemaakt!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Product save error: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Opslaan mislukt: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $product = Product::with(['images', 'prices.price'])->findOrFail($id);
        return view('admin.products.edit', compact('user', 'product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'required|string|max:65535',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'temp_image_ids' => 'nullable|string',
            'images_to_remove' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Removed 'price' from the update list
            $product->update($request->only('name', 'type', 'description'));

            if ($request->hasFile('image')) {
                File::delete(public_path('files/products/images/' . $product->image));
                $mainImageFile = $request->file('image');
                $mainImageName = time() . '_main.' . $mainImageFile->extension();
                $mainImageFile->move(public_path('files/products/images'), $mainImageName);
                $product->image = $mainImageName;
                $product->save();
            }

            // Process removals
            $imagesToRemoveIds = array_filter(explode(',', $request->input('images_to_remove', '')));
            if (!empty($imagesToRemoveIds)) {
                $imagesToRemove = ProductImage::where('product_id', $product->id)->whereIn('id', $imagesToRemoveIds)->get();
                foreach ($imagesToRemove as $image) {
                    File::delete(public_path('files/products/carousel/' . $image->image));
                    $image->delete();
                }
            }

            // Process new images
            $tempImageIds = array_filter(explode(',', $request->input('temp_image_ids') ?? ''));
            if (!empty($tempImageIds)) {
                $tempImages = ProductImage::whereIn('id', $tempImageIds)->whereNull('product_id')->get();
                foreach ($tempImages as $tempImage) {
                    $oldPath = public_path('uploads/products/temp/images/' . $tempImage->image);
                    $newPathDir = public_path('files/products/carousel');
                    File::ensureDirectoryExists($newPathDir);
                    $newPath = $newPathDir . '/' . $tempImage->image;

                    if (File::exists($oldPath)) {
                        File::move($oldPath, $newPath);
                    }
                    $tempImage->product_id = $product->id;
                    $tempImage->save();
                }
            }

            DB::commit();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Update product', 'product', 'Product id: ' . $product->id, '');

            return redirect()->route('admin.products.details', $product->id)->with('success', 'Product succesvol bijgewerkt!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Product update error: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het bijwerken. Probeer het opnieuw.')->withInput();
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete(); // Add cascade delete logic in model or here manually
        return redirect()->route('admin.products')->with('success', 'Product verwijderd.');
    }

    public function uploadTempImage(Request $request)
    {
        $file = $request->file('file');
        $uniqueId = $request->input('unique_id');
        $path = public_path('uploads/products/temp/images');
        File::ensureDirectoryExists($path);
        $fileName = time() . '_' . $uniqueId . '.' . $file->extension();
        $file->move($path, $fileName);

        $img = ProductImage::create(['product_id' => null, 'image' => $fileName, 'temp_id' => $uniqueId]);
        return response()->json(['success' => true, 'data' => ['id' => $img->id]]);
    }
}
