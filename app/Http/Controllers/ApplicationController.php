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
        $data = $request->validate(['name' => 'required|string']);
        return Application::create($data);
    }

    public function show(Application $application)
    {
        return $application;
    }

    public function update(Request $request, Application $application)
    {
        $application->update($request->only('name'));
        return $application;
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
