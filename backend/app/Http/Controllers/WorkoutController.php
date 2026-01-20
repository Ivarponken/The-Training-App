<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function workouts(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            return Workout::whereBetween('created_at', [$startDate, $endDate])->get();
        } else {
            return Workout::all();
        }
            return response()->json([
            'message' => 'start_date and/or end_date invalid'
        ], 400);
    }
}
