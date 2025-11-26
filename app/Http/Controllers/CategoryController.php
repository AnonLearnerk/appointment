<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use App\Services\FirebaseService;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Kreait\Firebase\Database;

class CategoryController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->database = $firebaseService->getDatabase();
    }

    public function index()
    {
        $categories = $this->database->getReference('categories')->getValue() ?? [];

        $categories = collect($categories)->map(function ($item, $key) {
            $item['id'] = $key;
            return $item; // 👈 return array, not (object)
        })->filter(function ($item) {
            return empty($item['deleted_at']); // array access
        });

        return view('admin.category.index', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        return view('admin.category.create');
    }


    public function trashed()
    {
        $categories = $this->database->getReference('categories')->getValue() ?? [];

        $categories = collect($categories)->map(function ($item, $key) {
            $item['id'] = $key;
            return $item; // 👈 return array
        })->filter(function ($item) {
            return !empty($item['deleted_at']); // array access
        });

        return view('admin.category.index', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'body' => 'nullable|string',
            'status' => ['required', Rule::in(['PUBLISHED', 'DRAFT'])],
        ]);

        // Check for duplicate title in Firebase
        $existingCategories = $this->database->getReference('categories')->getValue();
        if ($existingCategories) {
            foreach ($existingCategories as $cat) {
                if (strcasecmp($cat['title'], $data['title']) === 0 && empty($cat['deleted_at'])) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['title' => ['This category already exists.']]
                    ], 422);
                }
            }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/categories'), $filename);
            $data['image'] = 'uploads/categories/' . $filename;
        }

        // Add category to Firebase
        $id = (string) Str::uuid();
        $data['deleted_at'] = null;
        $this->database->getReference('categories/' . $id)->set($data);

        return response()->json([
            'success' => true,
            'message' => 'Category successfully created!',
            'data' => $data
        ], 201);
    }

    public function edit($id)
    {
        $category = $this->database->getReference('categories/' . $id)->getValue();
        if (!$category) abort(404, 'Category not found');

        $category['id'] = $id;
        return view('admin.category.edit', ['category' => (object) $category]);
    }

    public function update(Request $request, $id)
    {
        try {
            $category = $this->database->getReference('categories/' . $id)->getValue();
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.'
                ], 404);
            }

            $data = $request->validate([
                'title' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'body' => 'nullable|string',
                'status' => ['required', Rule::in(['PUBLISHED', 'DRAFT'])],
            ]);

            // Remove image
            if ($request->remove_image == "1" && !empty($category['image'])) {
                if (File::exists(public_path($category['image']))) {
                    File::delete(public_path($category['image']));
                }
                $data['image'] = null;
            }

            // Upload new image
            if ($request->hasFile('image')) {
                if (!empty($category['image']) && File::exists(public_path($category['image']))) {
                    File::delete(public_path($category['image']));
                }

                $filename = time() . '.' . $request->image->extension();
                $request->image->move(public_path('uploads/categories'), $filename);
                $data['image'] = 'uploads/categories/' . $filename;
            } elseif (!empty($category['image']) && $request->remove_image != "1") {
                // keep old image if not removed
                $data['image'] = $category['image'];
            }

            $this->database->getReference('categories/' . $id)->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $category = $this->database->getReference('categories/' . $id)->getValue();
        if (!$category) abort(404, 'Category not found');

        $this->database->getReference('categories/' . $id)->update([
            'deleted_at' => Carbon::now()->toDateTimeString(),
            'status' => 'INACTIVE', // 👈 mark as inactive when trashed
        ]);

        return redirect()->back()->with('success', 'Category moved to trash and marked as inactive.');
    }

    public function restore($id)
    {
        $category = $this->database->getReference('categories/' . $id)->getValue();
        if (!$category) abort(404, 'Category not found');

        $this->database->getReference('categories/' . $id)->update([
            'deleted_at' => null,
            'status' => 'PUBLISHED', // 👈 optionally re-activate when restored
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category restored and marked as published.');
    }

    public function forceDelete($id)
    {
        $category = $this->database->getReference('categories/' . $id)->getValue();
        if (!$category) abort(404, 'Category not found');

        // Delete image if exists
        if (!empty($category['image']) && File::exists(public_path($category['image']))) {
            File::delete(public_path($category['image']));
        }

        $this->database->getReference('categories/' . $id)->remove();

        return redirect()->route('admin.categories.trashed')->with('success', 'Category permanently deleted.');
    }
}
