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
            'activity' => 'required|string|max:255|regex:/^[a-zA-ZåäöÅÄÖ0-9\s\-]+$/u',
            'details' => 'nullable|string|max:1000',
            'borg_scale' => 'required|numeric|min:1|max:10',
            'distance' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9åäöÅÄÖ\s\-\/]+$/u',
            'duration' => 'required|numeric|min:0|max:1440',
            'image_path' => 'nullable|string|max:255'
        ]);

        // Ifall validationen misslyckas skicka error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Sanitera input ytterligare (ta bort HTML/script-taggar)
        $data = [
            'when' => $request->when,
            'activity' => strip_tags($request->activity),
            'details' => strip_tags($request->details),
            'borg_scale' => $request->borg_scale,
            'distance' => strip_tags($request->distance),
            'duration' => $request->duration,
            'image_path' => $request->image_path
        ];

        // Skapa träning
        $workout = Workout::create($data);

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

        // Validation på uppdaterade fält
        $validator = Validator::make($request->all(), [
            'when' => 'sometimes|date',
            'activity' => 'sometimes|string|max:255|regex:/^[a-zA-ZåäöÅÄÖ0-9\s\-]+$/u',
            'details' => 'nullable|string|max:1000',
            'borg_scale' => 'sometimes|numeric|min:1|max:10',
            'distance' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9åäöÅÄÖ\s\-\/]+$/u',
            'duration' => 'sometimes|numeric|min:0|max:1440'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Sanitera input innan uppdatering
        $data = [];
        if ($request->has('when')) $data['when'] = $request->when;
        if ($request->has('activity')) $data['activity'] = strip_tags($request->activity);
        if ($request->has('details')) $data['details'] = strip_tags($request->details);
        if ($request->has('borg_scale')) $data['borg_scale'] = $request->borg_scale;
        if ($request->has('distance')) $data['distance'] = strip_tags($request->distance);
        if ($request->has('duration')) $data['duration'] = $request->duration;

        // Uppdatera endast dessa poster:
        $workout->update($data);

        return response()->json($workout);
    }

    // Ta bort
    public function delete($id)
    {
        $workout = Workout::find($id);

        if (!$workout) {
            return response()->json(['error' => 'Workout not found'], 404);
        }

        // Ta bort bilden om den finns
        if ($workout->image_path) {
            $fullPath = base_path('public/' . $workout->image_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
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
