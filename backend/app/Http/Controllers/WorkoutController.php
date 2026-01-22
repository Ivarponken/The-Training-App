<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class WorkoutController extends Controller
{
    public function workouts(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $activity = $request->input('activity');

        $query = Workout::query();

        if ($startDate && $endDate) {
            // om start eller slutdatum inte finns eller slut är före start skicka error
            if (!strtotime($startDate) || !strtotime($endDate) || $endDate < $startDate) {
                return response()->json([
                    'Error' => 'start_date and/or end_date invalid/missing'
                ], 400);
            }
            // Filtrera inom intervall (created_at)
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($activity) {
            $query->where('activity', $activity);
        }

        return $query->orderBy('when', 'desc')->get();
    }

    public function store(Request $request)
    {
        // Validation på vad användaren skickat in i olika fälten
        $validator = Validator::make($request->all(), [
            'when' => 'required|date',
            'activity' => 'required|string|max:255',
            'details' => 'nullable|string',
            'borg_scale' => 'required|numeric|min:0|max:10',
            'distance' => 'nullable|string|min:0',
            'duration' => 'nullable|numeric|min:0',
        ]);

        // Ifall validationen misslyckas skicka error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Skapa träning
        $workout = Workout::create($request->only([
            'when',
            'activity',
            'details',
            'borg_scale',
            'distance',
            'duration'
        ]));

        return response()->json($workout, 201);
    }

    //  Hittar enskilda på id
    public function show($id)
    {
        $workout = Workout::find($id);

        if (!$workout) {
            return response()->json([
                'error' => 'Workout not found'
            ], 404);
        }

        return response()->json($workout);
    }

    public function update(Request $request, $id)
    {
        $workout = Workout::find($id);

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        // Uppdatera endast dessa poster:
        $workout->update($request->only([
            'when',
            'activity',
            'details',
            'borg_scale',
            'distance',
            'duration'
        ]));

        return response()->json($workout);
    }

    // Ta bort
    public function delete($id)
    {
        $workout = Workout::find($id);

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        $workout->delete();

        return response()->json(['message' => 'Workout deleted successfully']);
    }

    // Få statistik på total antal träning, distans, tid, borg
   public function stats(Request $request)
{
    $query = Workout::query();

    if ($request->activity) {
        $query->where('activity', $request->activity);
    }

    $stats = $query->selectRaw('
        activity,
        COUNT(*) as total_workouts,
        SUM(duration) as total_duration,
        AVG(borg_scale) as avg_borg
    ')
    ->groupBy('activity')
    ->get();

    return response()->json($stats);
}
}
