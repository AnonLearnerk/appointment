<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FirebaseService;
use Carbon\Carbon;

class ServiceController extends Controller
{
    protected $db;

    public function __construct(FirebaseService $firebase)
    {
        $this->db = $firebase->getDatabase();
    }

    // List all services
    public function index()
    {
        $services = $this->db->getReference('services')->getValue() ?? [];

        $servicesArray = [];

        foreach ($services as $id => $service) {
            if (empty($service)) continue;

            // Skip trashed services
            if (isset($service['trashed']) && $service['trashed'] === true) continue;

            $service['id'] = $id;
            $service['image'] = $service['image'] ?? null;
            $service['image_url'] = $service['image_url'] ?? ($service['image'] ? asset('storage/' . $service['image']) : null);

            $servicesArray[] = $service;
        }

        return view('admin.services.index', ['services' => $servicesArray]);
    }

    // Show create form
    public function create()
    {
        return view('admin.services.create');
    }

    // Store a new service
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'nullable|string',
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg,webp|max:2048',
        ]);

        // Check if title already exists in Firebase
        $services = $this->db->getReference('services')->getValue() ?? [];
        foreach ($services as $service) {
            if (isset($service['title']) && strtolower($service['title']) === strtolower($request->title)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A service with this title already exists.',
                ], 422);
            }
        }

        $data = $validated;
        $data['status'] = $request->status ?? 0;

        // Generate excerpt automatically
        if (!empty($request->body)) {
            $cleanText = strip_tags($request->body);
            $data['excerpt'] = mb_substr($cleanText, 0, 50) . (mb_strlen($cleanText) > 50 ? '...' : '');
        } else {
            $data['excerpt'] = '';
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->image->store('images/service', 'public');
            $data['image_url'] = asset('storage/' . $data['image']);
        } else {
            $data['image_url'] = null;
        }

        $data['created_at'] = now()->format('Y-m-d H:i:s');
        $data['updated_at'] = now()->format('Y-m-d H:i:s');

        // Push to Firebase
        $newRef = $this->db->getReference('services')->push($data);
        $data['id'] = $newRef->getKey();

        return response()->json([
            'success' => true,
            'message' => 'Service has been added successfully.',
            'service' => $data
        ]);
    }

    // Show edit form
    public function edit($id)
    {
        $service = $this->db->getReference("services/{$id}")->getValue();
        if (!$service) {
            return redirect()->back()->withErrors(['service' => 'Service not found']);
        }

        // Convert array to object
        $service = (object) $service;

        // Ensure all expected keys exist
        $service->title = $service->title ?? '';
        $service->body = $service->body ?? '';
        $service->excerpt = $service->excerpt ?? '';
        $service->status = $service->status ?? 0;
        $service->image = $service->image ?? null;

        $service->id = $id;

        return view('admin.services.edit', compact('service'));
    }

    // Update service
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'nullable|string',
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg,webp|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        $serviceRef = $this->db->getReference("services/{$id}");
        $service = $serviceRef->getValue();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        $data = $validated;
        $data['status'] = $request->status ?? 0;

        // Generate excerpt
        if (!empty($request->body)) {
            $cleanText = strip_tags($request->body);
            $data['excerpt'] = mb_substr($cleanText, 0, 50) . (mb_strlen($cleanText) > 50 ? '...' : '');
        } else {
            $data['excerpt'] = '';
        }

        // Remove current image if requested
        if ($request->boolean('remove_image') && isset($service['image'])) {
            if (Storage::exists('public/' . $service['image'])) {
                Storage::delete('public/' . $service['image']);
            }
            $data['image'] = null;
            $data['image_url'] = null;
        }

        // Upload new image
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (isset($service['image']) && Storage::exists('public/' . $service['image'])) {
                Storage::delete('public/' . $service['image']);
            }
            $data['image'] = $request->image->store('images/service', 'public');
            $data['image_url'] = asset('storage/' . $data['image']);
        }

        $data['updated_at'] = now()->format('Y-m-d H:i:s');

        // Update Firebase
        $serviceRef->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'redirect' => route('admin.services.index')
        ]);
    }

    // Show trashed services
    public function trash()
    {
        $services = $this->db->getReference('services')->getValue() ?? [];

        $trashedServices = [];

        foreach ($services as $id => $service) {
            if (!empty($service) && isset($service['trashed']) && $service['trashed'] === true) {
                // Convert array to object
                $s = (object) $service;
                $s->id = $id;

                // Ensure all expected keys exist to avoid Undefined property errors
                $s->title = $s->title ?? '';
                $s->excerpt = $s->excerpt ?? '';
                $s->image = $s->image ?? null;

                // Convert deleted_at to Carbon instance for formatting
                $s->deleted_at = isset($s->deleted_at) ? Carbon::parse($s->deleted_at) : null;

                $trashedServices[] = $s;
            }
        }

        return view('admin.services.trash', ['services' => collect($trashedServices)]);
    }

    // Move to trash
    public function destroy($id)
    {
        $serviceRef = $this->db->getReference("services/{$id}");
        $service = $serviceRef->getValue();

        if (!$service) {
            return redirect()->back()->withErrors(['service' => 'Service not found']);
        }

        $serviceRef->update(['trashed' => true, 'deleted_at' => now()->format('Y-m-d H:i:s')]);

        return redirect()->back()->with('success', 'Service moved to trash.');
    }

    // Restore
    public function restore($id)
    {
        $serviceRef = $this->db->getReference("services/{$id}");
        $service = $serviceRef->getValue();

        if (!$service) {
            return redirect()->back()->withErrors(['service' => 'Service not found']);
        }

        $serviceRef->update(['trashed' => false, 'deleted_at' => null]);

        return redirect()->back()->with('success', 'Service restored successfully.');
    }

    // Force delete
    public function force_delete($id)
    {
        $serviceRef = $this->db->getReference("services/{$id}");
        $service = $serviceRef->getValue();

        if (!$service) {
            return redirect()->back()->withErrors(['service' => 'Service not found']);
        }

        // Delete image from storage
        if (isset($service['image']) && Storage::exists('public/' . $service['image'])) {
            Storage::delete('public/' . $service['image']);
        }

        $serviceRef->remove();

        return redirect()->back()->with('success', 'Service permanently deleted.');
    }
}
