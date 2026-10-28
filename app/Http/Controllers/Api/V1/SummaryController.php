<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Summary\IndexSummaryRequest;
use App\Http\Resources\SummaryResource;
use App\Models\Expense;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class SummaryController extends Controller
{
    /**
     * Display a summary of the authenticated user's expenses.
     */
    public function index(IndexSummaryRequest $request): SummaryResource
    {
        $this->authorize('viewAny', Expense::class);

        $filters = $request->validated();

        [$from, $to] = $this->resolveRange($filters);

        $user = $request->user();

        $totals = $this->totals($user, $from, $to);

        return new SummaryResource([
            'group_by' => $filters['group_by'] ?? 'category',
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'total_amount' => $this->formatAmount($totals->total_amount),
            'total_count' => (int) $totals->total_count,
            'groups' => ($filters['group_by'] ?? 'category') === 'category'
                ? $this->categoryGroups($user, $from, $to, $totals->total_amount)
                : $this->dateGroups($user, $from, $to),
        ]);
    }

    /**
     * Resolve the date range: explicit month, explicit from/to, or the current month.
     *
     * @param  array<string, mixed>  $filters
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function resolveRange(array $filters): array
    {
        if (! empty($filters['month'])) {
            $start = CarbonImmutable::parse($filters['month'].'-01');

            return [$start, $start->endOfMonth()];
        }

        $from = isset($filters['from'])
            ? CarbonImmutable::parse($filters['from'])
            : CarbonImmutable::today()->startOfMonth();

        $to = isset($filters['to'])
            ? CarbonImmutable::parse($filters['to'])
            : CarbonImmutable::today()->endOfMonth();

        return [$from, $to];
    }

    /**
     * Total amount and expense count over the range.
     */
    private function totals(User $user, CarbonImmutable $from, CarbonImmutable $to): object
    {
        return $user->expenses()
            ->whereBetween('expense_date', [$this->rangeStartComparand($from), $this->rangeEndComparand($to)])
            ->selectRaw('COALESCE(SUM(amount), 0) AS total_amount, COALESCE(COUNT(*), 0) AS total_count')
            ->toBase()
            ->first();
    }

    /**
     * Expense groups per category, including budget progress for single-month ranges.
     *
     * @return array<int, array<string, mixed>>
     */
    private function categoryGroups(
        User $user,
        CarbonImmutable $from,
        CarbonImmutable $to,
        string|int|float|null $totalAmount
    ): array {
        $rows = $user->expenses()
            ->leftJoin('categories', 'categories.id', '=', 'expenses.category_id')
            ->whereBetween('expenses.expense_date', [$this->rangeStartComparand($from), $this->rangeEndComparand($to)])
            ->groupBy('expenses.category_id', 'categories.id', 'categories.name', 'categories.color')
            ->selectRaw('expenses.category_id AS category_id, categories.id AS id, categories.name AS name, categories.color AS color, SUM(expenses.amount) AS group_total, COUNT(*) AS expense_count')
            ->orderByRaw('SUM(expenses.amount) DESC')
            ->toBase()
            ->get();

        $total = (float) $totalAmount;
        $budgets = $this->budgetsInMonth($user, $from, $to);

        return $rows
            ->map(function (object $row) use ($total, $budgets): array {
                $spent = (float) $row->group_total;

                $group = [
                    'category' => $row->category_id === null ? null : [
                        'id' => $row->id,
                        'name' => $row->name,
                        'color' => $row->color,
                    ],
                    'total_amount' => $this->formatAmount($spent),
                    'expense_count' => (int) $row->expense_count,
                    'share' => $total > 0 ? round($spent * 100 / $total, 2) : 0.0,
                ];

                $budget = $budgets->get($row->category_id);

                if ($budget !== null) {
                    $group['budget'] = [
                        'limit_amount' => $this->formatAmount($budget->limit_amount),
                        'remaining' => $this->formatAmount((float) $budget->limit_amount - $spent),
                        'percentage_used' => $budget->limit_amount > 0
                            ? round($spent * 100 / (float) $budget->limit_amount, 2)
                            : null,
                    ];
                }

                return $group;
            })
            ->all();
    }

    /**
     * Expense groups per day.
     *
     * @return array<int, array<string, mixed>>
     */
    private function dateGroups(User $user, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $user->expenses()
            ->whereBetween('expenses.expense_date', [$this->rangeStartComparand($from), $this->rangeEndComparand($to)])
            ->groupBy('expenses.expense_date')
            ->selectRaw('expenses.expense_date AS expense_date, SUM(expenses.amount) AS group_total, COUNT(*) AS expense_count')
            ->orderBy('expenses.expense_date')
            ->toBase()
            ->get()
            ->map(fn (object $row): array => [
                'date' => CarbonImmutable::parse($row->expense_date)->toDateString(),
                'total_amount' => $this->formatAmount($row->group_total),
                'expense_count' => (int) $row->expense_count,
            ])
            ->all();
    }

    /**
     * Budgets for the user overlapping the range, keyed by category.
     *
     * Budget progress is only meaningful for a single-month range, so an
     * empty collection is returned for anything wider than one month.
     */
    private function budgetsInMonth(User $user, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        if (! $from->isSameMonth($to) || ! $from->isSameDay($from->startOfMonth()) || ! $to->isSameDay($to->endOfMonth())) {
            return collect();
        }

        return $user->budgets()
            ->whereBetween('month', [$from->toDateString(), $to->toDateString()])
            ->get(['user_id', 'category_id', 'limit_amount'])
            ->keyBy('category_id');
    }

    /**
     * Inclusive date-comparison bound for the start of the range. Works across engines
     * whose DATE columns are compared to strings with different formats.
     */
    private function rangeStartComparand(CarbonImmutable $from): string
    {
        return $from->startOfDay()->format('Y-m-d H:i:s');
    }

    /**
     * Inclusive date-comparison bound for the end of the range.
     */
    private function rangeEndComparand(CarbonImmutable $to): string
    {
        return $to->endOfDay()->format('Y-m-d H:i:s');
    }

    /**
     * Normalize monetary values to a two-decimal string.
     */
    private function formatAmount(float|string|int|null $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
