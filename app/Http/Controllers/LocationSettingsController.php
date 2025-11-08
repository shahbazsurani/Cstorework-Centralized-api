<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\LocationSettingsCategory;
use App\Models\LocationSetting;
use App\Policies\LocationSettingsPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LocationSettingsController extends Controller
{
    /**
     * Store location settings from hierarchical JSON data
     * Converts JSON structure to normalized tables
     */
    public function store(Request $request, $locationHash)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = Location::where('hash', $locationHash)->firstOrFail();

        // Check authorization using policy
        $this->authorize('create', [LocationSettingsPolicy::class, $location]);

        DB::beginTransaction();
        try {
            $settings = $request->input('settings');
            $this->processSettings($location->id, $settings);

            DB::commit();
            return response()->json(['message' => 'Settings saved successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to save settings', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recursively process settings and create categories/settings
     */
    private function processSettings($locationId, $settings, $parentId = null)
    {
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                // This is a category - create category and process children
                $category = LocationSettingsCategory::create([
                    'location_id' => $locationId,
                    'parent_id' => $parentId,
                    'category_name' => $key,
                ]);

                // Process nested settings recursively
                $this->processSettings($locationId, $value, $category->id);
            } else {
                // This is a setting - find or create category for current level
                if ($parentId === null) {
                    // Root level setting - create a default category if needed
                    $category = LocationSettingsCategory::firstOrCreate([
                        'location_id' => $locationId,
                        'parent_id' => null,
                        'category_name' => 'Root',
                    ]);
                    $categoryId = $category->id;
                } else {
                    $categoryId = $parentId;
                }

                // Create the setting
                LocationSetting::create([
                    'category_id' => $categoryId,
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Get all settings for a location in hierarchical format
     */
    public function show(Request $request, $locationHash)
    {
        $location = Location::where('hash', $locationHash)->firstOrFail();

        // Check authorization using policy
        $this->authorize('view', [LocationSettingsPolicy::class, $location]);

        $categories = LocationSettingsCategory::where('location_id', $location->id)
            ->with(['settings', 'children.settings'])
            ->whereNull('parent_id')
            ->get();

        $result = $this->buildHierarchy($categories);

        return response()->json($result);
    }

    /**
     * Build hierarchical structure from categories and settings
     */
    private function buildHierarchy($categories)
    {
        $result = [];

        foreach ($categories as $category) {
            if ($category->category_name === 'Root') {
                // Add root-level settings directly
                foreach ($category->settings as $setting) {
                    $result[$setting->key] = $setting->value;
                }
            } else {
                $result[$category->category_name] = $this->buildCategoryData($category);
            }
        }

        return $result;
    }

    /**
     * Build data for a single category
     */
    private function buildCategoryData($category)
    {
        $data = [];

        // Add direct settings
        foreach ($category->settings as $setting) {
            $data[$setting->key] = $setting->value;
        }

        // Add child categories recursively
        foreach ($category->children as $child) {
            $data[$child->category_name] = $this->buildCategoryData($child);
        }

        return $data;
    }

    /**
     * Update settings for a location
     */
    public function update(Request $request, $locationHash)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = Location::where('hash', $locationHash)->firstOrFail();

        // Check authorization using policy
        $this->authorize('update', [LocationSettingsPolicy::class, $location]);

        DB::beginTransaction();
        try {
            // Delete existing settings for this location
            LocationSettingsCategory::where('location_id', $location->id)->delete();

            // Create new settings
            $settings = $request->input('settings');
            $this->processSettings($location->id, $settings);

            DB::commit();
            return response()->json(['message' => 'Settings updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update settings', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete all settings for a location
     */
    public function destroy(Request $request, $locationHash)
    {
        $location = Location::where('hash', $locationHash)->firstOrFail();

        // Check authorization using policy
        $this->authorize('delete', [LocationSettingsPolicy::class, $location]);

        DB::beginTransaction();
        try {
            LocationSettingsCategory::where('location_id', $location->id)->delete();

            DB::commit();
            return response()->json(['message' => 'Settings deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete settings', 'error' => $e->getMessage()], 500);
        }
    }
}
