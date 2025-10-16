<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        return Application::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string|alpha_dash:ascii|unique:applications,slug',
            'name' => 'required|string',
            'url_local' => 'required|url',
            'url_production' => 'required|url',
            'url_staging' => 'nullable|url',
            'login_path' => 'nullable|string',
            'target_param' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        return Application::create($data);
    }

    public function show(Application $application)
    {
        return $application;
    }

    public function update(Request $request, Application $application)
    {
        $data = $request->validate([
            'slug' => 'sometimes|required|string|alpha_dash:ascii|unique:applications,slug,' . $application->id,
            'name' => 'sometimes|required|string',
            'url_local' => 'sometimes|required|url',
            'url_production' => 'sometimes|required|url',
            'url_staging' => 'nullable|url',
            'login_path' => 'nullable|string',
            'target_param' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $application->update($data);
        return $application;
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function resolve(Request $request, string $slug)
    {
        $env = (string) $request->query('env', config('app.env'));
        $app = Application::where('slug', $slug)->first();
        if (!$app || !$app->is_active) {
            return response()->json(['message' => 'Application not found'], 404);
        }
        $base = $app->baseUrlForEnv($env);
        return [
            'slug' => $app->slug,
            'name' => $app->name,
            'base_url' => rtrim((string) $base, '/'),
            'login_path' => $app->login_path,
            'target_param' => $app->target_param ?: 'target',
            'env_used' => strtolower((string) $env),
        ];
    }
}
