<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\IndexExpenseRequest;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexExpenseRequest $request)
    {
        $this->authorize('viewAny', Expense::class);

        $filters = $request->validated();
        
        $query = $request->user()->expenses()
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) =>
                    $query->where('description', 'ilike', "%{$search}%")
            )
            ->when(
                isset($filters['category_id']), 
                fn ($query) =>
                    $query->where('category_id', $filters['category_id'])    
            )
            ->when(
                isset($filters['min_amount']),
                fn ($query) =>
                    $query->where('amount', '>=', $filters['min_amount'])
            )
            ->when(
                isset($filters['max_amount']),
                fn ($query) =>
                    $query->where('amount', '<=', $filters['max_amount'])
            )
            ->when(
                isset($filters['from']),
                fn ($query) =>
                    $query->where('expense_date', '>=' , $filters['from'])
            )
            ->when(
                isset($filters['to']),
                fn ($query) =>
                    $query->where('expense_date', '<=' , $filters['to'])
            );

        $sortBy = $filters['sort_by'] ?? 'expense_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $expenses = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($filters['per_page'] ?? 15);

        return ExpenseResource::collection($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $this->authorize('create', Expense::class);

        $expense = $request->user()->expenses()
            ->create($request->validated());

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);

        return new ExpenseResource($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        $expense->update($request->validated());

        return new ExpenseResource($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return response()->noContent();
    }
}
