<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Carbon\Carbon;

class SpecialDayController extends Controller
{
    protected $database;
    protected $reference = 'special_days'; // Firebase node

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase-admin-sdk.json'))
            ->withDatabaseUri('https://appointment-system-b9648-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    public function index()
    {
        $specialDays = $this->database->getReference($this->reference)->getValue() ?? [];
        $specialDays = collect($specialDays)
            ->sortBy('date')
            ->map(function ($item, $key) {
                $item['id'] = $key;
                return $item;
            });

        return view('admin.special-days.index', compact('specialDays'));
    }

    public function create()
    {
        return view('admin.special-days.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'type' => 'required|in:holiday,no_office',
            'status' => 'required|in:active,inactive',
        ]);

        $id = $this->database->getReference($this->reference)->push()->getKey();

        $data = array_merge($validated, [
            'id' => $id,
            'created_at' => now()->toDateTimeString(),
        ]);

        $this->database->getReference("{$this->reference}/{$id}")->set($data);

        // ✅ Return JSON response instead of redirect
        return response()->json([
            'message' => 'Special day added successfully!',
            'data' => $data
        ], 200);
    }

    public function edit($id)
    {
        $specialDay = $this->database->getReference("{$this->reference}/{$id}")->getValue();

        if (!$specialDay) {
            return redirect()->route('admin.special-days.index')->with('error', 'Special day not found.');
        }

        // Pass both the data and the ID to the view
        return view('admin.special-days.edit', [
            'specialDay' => $specialDay,
            'id' => $id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'type' => 'required|in:holiday,no_office',
            'status' => 'required|in:active,inactive',
        ]);

        $ref = $this->database->getReference("special_days/{$id}");
        $existing = $ref->getValue();

        if (!$existing) {
            return response()->json(['message' => 'Special day not found.'], 404);
        }

        $updatedData = array_merge($existing, $validated, [
            'updated_at' => now()->toDateTimeString(),
        ]);

        $ref->update($updatedData);

        return response()->json([
            'message' => 'Special day updated successfully!',
            'data' => $updatedData,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $specialDayRef = $this->database->getReference("{$this->reference}/{$id}");
        $specialDay = $specialDayRef->getValue();

        if (!$specialDay) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Special day not found.'], 404);
            }
            return redirect()->route('admin.special-days.index')->with('error', 'Special day not found.');
        }

        // Move to trash (soft delete) node
        $this->database->getReference("{$this->reference}_trash/{$id}")->set($specialDay);
        $specialDayRef->remove();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Special day moved to Trash!'], 200);
        }

        return redirect()->route('admin.special-days.index')->with('success', 'Special day moved to Trash!');
    }

    public function trash()
    {
        $trashed = $this->database->getReference("{$this->reference}_trash")->getValue() ?? [];

        $specialDays = collect($trashed)->map(function ($item, $key) {
            $item['id'] = $key;
            return $item;
        });

        return view('admin.special-days.trash', compact('specialDays'));
    }

    public function restore(Request $request, $id)
    {
        $trashRef = $this->database->getReference("{$this->reference}_trash/{$id}");
        $snapshot = $trashRef->getSnapshot();

        if (!$snapshot->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Special day not found in trash.'], 404);
            }

            return redirect()
                ->route('admin.special-days.trash')
                ->with('error', 'Special day not found in trash.');
        }

        $specialDay = $snapshot->getValue();
        $specialDay['restored_at'] = now()->toDateTimeString();

        $this->database->getReference("{$this->reference}/{$id}")->set($specialDay);
        $trashRef->remove();

        // ✅ Return JSON for AJAX requests (used by fetch)
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Special day restored successfully!']);
        }

        // ✅ Fallback for normal form submission
        return redirect()
            ->route('admin.special-days.trash')
            ->with('success', 'Special day restored successfully!');
    }


    public function forceDelete($id)
    {
        // Check if the record exists in trash before deleting
        $trashRef = $this->database->getReference("{$this->reference}_trash/{$id}");
        $exists = $trashRef->getValue();

        if (!$exists) {
            return redirect()
                ->route('admin.special-days.trash')
                ->with('error', 'Special day not found or already deleted.');
        }

        // Permanently remove from Firebase trash node
        $trashRef->remove();

        return redirect()
            ->route('admin.special-days.trash')
            ->with('success', 'Special day permanently deleted!');
    }

    public function calendarData()
    {
        $specialDays = $this->database->getReference($this->reference)->getValue() ?? [];

        $events = collect($specialDays)->map(function ($day, $key) {
            return [
                'id' => 'special-' . $key,
                'title' => $day['title'] ?? 'Special Day',
                'start' => $day['date'] ?? now()->toDateString(),
                'display' => 'background',
                'color' => '#f87171', // red shade
            ];
        })->values();

        return response()->json($events);
    }

    public function getActiveSpecialDays()
    {
        $specialDaysRef = app('firebase.database')->getReference('special_days');
        $specialDays = $specialDaysRef->getValue();

        $disabledDates = [];

        if ($specialDays) {
            foreach ($specialDays as $id => $day) {
                if (
                    isset($day['date'], $day['status'])
                    && strtolower($day['status']) === 'active'
                ) {
                    $disabledDates[] = $day['date']; // ex: "2025-12-25"
                }
            }
        }

        return response()->json($disabledDates);
    }
}
