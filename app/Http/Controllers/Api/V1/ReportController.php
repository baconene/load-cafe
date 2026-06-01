<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function dailySales(): JsonResponse
    {
        $this->checkPermission();

        $date = request()->input('date') ? Carbon::parse(request()->input('date')) : null;
        $report = $this->reportService->getDailySalesReport($date);

        return response()->json($report);
    }

    public function monthlySales(): JsonResponse
    {
        $this->checkPermission();

        $year = request()->input('year', Carbon::now()->year);
        $month = request()->input('month', Carbon::now()->month);

        $report = $this->reportService->getMonthlySalesReport($year, $month);

        return response()->json($report);
    }

    public function productSales(): JsonResponse
    {
        $this->checkPermission();

        $startDate = request()->input('start_date') ? Carbon::parse(request()->input('start_date')) : null;
        $endDate = request()->input('end_date') ? Carbon::parse(request()->input('end_date')) : null;

        $report = $this->reportService->getProductSalesReport($startDate, $endDate);

        return response()->json($report);
    }

    public function inventoryValuation(): JsonResponse
    {
        $this->checkPermission();

        $report = $this->reportService->getInventoryValuation();

        return response()->json($report);
    }

    public function profitLoss(): JsonResponse
    {
        $this->checkPermission();

        $start = request()->input('start_date')
            ? Carbon::parse(request()->input('start_date'))
            : Carbon::now()->startOfMonth();
        $end = request()->input('end_date')
            ? Carbon::parse(request()->input('end_date'))
            : Carbon::now()->endOfMonth();
        $includeCogs = request()->boolean('include_cogs', true);

        return response()->json($this->reportService->getProfitLossReport($start, $end, $includeCogs));
    }

    public function inventoryTransactions(): JsonResponse
    {
        $this->checkPermission();

        $query = \App\Models\InventoryTransaction::with(['ingredient', 'user'])
            ->orderByDesc('created_at');

        if (request()->input('date_from')) {
            $query->where('created_at', '>=', Carbon::parse(request()->input('date_from'))->startOfDay());
        }
        if (request()->input('date_to')) {
            $query->where('created_at', '<=', Carbon::parse(request()->input('date_to'))->endOfDay());
        }
        if (request()->input('type')) {
            $query->where('type', request()->input('type'));
        }
        if (request()->input('ingredient_id')) {
            $query->where('ingredient_id', request()->input('ingredient_id'));
        }

        return response()->json($query->paginate(50));
    }

    public function reportOrders(): JsonResponse
    {
        $this->checkPermission();

        $paginated = \App\Models\Order::with('queueNumber:id,number')
            ->withCount('items')
            ->when(request('status'),         fn($q) => $q->where('status', request('status')))
            ->when(request('payment_status'), fn($q) => $q->where('payment_status', request('payment_status')))
            ->when(request('date_from'),      fn($q) => $q->whereDate('created_at', '>=', request('date_from')))
            ->when(request('date_to'),        fn($q) => $q->whereDate('created_at', '<=', request('date_to')))
            ->when(request('search'),         fn($q) => $q->where(function ($q) {
                $s = request('search');
                $q->where('id', $s)
                  ->orWhere('notes', 'like', "%{$s}%")
                  ->orWhere('table_number', 'like', "%{$s}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json([
            'data' => $paginated->getCollection()->map(fn($o) => [
                'id'             => $o->id,
                'queue_number'   => $o->queueNumber?->number,
                'order_type'     => $o->order_type,
                'status'         => $o->status,
                'payment_status' => $o->payment_status,
                'table_number'   => $o->table_number,
                'notes'          => $o->notes,
                'total_amount'   => (float) $o->total_amount,
                'items_count'    => $o->items_count,
                'created_at'     => $o->created_at,
            ]),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
        ]);
    }

    public function dailySalesChart(): JsonResponse
    {
        $this->checkPermission();

        $period = request()->input('period', '7');
        $now    = Carbon::now();

        $start = match ($period) {
            'ytd'   => $now->copy()->startOfYear()->startOfDay(),
            '90'    => $now->copy()->subDays(89)->startOfDay(),
            '30'    => $now->copy()->subDays(29)->startOfDay(),
            default => $now->copy()->subDays(6)->startOfDay(),
        };
        $end = $now->copy()->endOfDay();

        $rows = \App\Models\Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_sales')
            )
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $d      = $cursor->toDateString();
            $row    = $rows->get($d);
            $result[] = [
                'date'   => $d,
                'total'  => $row ? round((float) $row->total_sales, 2) : 0.0,
                'orders' => $row ? (int) $row->order_count : 0,
            ];
            $cursor->addDay();
        }

        return response()->json($result);
    }

    private function checkPermission(): void
    {
        if (! auth()->user()?->hasAnyRole('admin') && ! auth()->user()?->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized');
        }
    }
}
