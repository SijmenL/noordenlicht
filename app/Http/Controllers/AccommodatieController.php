<?php

namespace App\Http\Controllers;

use App\Models\Accommodatie;
use App\Models\AccommodatieIcon;
use App\Models\AccommodatieImage;
use App\Models\AccommodatiePrice;
use App\Models\Price; // FIX: Added Price model import
use App\Models\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class AccommodatieController extends Controller
{
    public function home()
    {
        $search = request('search');

        if (request('search')) {
            $all_accommodatie = Accommodatie::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })->orderBy('name')->paginate(25);
        } else {
            $all_accommodatie = Accommodatie::orderBy('name')->paginate(25);
        }

        return view('accommodaties.list', ['all_accommodaties' => $all_accommodatie, 'search' => $search]);
    }

    public function details($id)
    {
        try {
            $accommodatie = Accommodatie::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('accommodaties')->with('error', 'Deze accommodatie bestaat niet.');
        }

        return view('accommodaties.details', ['accommodatie' => $accommodatie]);
    }

    // Admin functions
    public function accommodaties()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();
        $search = request('search');

        if (request('search')) {
            $all_accommodatie = Accommodatie::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })->orderBy('name')->paginate(25);
        } else {
            $all_accommodatie = Accommodatie::orderBy('name')->paginate(25);
        }

        return view('admin.accommodaties.list', ['user' => $user, 'roles' => $roles, 'all_accommodaties' => $all_accommodatie, 'search' => $search]);
    }

    public function createAccommodatie()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();
        $tempImages = AccommodatieImage::whereNull('accommodatie_id')->get();
        $tempIcons = AccommodatieIcon::whereNull('accommodatie_id')->get();

        return view('admin.accommodaties.new', [
            'user' => $user,
            'roles' => $roles,
            'tempImages' => $tempImages,
            'tempIcons' => $tempIcons,
        ]);
    }

    public function createAccommodatieSave(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'image' => 'required|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'icon_data' => 'nullable|string',
            'temp_image_ids' => 'nullable|string',
            'temp_icon_data' => 'nullable|string',
            'temp_icon_ids' => 'nullable|string', // ADDED: Fallback for legacy ID list
            'prices_to_add' => 'nullable|string',
        ]);


        $tempImageIds = array_filter(explode(',', $request->input('temp_image_ids', '')));

        DB::beginTransaction();
        try {
            $mainImageFile = $request->file('image');
            $mainImageName = time() . '_main.' . $mainImageFile->extension();
            $mainImagePath = public_path('files/accommodaties/images');
            $mainImageFile->move($mainImagePath, $mainImageName);

            $accommodatie = Accommodatie::create([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'price' => 0,
                'description' => $request->input('description'),
                'image' => $mainImageName,
                'user_id' => Auth::id(),
            ]);

            // 1. Process Prices (Create Price model first, then link)
            if ($request->input('prices_to_add')) {
                $prices = json_decode($request->input('prices_to_add'), true);
                if (is_array($prices)) {
                    foreach ($prices as $priceData) {
                        $price = Price::create([
                            'name' => $priceData['name'],
                            'amount' => $priceData['amount'],
                            'type' => $priceData['type'],
                        ]);

                        AccommodatiePrice::create([
                            'accommodatie_id' => $accommodatie->id,
                            'price_id' => $price->id,
                        ]);
                    }
                }
            }

            // 2. Move and link temporary carousel images
            if (!empty($tempImageIds)) {
                $tempImages = AccommodatieImage::whereIn('id', $tempImageIds)->whereNull('accommodatie_id')->get();
                foreach ($tempImages as $tempImage) {
                    $oldPath = public_path('uploads/accommodaties/temp/images/' . $tempImage->image);
                    $newPathDir = public_path('files/accommodaties/carousel');
                    File::ensureDirectoryExists($newPathDir);
                    $newPath = $newPathDir . '/' . $tempImage->image;

                    if (File::exists($oldPath)) {
                        File::move($oldPath, $newPath);
                    }
                    $tempImage->accommodatie_id = $accommodatie->id;
                    $tempImage->save();
                }
            }

            // 3. Move and link temporary icons
            // Primary Method: JSON Data (includes text edits)
            if ($request->input('temp_icon_data')) {
                $iconDataList = json_decode($request->input('temp_icon_data'), true);

                if (is_array($iconDataList) && !empty($iconDataList)) {
                    $iconIds = array_column($iconDataList, 'id');
                    $tempIcons = AccommodatieIcon::whereIn('id', $iconIds)->whereNull('accommodatie_id')->get()->keyBy('id');

                    foreach ($iconDataList as $iconItem) {
                        $id = $iconItem['id'] ?? null;
                        $text = $iconItem['text'] ?? '';

                        if ($id && isset($tempIcons[$id])) {
                            $tempIcon = $tempIcons[$id];

                            $oldPath = public_path('uploads/accommodaties/temp/icons/' . $tempIcon->icon);
                            $newPathDir = public_path('files/accommodaties/icons');
                            File::ensureDirectoryExists($newPathDir);
                            $newPath = $newPathDir . '/' . $tempIcon->icon;

                            if (File::exists($oldPath)) {
                                File::move($oldPath, $newPath);
                            }

                            $tempIcon->accommodatie_id = $accommodatie->id;
                            $tempIcon->text = $text;
                            $tempIcon->save();
                        }
                    }
                }
            }
            // Fallback Method: ID List (if JS sends the old format)
            elseif ($request->input('temp_icon_ids')) {
                $tempIconIds = array_filter(explode(',', $request->input('temp_icon_ids', '')));
                if (!empty($tempIconIds)) {
                    $tempIcons = AccommodatieIcon::whereIn('id', $tempIconIds)->whereNull('accommodatie_id')->get();
                    foreach ($tempIcons as $tempIcon) {
                        $oldPath = public_path('uploads/accommodaties/temp/icons/' . $tempIcon->icon);
                        $newPathDir = public_path('files/accommodaties/icons');
                        File::ensureDirectoryExists($newPathDir);
                        $newPath = $newPathDir . '/' . $tempIcon->icon;

                        if (File::exists($oldPath)) {
                            File::move($oldPath, $newPath);
                        }

                        $tempIcon->accommodatie_id = $accommodatie->id;
                        $tempIcon->save();
                    }
                }
            }

            DB::commit();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Create accommodatie', 'accommodatie', 'Accommodatie id: ' . $accommodatie->id, '');

            return redirect()->route('admin.accommodaties')->with('success', 'De accommodatie is succesvol opgeslagen!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Accommodatie save error: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het opslaan: ' . $e->getMessage())->withInput();
        }
    }

    public function editAccommodatie($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $accommodatie = Accommodatie::with(['images', 'icons'])->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('admin.accommodaties')->with('error', 'Deze accommodatie kon niet worden gevonden.');
        }

        return view('admin.accommodaties.edit', compact('user', 'roles', 'accommodatie'));
    }

    public function editAccommodatieSave(Request $request, $id)
    {
        $accommodatie = Accommodatie::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'temp_image_ids' => 'nullable|string',
            'temp_icon_data' => 'nullable|string',
            'images_to_remove' => 'nullable|string',
            'icons_to_remove' => 'nullable|string',
            'updated_icon_data' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $accommodatie->update($request->only('name', 'type', 'price', 'description'));

            if ($request->hasFile('image')) {
                File::delete(public_path('files/accommodaties/images/' . $accommodatie->image));
                $mainImageFile = $request->file('image');
                $mainImageName = time() . '_main.' . $mainImageFile->extension();
                $mainImageFile->move(public_path('files/accommodaties/images'), $mainImageName);
                $accommodatie->image = $mainImageName;
                $accommodatie->save();
            }

            // Update existing icon texts
            if ($request->input('updated_icon_data')) {
                $updatedIcons = json_decode($request->input('updated_icon_data'), true);
                if (is_array($updatedIcons)) {
                    foreach ($updatedIcons as $iconData) {
                        if (isset($iconData['id']) && isset($iconData['text'])) {
                            AccommodatieIcon::where('id', $iconData['id'])
                                ->where('accommodatie_id', $accommodatie->id)
                                ->update(['text' => $iconData['text']]);
                        }
                    }
                }
            }

            // Process removals
            $imagesToRemoveIds = array_filter(explode(',', $request->input('images_to_remove', '')));
            if (!empty($imagesToRemoveIds)) {
                $imagesToRemove = AccommodatieImage::where('accommodatie_id', $accommodatie->id)->whereIn('id', $imagesToRemoveIds)->get();
                foreach ($imagesToRemove as $image) {
                    File::delete(public_path('files/accommodaties/carousel/' . $image->image));
                    $image->delete();
                }
            }
            $iconsToRemoveIds = array_filter(explode(',', $request->input('icons_to_remove', '')));
            if (!empty($iconsToRemoveIds)) {
                $iconsToRemove = AccommodatieIcon::where('accommodatie_id', $accommodatie->id)->whereIn('id', $iconsToRemoveIds)->get();
                foreach ($iconsToRemove as $icon) {
                    File::delete(public_path('files/accommodaties/icons/' . $icon->icon));
                    $icon->delete();
                }
            }

            // Process new images
            $tempImageIds = array_filter(explode(',', $request->input('temp_image_ids', '')));
            if (!empty($tempImageIds)) {
                $tempImages = AccommodatieImage::whereIn('id', $tempImageIds)->whereNull('accommodatie_id')->get();
                foreach ($tempImages as $tempImage) {
                    $oldPath = public_path('uploads/accommodaties/temp/images/' . $tempImage->image);
                    $newPathDir = public_path('files/accommodaties/carousel');
                    File::ensureDirectoryExists($newPathDir);
                    $newPath = $newPathDir . '/' . $tempImage->image;

                    if (File::exists($oldPath)) {
                        File::move($oldPath, $newPath);
                    }
                    $tempImage->accommodatie_id = $accommodatie->id;
                    $tempImage->save();
                }
            }

            // Process new icons
            if ($request->input('temp_icon_data')) {
                $newIconsData = json_decode($request->input('temp_icon_data'), true);
                if (is_array($newIconsData) && !empty($newIconsData)) {
                    $newIconIds = array_column($newIconsData, 'id');
                    $tempIcons = AccommodatieIcon::whereIn('id', $newIconIds)->whereNull('accommodatie_id')->get()->keyBy('id');

                    foreach ($newIconsData as $iconData) {
                        if (isset($tempIcons[$iconData['id']])) {
                            $tempIcon = $tempIcons[$iconData['id']];

                            $oldPath = public_path('uploads/accommodaties/temp/icons/' . $tempIcon->icon);
                            $newPathDir = public_path('files/accommodaties/icons');
                            File::ensureDirectoryExists($newPathDir);
                            $newPath = $newPathDir . '/' . $tempIcon->icon;

                            if (File::exists($oldPath)) {
                                File::move($oldPath, $newPath);
                            }

                            $tempIcon->accommodatie_id = $accommodatie->id;
                            $tempIcon->text = $iconData['text'];
                            $tempIcon->save();
                        }
                    }
                }
            }

            DB::commit();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Update accommodatie', 'accommodatie', 'Accommodatie id: ' . $accommodatie->id, '');

            return redirect()->route('admin.accommodaties.details', $accommodatie->id)->with('success', 'Accommodatie succesvol bijgewerkt!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Accommodatie update error: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het bijwerken. Probeer het opnieuw.')->withInput();
        }
    }

    public function deleteAccommodatie($id)
    {
        try {
            $accommodatie = Accommodatie::with(['images', 'icons'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.accommodaties')->with('error', 'Accommodatie niet gevonden.');
        }

        DB::beginTransaction();
        try {
            // Delete associated carousel images
            foreach ($accommodatie->images as $image) {
                File::delete(public_path('files/accommodaties/carousel/' . $image->image));
            }
            $accommodatie->images()->delete();

            // Delete associated icons
            foreach ($accommodatie->icons as $icon) {
                File::delete(public_path('files/accommodaties/icons/' . $icon->icon));
            }
            $accommodatie->icons()->delete();

            // Delete main image
            File::delete(public_path('files/accommodaties/images/' . $accommodatie->image));

            // Delete accommodation record
            $accommodatie->delete();

            DB::commit();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Delete accommodatie', 'accommodatie', 'Accommodatie: ' . $accommodatie->name, '');


            return redirect()->route('admin.accommodaties')->with('success', 'Accommodatie succesvol verwijderd.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Accommodatie delete error: " . $e->getMessage());
            return redirect()->route('admin.accommodaties')->with('error', 'Er is een fout opgetreden bij het verwijderen.');
        }
    }

    public function uploadTempImage(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'unique_id' => 'required|string',
        ]);

        $file = $request->file('file');
        $uniqueId = $request->input('unique_id');

        try {
            $tempPath = public_path('uploads/accommodaties/temp/images');
            File::ensureDirectoryExists($tempPath);
            $fileName = time() . '_' . $uniqueId . '.' . $file->extension();
            $file->move($tempPath, $fileName);

            $tempImage = AccommodatieImage::create([
                'accommodatie_id' => null,
                'image' => $fileName,
                'alt_text' => '',
                'temp_id' => $uniqueId,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tempImage->id,
                    'path' => asset('uploads/accommodaties/temp/images/' . $fileName),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Uploadfout: ' . $e->getMessage()], 500);
        }
    }

    public function uploadTempIcon(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:svg|max:1024',
            'text' => 'required|string|max:255',
            'unique_id' => 'required|string',
        ]);

        $file = $request->file('file');
        $uniqueId = $request->input('unique_id');
        $text = $request->input('text');

        try {
            $tempPath = public_path('uploads/accommodaties/temp/icons');
            File::ensureDirectoryExists($tempPath);
            $fileName = time() . '_' . $uniqueId . '.' . $file->extension();
            $file->move($tempPath, $fileName);

            $tempIcon = AccommodatieIcon::create([
                'accommodatie_id' => null,
                'icon' => $fileName,
                'text' => $text,
                'temp_id' => $uniqueId,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tempIcon->id,
                    'path' => asset('uploads/accommodaties/temp/icons/' . $fileName),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Uploadfout: ' . $e->getMessage()], 500);
        }
    }

    public function deleteTempImage(AccommodatieImage $image)
    {
        if ($image->accommodatie_id !== null) {
            return response()->json(['success' => false, 'message' => 'Afbeelding is reeds toegewezen.'], 403);
        }
        File::delete(public_path('uploads/accommodaties/temp/images/' . $image->image));
        $image->delete();
        return response()->json(['success' => true]);
    }

    public function deleteTempIcon(AccommodatieIcon $icon)
    {
        if ($icon->accommodatie_id !== null) {
            return response()->json(['success' => false, 'message' => 'Icoon is reeds toegewezen.'], 403);
        }
        File::delete(public_path('uploads/accommodaties/temp/icons/' . $icon->icon));
        $icon->delete();
        return response()->json(['success' => true]);
    }

    public function accommodatieDetails($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $accommodatie = Accommodatie::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('admin.accommodaties')->with('error', 'Deze accommodatie bestaat niet.');
        }

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'View accommodatie', 'Accommodaties dashboard', $accommodatie->name, '');

        return view('admin.accommodaties.details', ['user' => $user, 'roles' => $roles, 'accommodatie' => $accommodatie]);
    }
}
