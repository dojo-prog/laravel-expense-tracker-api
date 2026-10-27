<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\IndexBudgetRequest;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexBudgetRequest $request)
    {
        $this->authorize('viewAny', Budget::class);

        $filters = $request->validated();

        $query = $request->user()->budgets()
            ->when(
                isset($filters['month'] ),
                fn ($query) =>
                    $query->where('month', $filters['month'])
            )
            ->when(
                isset($filters['category_id']),
                fn ($query) => 
                    $query->where('category_id', $filters['category_id'])
            );
        
        $budgets = $query
            ->with(['category', 'user'])
            ->orderBy('month', 'desc')
            ->paginate($filters['per_page'] ?? 15);

        return BudgetResource::collection($budgets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBudgetRequest $request)
    {
        $this->authorize('create', Budget::class);

        $budget = $request->user()->budgets()
            ->create($request->validated());

        return (new BudgetResource($budget))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        $this->authorize('view', $budget);

        return new BudgetResource($budget);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        $budget->update($request->validated());
        $budget->load(['category', 'user']);

        return new BudgetResource($budget);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        return response()->noContent();
    }
}
