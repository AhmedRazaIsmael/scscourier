<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class UniversalTableController extends Controller
{
    // 🔹 Main Handler (Index + Filtering + Sorting + Pagination)
    public function handle(Request $request)
    {
        $table = $request->input('table'); // e.g., 'thirdparty', 'orders'
        $model = $this->getModel($table);
        $columns = $this->getColumns($table);

        $query = $model::query();

        // ✅ Filter
        if ($request->filled('filter_column')) {
            $query->where($request->filter_column, $request->filter_operator, $request->filter_value);
        }

        // ✅ Row Filter (Expression)
        if ($request->filled('row_filter_expression')) {
            // Simple eval or custom parser
            // For now skipping complex eval, assume safe expressions
        }

        // ✅ Sorting
        if ($request->filled('sort_column')) {
            $query->orderBy($request->sort_column, $request->sort_direction ?? 'asc');
        }

        $data = $query->paginate(10);

        return view('universal-table', compact('data', 'columns', 'table'));
    }

    // 🔹 Compute Example
    public function compute(Request $request)
    {
        $table = $request->input('table');
        $model = $this->getModel($table);
        $expression = $request->input('compute_expression');

        $data = $model::all()->map(function($row) use ($expression) {
            // Simple string replacement for demonstration
            return eval('return '.$expression.';');
        });

        return response()->json($data);
    }

    // 🔹 Download Example
    public function download(Request $request)
    {
        $table = $request->input('table');
        $model = $this->getModel($table);
        $data = $model::all();

        // Generate CSV/XLSX/HTML
        // For now, simple CSV
        $filename = $table.'_'.date('Y-m-d_H-i-s').'.csv';
        $csv = implode(',', array_keys((array)$data->first()))."\n";
        foreach ($data as $row) {
            $csv .= implode(',', (array)$row)."\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    // 🔹 Chart / RowFilter / Aggregate can be similar
    public function chart(Request $request) { /* ... */ }
    public function rowFilter(Request $request) { /* ... */ }
    public function aggregate(Request $request) { /* ... */ }

    // 🔹 Model Mapping
    private function getModel($table)
    {
        $map = [
            'thirdparty' => \App\Models\ThirdPartyBooking::class,
            'orders' => \App\Models\Order::class,
        ];
        return $map[$table] ?? abort(404);
    }

    // 🔹 Columns Mapping
    private function getColumns($table)
    {
        $map = [
            'thirdparty' => ['book_no','book_date','company_name','ref_no','remarks','customer','shipper','consignee','updated_by','updated_at'],
            'orders' => ['order_no','customer_name','status','total','created_at'],
        ];
        return $map[$table] ?? abort(404);
    }
}
