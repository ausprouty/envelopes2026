<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(
        Request $request,
        Household $household
    ): Response {


        return Inertia::render('households/categories/Index', [
            'household' => $household,
            'categories' => Category::query()
                ->where('household_id', $household->id)
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(
        Request $request,
        Household $household
    ): Response {


        return Inertia::render('households/categories/Edit', [
            'household' => $household,
            'category' => null,
            'parentCategories' => $this->parentCategories($household),
        ]);
    }

    public function store(
        Request $request,
        Household $household
    ): RedirectResponse {


        $validated = $this->validateCategory($request, $household);

        $validated['household_id'] = $household->id;

        Category::create($validated);

        return redirect()
            ->route('households.categories.index', $household)
            ->with('success', 'Category created.');
    }

    public function edit(
        Request $request,
        Household $household,
        Category $category
    ): Response {

        $this->authorizeCategory($household, $category);

        return Inertia::render('households/categories/Edit', [
            'household' => $household,
            'category' => $category,
            'parentCategories' => $this->parentCategories(
                $household,
                $category
            ),
        ]);
    }

    public function update(
        Request $request,
        Household $household,
        Category $category
    ): RedirectResponse {

        $this->authorizeCategory($household, $category);

        $validated = $this->validateCategory(
            $request,
            $household,
            $category
        );

        $category->update($validated);

        return redirect()
            ->route('households.categories.index', $household)
            ->with('success', 'Category updated.');
    }

    private function validateCategory(
        Request $request,
        Household $household,
        ?Category $category = null
    ): array {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'needs_attention' => ['required', 'boolean'],
            'dashboard_image' => ['nullable', 'string', 'max:255'],

            'parent_category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where('household_id', $household->id)
                    ),
                Rule::notIn(
                    $category ? [$category->id] : []
                ),
            ],

            'category_type' => [
                'required',
                'in:income,expense,asset,transfer,reimbursement,heading',
            ],

            'context' => [
                'required',
                'in:household,ministry_au,ministry_us,other',
            ],

            'tracks_balance' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'display_order' => ['required', 'integer'],
        ]);
    }

    private function parentCategories(
        Household $household,
        ?Category $category = null
    ) {
        return Category::query()
            ->where('household_id', $household->id)
            ->where('category_type', 'heading')
            ->when(
                $category,
                fn ($query) => $query->whereKeyNot($category->id)
            )
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    private function authorizeCategory(
        Household $household,
        Category $category
    ): void {
        abort_unless(
            $category->household_id === $household->id,
            404
        );
    }


}
