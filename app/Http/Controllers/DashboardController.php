<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        Household $household
    ): Response {
        $headings = Category::query()
            ->where('household_id', $household->id)
            ->where('category_type', 'heading')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $heading) use ($household) {
                $balance = Category::query()
                    ->where('household_id', $household->id)
                    ->where('parent_category_id', $heading->id)
                    ->where('is_active', true)
                    ->where('tracks_balance', true)
                    ->sum('current_balance');

                return [
                    'id' => $heading->id,
                    'name' => $heading->name,
                    'icon' => $heading->icon,
                    'dashboard_image' => $heading->dashboard_image,
                    'balance' => (float) $balance,
                ];
            })
            ->values();

        $totalAvailable = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('code', '!=', 'income_pool')
            ->sum('current_balance');

        $watchCategories = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('category_type', '!=', 'heading')
            ->where(function ($query) {
                $query
                    ->where('needs_attention', true)
                    ->orWhere('current_balance', '<', 0);
            })
            ->orderByRaw('current_balance < 0 DESC')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'dashboard_image',
                'current_balance',
                'needs_attention',
            ]);

        return Inertia::render('households/dashboard/Index', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],
            'headings' => $headings,
            'totalAvailable' => (float) $totalAvailable,
            'watchCategories' => $watchCategories,
        ]);
    }
}
