<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class SkillController extends Controller
{
    public function index()
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['skills' => []]);

        $skills = $child->skillScores()->get();
        $allCategories = ['Logic', 'Creativity', 'Memory', 'Communication', 'Leadership', 'Problem Solving', 'Focus', 'Innovation'];

        $skillMap = $skills->keyBy('category');
        $radarData = collect($allCategories)->map(fn($cat) => [
            'category' => $cat,
            'score'    => $skillMap->get($cat)?->score ?? 0,
        ]);

        $strongest = $skills->sortByDesc('score')->first();
        $weakest   = $skills->sortBy('score')->first();

        return response()->json([
            'skills'      => $skills,
            'radar_data'  => $radarData,
            'strongest'   => $strongest,
            'weakest'     => $weakest,
            'total_quizzes' => $skills->sum('quizzes_taken'),
        ]);
    }

    public function progress()
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['progress' => []]);

        $progress = $child->skillProgress()
            ->orderBy('recorded_at')
            ->get()
            ->groupBy('category')
            ->map(fn($items, $cat) => [
                'category' => $cat,
                'history'  => $items->map(fn($i) => [
                    'score' => $i->score,
                    'date'  => $i->recorded_at->format('M d'),
                ]),
            ])->values();

        return response()->json(['progress' => $progress]);
    }
}
