<?php

namespace App\Http\Controllers;

use DB;
use Storage;
use App\Models\City;
use App\Models\User;
use App\Models\State;
use App\Models\Country;
use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Builder;

class MasterController extends Controller
{
    public function city(Request $request)
    {
        $query = City::with(['country', 'state']);
        $grid = $this->applyGridFeatures($request, $query, City::class);

        $cities = $grid['query']->paginate(50)->appends($request->all());
        $visibleColumns = session('visible_columns', ['City Code', 'City', 'Country', 'State', 'Province']);

        return view('city', [
            'cities' => $cities,
            'aggregateResult' => $grid['aggregateResult'],
            'computeExpression' => $grid['computeExpression'],
            'controlBreak' => $grid['controlBreak'],
            'visibleColumns' => $visibleColumns,
        ]);
    }

    public function download(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $dataOnly = $request->has('data_only');

        // Same query logic
        $query = City::with(['country', 'state']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhereHas('country', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('state', fn($q) => $q->where('name', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->has('sort_columns')) {
            foreach ($request->sort_columns as $sort) {
                $column = $sort['column'] ?? null;
                $direction = strtolower($sort['direction'] ?? 'asc');
                if (!$column || !in_array($direction, ['asc', 'desc'])) continue;

                if (in_array($column, ['code', 'name'])) {
                    $query->orderBy($column, $direction);
                } elseif ($column === 'country') {
                    $query->join('countries', 'cities.country_id', '=', 'countries.id')
                        ->orderBy('countries.name', $direction)
                        ->select('cities.*');
                } elseif (in_array($column, ['state', 'province'])) {
                    $query->join('states', 'cities.state_id', '=', 'states.id')
                        ->orderBy('states.name', $direction)
                        ->select('cities.*');
                }
            }
        }

        $computeExpression = $request->compute_expression;
        $cities = $query->get();

        $data = $cities->map(function ($city) use ($computeExpression, $dataOnly) {
            $row = $dataOnly ? [] : [
                'City Code' => $city->code,
                'City Name' => $city->name,
                'Country' => $city->country->name ?? '',
                'State' => $city->state->name ?? '',
                'Province' => $city->state->name ?? '',
            ];

            if (!empty($computeExpression)) {
                $expr = str_replace(
                    ['name', 'code', 'state_id', 'country_id'],
                    [
                        "'" . ($city->name ?? '') . "'",
                        "'" . ($city->code ?? '') . "'",
                        "'" . ($city->state->name ?? '') . "'",
                        "'" . ($city->country->name ?? '') . "'"
                    ],
                    $computeExpression
                );

                try {
                    eval('$computed = ' . $expr . ';');
                } catch (\Throwable $e) {
                    $computed = '[Error]';
                }

                $row['Computed'] = $computed ?? '';
            }

            return $row;
        });

        $filename = 'cities_' . now()->format('Ymd_His') . '.' . $format;

        if ($format === 'csv') {
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];
            $callback = function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, array_keys($data[0] ?? []));
                foreach ($data as $row) fputcsv($handle, $row);
                fclose($handle);
            };
            return Response::stream($callback, 200, $headers);
        }

        if ($format === 'xlsx') {
            return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $data;
                public function __construct($data)
                {
                    $this->data = $data;
                }
                public function collection()
                {
                    return collect($this->data);
                }
                public function headings(): array
                {
                    return array_keys($this->data->first() ?? []);
                }
            }, $filename);
        }

        if ($format === 'html') {
            return response()->view('city_export_html', ['data' => $data]);
        }

        return back()->with('error', 'Unsupported format');
    }


    // Show create city form
    public function create()
    {
        $countries = Country::all();
        return view('city-create', compact('countries'));
    }

    // Store new city
    public function store(Request $request)
    {
        try {
            $request->validate([
                'code'       => 'nullable|unique:cities,code',
                'name'       => 'required|string|max:255',
                'country_id' => 'required|exists:countries,id',
            ]);

            City::create([
                'code'         => $request->code,
                'name'         => $request->name,
                'country_id'   => $request->country_id,
                'state_id'     => null,
                'state_code'   => null,
                'country_code' => Country::find($request->country_id)->iso2 ?? null,
            ]);

            return redirect()->back()->with('success', 'City created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create city. Please try again.');
        }
    }

    // AJAX: Get states by country
    public function getStates($country_id)
    {
        return response()->json(State::where('country_id', $country_id)->get());
    }

    // AJAX: Get cities by state
    public function getCities($state_id)
    {
        return response()->json(City::where('state_id', $state_id)->get());
    }

    // AJAX: Auto-detect country & state by city name
    public function getCountryByCity($cityName)
    {
        $city = City::with('country')
            ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
            ->first();

        if (!$city) {
            return response()->json(['error' => 'City not found'], 404);
        }

        return response()->json([
            'country_id'   => $city->country_id,
            'country_code' => $city->country_code,
        ]);
    }

    // public function customer()
    // {
    //     $customers = Customer::with(['country', 'city'])->latest()->get();
    //     return view('customer', compact('customers'));
    // }

    public function customer(Request $request)
    {
        $query = Customer::with(['country', 'city']);
        $grid = $this->applyGridFeatures($request, $query, Customer::class);

        $customers = $grid['query']->paginate(50)->appends($request->all());
        $visibleColumns = session('visible_columns', [
            'Customer Code',
            'Customer Name',
            'Contact Person',
            'Contact No',
            'Email',
            'NTN',
            'Territory',
            'Sales Person',
            'Country',
            'City'
        ]);

        return view('customer', [
            'customers' => $customers,
            'aggregateResult' => $grid['aggregateResult'],
            'computeExpression' => $grid['computeExpression'],
            'controlBreak' => $grid['controlBreak'],
            'visibleColumns' => $visibleColumns,
        ]);
    }


    public function createCustomer()
    {
        $countries = Country::all();
        $cities = City::all();
        $users = User::all();
        return view('create-customer', compact('countries', 'cities', 'users'));
    }

    // public function storeCustomer(Request $request)
    // {
    //     $request->validate([
    //         'customer_name'    => 'required|string|max:255',
    //         'contact_person_1' => 'required|string|max:255',
    //         'contact_no_1'     => 'required|string|max:20',
    //         'email_1'          => 'required|email',
    //         'address_1'        => 'required|string',
    //         'country_id'       => 'required|exists:countries,id',
    //         'city_id'          => 'required|exists:cities,id',
    //         'product'          => 'required|string',
    //         'ntn'              => 'nullable|string|max:30',
    //         'nic'              => 'nullable|string|max:20', // ✅ added
    //         'business_type'    => 'required|string',
    //         'attachment'       => 'nullable|file|max:5120', // max 5MB
    //         'other_business_type' => 'nullable|string|max:255', // ✅ added
    //     ]);

    //     $lastId = Customer::max('id') ?? 0;
    //     $nextId = $lastId + 1;
    //     $code = 'BOX-01-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

    //     $attachmentPath = null;
    //     if ($request->hasFile('attachment')) {
    //         $attachmentPath = $request->file('attachment')->store('attachments', 'public');
    //     }

    //     Customer::create([
    //         'code'                => $code,
    //         'customer_name'       => $request->customer_name,
    //         'contact_person_1'    => $request->contact_person_1,
    //         'contact_no_1'        => $request->contact_no_1,
    //         'email_1'             => $request->email_1,
    //         'address_1'           => $request->address_1,
    //         'contact_person_2'    => $request->contact_person_2,
    //         'contact_no_2'        => $request->contact_no_2,
    //         'email_2'             => $request->email_2,
    //         'address_2'           => $request->address_2,
    //         'ntn'                 => $request->ntn,
    //         'nic'                 => $request->nic,
    //         'website'             => $request->website,
    //         'open_date'           => now()->toDateString(),
    //         'parent_customer_code'=> $request->parent_customer_code,
    //         'territory'           => $request->territory,
    //         'sales_person'        => $request->sales_person,
    //         'tariff_code'         => $request->tariff_code,
    //         'status'              => $request->status ?? 1,
    //         'country_id'          => $request->country_id,
    //         'city_id'             => $request->city_id,
    //         'product'             => $request->product,
    //         'business_type'       => $request->business_type,
    //         'attachment'          => $attachmentPath,
    //     ]);

    //     return redirect()->route('customer.index')->with('success', 'Customer created successfully!');
    // }

    public function storeCustomer(Request $request)
    {
        // Validate form (all fields nullable except minimal required ones)
        $request->validate([
            'customer_name'       => 'nullable|string|max:255',
            'contact_person_1'    => 'nullable|string|max:255',
            'contact_no_1'        => 'nullable|string|max:20',
            'email_1'             => 'nullable|email|max:255',
            'address_1'           => 'nullable|string',
            'contact_person_2'    => 'nullable|string|max:255',
            'contact_no_2'        => 'nullable|string|max:20',
            'email_2'             => 'nullable|email|max:255',
            'address_2'           => 'nullable|string',
            'ntn'                 => 'nullable|string|max:30',
            'nic'                 => 'nullable|string|max:20',
            'website'             => 'nullable|string|max:255',
            'country_id'          => 'nullable|exists:countries,id',
            'city_id'             => 'nullable|exists:cities,id',
            'parent_customer_code' => 'nullable|string|max:50',
            'territory'           => 'nullable|string|max:255',
            'sales_person'        => 'nullable|string|max:255',
            'product'             => 'nullable|string|max:50',
            'tariff_code'         => 'nullable|string|max:20',
            'status'              => 'nullable|boolean',
            'business_type'       => 'nullable|string|max:255',
            'other_business_type' => 'nullable|string|max:255',
            'attachment'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Generate auto customer code
        $lastId = Customer::max('id') ?? 0;
        $nextId = $lastId + 1;
        $code = 'ABC-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Handle Business Type "Other"
        $businessType = $request->business_type === 'Other'
            ? $request->other_business_type
            : $request->business_type;

        // Handle optional attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('attachments', 'public');
        }

        // Create customer
        $customer = Customer::create([
            'code'                => $code,
            'customer_name'       => $request->customer_name,
            'contact_person_1'    => $request->contact_person_1,
            'contact_no_1'        => $request->contact_no_1,
            'email_1'             => $request->email_1,
            'address_1'           => $request->address_1,
            'contact_person_2'    => $request->contact_person_2,
            'contact_no_2'        => $request->contact_no_2,
            'email_2'             => $request->email_2,
            'address_2'           => $request->address_2,
            'ntn'                 => $request->ntn,
            'nic'                 => $request->nic,
            'website'             => $request->website,
            'open_date'           => now()->toDateString(),
            'parent_customer_code' => $request->parent_customer_code,
            'territory'           => $request->territory,
            'sales_person'        => $request->sales_person,
            'product'             => $request->product,
            'tariff_code'         => $request->tariff_code,
            'status'              => $request->status ?? 1,
            'country_id'          => $request->country_id,
            'city_id'             => $request->city_id,
            'business_type'       => $businessType,
            'attachment'          => $attachmentPath,
        ]);

        // Save attachment in customer_attachments table if exists
        if ($attachmentPath) {
            \Illuminate\Support\Facades\DB::table('customer_attachments')->insert([
                'customer_id' => $customer->id,
                'file_path'   => $attachmentPath,
                'filename'    => $file->getClientOriginalName(),
                'mimetype'    => $file->getMimeType(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // User table mein bhi insert karo
        User::create([
            'name'      => $request->customer_name,
            'email'     => $request->email_1,
            'password'  => bcrypt('12345678'),
            'userRole'  => 2,
            'is_admin'  => 0,
        ]);

        return redirect()->route('customer.index')->with('success', 'Customer created successfully!');
    }

    public function chart(Request $request)
    {
        $model = $request->input('model'); // 'city' or 'customer'
        $label = $request->input('label');
        $value = $request->input('value');
        $function = $request->input('function', 'count');
        $chartType = $request->input('type', 'bar');

        if (!$model || !$label || !$value || !$function) {
            return back()->with('error', 'Missing chart parameters.');
        }

        // Determine model class
        $modelClass = match ($model) {
            'city' => \App\Models\City::class,
            'customer' => \App\Models\Customer::class,
            default => abort(400, 'Invalid model'),
        };

        // Build query
        $data = $modelClass::selectRaw("$label as label, " . strtoupper($function) . "($value) as value")
            ->groupBy($label)
            ->orderBy('value', 'desc')
            ->limit(20)
            ->get();

        return view('chart', [
            'labels' => $data->pluck('label'),
            'values' => $data->pluck('value'),
            'chartType' => $chartType,
            'labelTitle' => $request->input('label_title', ucfirst($label)),
            'valueTitle' => $request->input('value_title', ucfirst($value)),
            'model' => $model,
        ]);
    }


    public function setColumns(Request $request)
    {
        $columns = $request->input('visible_columns', []);
        session(['visible_columns' => $columns]);
        return redirect()->back();
    }

    /**
     * ==============================
     * UNIVERSAL GRID FEATURE HANDLER
     * ==============================
     * Supports:
     *  - Search
     *  - Column Filters
     *  - Row Filters (raw SQL)
     *  - Sorting (with joins for related tables)
     *  - Aggregates (sum, avg, count, min, max, median)
     *  - Compute expressions
     *  - Control Break grouping
     */
    private function applyGridFeatures(Request $request, Builder $query, $modelClass)
    {
        // 🔁 1. Column Mappings
        $columnMap = match ($modelClass) {
            \App\Models\City::class => [
                'id'         => 'id',
                'name'       => 'name',
                'code'       => 'code',
                'state'      => 'state_id',
                'province'   => 'state_id',
                'country'    => 'country_id',
                'country_id' => 'country_id',
                'state_id'   => 'state_id',
                'latitude'   => 'latitude',
                'longitude'  => 'longitude',
                'flag'       => 'flag',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            \App\Models\Customer::class => [
                'id'               => 'id',
                'code'             => 'code',
                'customer_name'    => 'customer_name',
                'contact_person_1' => 'contact_person_1',
                'contact_no_1'     => 'contact_no_1',
                'email_1'          => 'email_1',
                'ntn'              => 'ntn',
                'nic'              => 'nic',
                'website'          => 'website',
                'address_1'        => 'address_1',
                'territory'        => 'territory',
                'sales_person'     => 'sales_person',
                'product'          => 'product',
                'business_type'    => 'business_type',
                'country'          => 'country_id',
                'city'             => 'city_id',
                'open_date'        => 'open_date',
                'status'           => 'status',
                'created_at'       => 'created_at',
                'updated_at'       => 'updated_at',
            ],
            default => [],
        };

        // 🔍 2. Global Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $modelClass) {
                if ($modelClass === \App\Models\City::class) {
                    $q->where('cities.name', 'LIKE', "%{$search}%")
                        ->orWhere('cities.code', 'LIKE', "%{$search}%")
                        ->orWhereHas('country', fn($r) => $r->where('name', 'LIKE', "%{$search}%"))
                        ->orWhereHas('state', fn($r) => $r->where('name', 'LIKE', "%{$search}%"));
                } else {
                    $q->where('customers.customer_name', 'LIKE', "%{$search}%")
                        ->orWhere('customers.code', 'LIKE', "%{$search}%")
                        ->orWhere('customers.contact_person_1', 'LIKE', "%{$search}%")
                        ->orWhere('customers.email_1', 'LIKE', "%{$search}%")
                        ->orWhereHas('country', fn($r) => $r->where('name', 'LIKE', "%{$search}%"))
                        ->orWhereHas('city', fn($r) => $r->where('name', 'LIKE', "%{$search}%"));
                }
            });
        }

        // 🎯 3. Column Filter
        if ($request->filled('filter_column') && $request->filled('filter_operator') && $request->filled('filter_value')) {
            $col = $columnMap[$request->filter_column] ?? $request->filter_column;
            $op  = $request->filter_operator;
            $val = $request->filter_value;

            if (in_array($op, ['=', '!=', 'like'])) {
                $query->where($col, $op, $op === 'like' ? "%$val%" : $val);
            }
        }

        // 🧠 4. Row Filter (Raw SQL expression)
        if ($request->filled('row_filter_expression')) {
            try {
                $expr = str_ireplace(
                    ['state', 'country', 'province'],
                    ['states.name', 'countries.name', 'states.name'],
                    $request->row_filter_expression
                );
                $query->whereRaw($expr);
            } catch (\Throwable $e) {
                session()->flash('error', 'Invalid row filter expression.');
            }
        }

        // 🔽 5. Sorting (final fixed version for City & Customer)
        if ($request->has('sort_columns')) {
            foreach ($request->sort_columns as $sort) {
                $col = $sort['column'] ?? null;
                $dir = strtolower($sort['direction'] ?? 'asc');
                if (!$col || !in_array($dir, ['asc', 'desc'])) continue;

                $dbCol = $columnMap[$col] ?? $col;

                // ✅ CITY MODULE SORTING
                if ($modelClass === \App\Models\City::class) {
                    if ($col === 'country') {
                        $query->leftJoin('countries', 'countries.id', '=', 'cities.country_id')
                            ->orderBy('countries.name', $dir)
                            ->select('cities.*');
                    } elseif (in_array($col, ['state', 'province'])) {
                        $query->leftJoin('states', 'states.id', '=', 'cities.state_id')
                            ->orderBy('states.name', $dir)
                            ->select('cities.*');
                    } elseif (in_array($dbCol, ['name', 'code', 'latitude', 'longitude', 'flag'])) {
                        $query->orderBy("cities.$dbCol", $dir);
                    }

                    // ✅ CUSTOMER MODULE SORTING
                } elseif ($modelClass === \App\Models\Customer::class) {
                    if ($col === 'country') {
                        $query->leftJoin('countries', 'countries.id', '=', 'customers.country_id')
                            ->orderBy('countries.name', $dir)
                            ->select('customers.*');
                    } elseif ($col === 'city') {
                        $query->leftJoin('cities', 'cities.id', '=', 'customers.city_id')
                            ->orderBy('cities.name', $dir)
                            ->select('customers.*');
                    } else {
                        if (in_array($dbCol, [
                            'code',
                            'customer_name',
                            'contact_person_1',
                            'contact_no_1',
                            'email_1',
                            'ntn',
                            'nic',
                            'territory',
                            'sales_person',
                            'product',
                            'business_type',
                            'status',
                            'created_at',
                            'updated_at'
                        ])) {
                            $query->orderBy("customers.$dbCol", $dir);
                        }
                    }
                }
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $aggregateResult = null;
        $appliedFunction = null;

        if ($request->filled('aggregate_function') && $request->filled('aggregate_column')) {
            $func = strtolower($request->aggregate_function);
            $reqCol = $request->aggregate_column;

            // Apply column mapping safely
            $col = $columnMap[$reqCol] ?? $reqCol;

            // Add correct table prefix for safety
            $table = (new $modelClass)->getTable();
            $colWithTable = "$table.$col";

            try {
                $schema = app('db')->connection()->getSchemaBuilder();

                if (!$schema->hasColumn($table, $col)) {
                    throw new \Exception("Invalid aggregate column: $col");
                }

                // Detect column data type (using Doctrine)
                $columnType = \DB::getDoctrineColumn($table, $col)->getType()->getName();

                // Auto-convert SUM/AVG for text columns to COUNT()
                if (in_array($func, ['sum', 'avg']) && !in_array($columnType, ['integer', 'decimal', 'float', 'double'])) {
                    $func = 'count';
                }

                if (in_array($func, ['sum', 'avg', 'count', 'min', 'max'])) {
                    $aggregateResult = $modelClass::selectRaw(strtoupper($func) . "($colWithTable) as result")->value('result');
                    $appliedFunction = strtoupper($func);
                } elseif ($func === 'median') {
                    $values = $modelClass::orderBy($col)->pluck($col)->filter()->values();
                    $count = $values->count();
                    if ($count > 0) {
                        $mid = floor($count / 2);
                        $aggregateResult = $count % 2
                            ? $values[$mid]
                            : ($values[$mid - 1] + $values[$mid]) / 2;
                        $appliedFunction = 'MEDIAN';
                    }
                }
            } catch (\Throwable $e) {
                \Log::error("Aggregate failed: " . $e->getMessage());
                session()->flash('error', 'Invalid aggregate column or function.');
                $aggregateResult = null;
            }
        }
        // 🧮 7. Compute Expression (custom)
        $computeExpression = $request->compute_expression;

        // 👨‍👩‍👧‍👦 8. Control Break grouping
        $controlBreak = collect($request->input('control_break', []))
            ->filter(fn($r) => isset($r['column']) && $r['status'] === 'enabled')
            ->pluck('column')
            ->toArray();

        // ✅ Return all results
        return compact('query', 'aggregateResult', 'computeExpression', 'controlBreak', 'appliedFunction');
    }

    public function editCity($id)
    {
        $city = City::findOrFail($id);
        $countries = Country::all();

        return view('city-edit', compact('city', 'countries'));
    }

    public function updateCity(Request $request, $id)
    {
        try {
            $city = City::findOrFail($id);

            $request->validate([
                'code'       => "nullable|unique:cities,code,{$id}",
                'name'       => 'required|string|max:255',
                'country_id' => 'required|exists:countries,id',
            ]);

            $city->update([
                'code'         => $request->code,
                'name'         => $request->name,
                'country_id'   => $request->country_id,
                'state_id'     => null,
                'state_code'   => null,
                'country_code' => Country::find($request->country_id)->iso2 ?? null,
            ]);

            return redirect()->back()->with('success', 'City updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update city. Please try again.');
        }
    }



    // Delete city
    public function destroyCity($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return redirect()->route('city.index')->with('success', 'City Deleted Successfully!');
    }

    public function editCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $countries = Country::all();

        // Only load cities for the customer's country
        $cities = $customer->country_id
            ? City::where('country_id', $customer->country_id)->get()
            : collect();

        // Optional: If you have many users, load only active/needed ones
        $users = User::all(); // Could add ->limit(100) if too many

        return view('customer-edit', compact('customer', 'countries', 'cities', 'users'));
    }

    // Update customer
    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'customer_name'       => 'nullable|string|max:255',
            'contact_person_1'    => 'nullable|string|max:255',
            'contact_no_1'        => 'nullable|string|max:20',
            'email_1'             => 'nullable|email|max:255',
            'address_1'           => 'nullable|string',
            'contact_person_2'    => 'nullable|string|max:255',
            'contact_no_2'        => 'nullable|string|max:20',
            'email_2'             => 'nullable|email|max:255',
            'address_2'           => 'nullable|string',
            'ntn'                 => 'nullable|string|max:30',
            'nic'                 => 'nullable|string|max:20',
            'website'             => 'nullable|string|max:255',
            'country_id'          => 'nullable|exists:countries,id',
            'city_id'             => 'nullable|exists:cities,id',
            'parent_customer_code' => 'nullable|string|max:50',
            'territory'           => 'nullable|string|max:255',
            'sales_person'        => 'nullable|string|max:255',
            'product'             => 'nullable|string|max:50',
            'tariff_code'         => 'nullable|string|max:20',
            'status'              => 'nullable|boolean',
            'business_type'       => 'nullable|string|max:255',
            'other_business_type' => 'nullable|string|max:255',
            'attachment'          => 'nullable|file|max:5120',
        ]);

        // Handle Business Type "Other"
        $businessType = $request->business_type === 'Other'
            ? $request->other_business_type
            : $request->business_type;

        // Handle attachment update
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');

            // Delete old attachment if exists
            if ($customer->attachment) {
                Storage::disk('public')->delete($customer->attachment);
            }

            $customer->attachment = $path;
        }

        // Update customer
        $customer->update([
            'customer_name' => $request->customer_name,
            'contact_person_1' => $request->contact_person_1,
            'contact_no_1' => $request->contact_no_1,
            'email_1' => $request->email_1,
            'address_1' => $request->address_1,
            'contact_person_2' => $request->contact_person_2,
            'contact_no_2' => $request->contact_no_2,
            'email_2' => $request->email_2,
            'address_2' => $request->address_2,
            'ntn' => $request->ntn,
            'nic' => $request->nic,
            'website' => $request->website,
            'territory' => $request->territory,
            'sales_person' => $request->sales_person,
            'tariff_code' => $request->tariff_code,
            'status' => $request->status ?? 1,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'product' => $request->product,
            'business_type' => $businessType,
            'parent_customer_code' => $request->parent_customer_code,
            'attachment' => $customer->attachment, // keep updated if uploaded
        ]);

        return redirect()->route('customer.index')->with('success', 'Customer Updated Successfully!');
    }


    // Delete customer
    public function destroyCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customer.index')->with('success', 'Customer Deleted Successfully!');
    }


    public function getCitiesByCountry($country_id)
    {
        try {
            $cities = City::where('country_id', $country_id)
                ->select('id', 'name', 'state_id', 'code')
                ->get();
            return response()->json($cities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadCustomer($format)
    {
        $customers = Customer::with(['city', 'country'])->get([
            'code',
            'customer_name',
            'email_1',
            'contact_no_1',
            'product',
            'address_1',
            'ntn',
            'nic',
            'website',
            'sales_person'
        ]);

        if ($format === 'csv') {
            $filename = 'customers.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($customers) {
                $file = fopen('php://output', 'w');

                // Header row
                fputcsv($file, [
                    'Code',
                    'Name',
                    'Email',
                    'Phone',
                    'Product',
                    'Address',
                    'NTN',
                    'NIC',
                    'Website',
                    'Sales Person'
                ]);

                // Data rows
                foreach ($customers as $c) {
                    fputcsv($file, [
                        $c->code,
                        $c->customer_name,
                        $c->email_1,
                        $c->contact_no_1,
                        $c->product,
                        $c->address_1,
                        $c->ntn,
                        $c->nic,
                        $c->website,
                        $c->sales_person
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($format === 'xlsx') {
            $filename = 'customers.xlsx';
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($customers) {
                $file = fopen('php://output', 'w');

                // Simulate Excel by using tab-separated values
                fputs($file, implode("\t", [
                    'Code',
                    'Name',
                    'Email',
                    'Phone',
                    'Product',
                    'Address',
                    'NTN',
                    'NIC',
                    'Website',
                    'Sales Person'
                ]) . "\n");

                foreach ($customers as $c) {
                    fputs($file, implode("\t", [
                        $c->code,
                        $c->customer_name,
                        $c->email_1,
                        $c->contact_no_1,
                        $c->product,
                        $c->address_1,
                        $c->ntn,
                        $c->nic,
                        $c->website,
                        $c->sales_person
                    ]) . "\n");
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Unsupported format.');
    }

    public function bookingAttachments()
    {
        $attachments = DB::table('booking_attachments as ba')
            ->leftJoin('bookings as b', 'ba.booking_id', '=', 'b.id')
            ->leftJoin('customers as c', 'b.customer_id', '=', 'c.id')
            ->select(
                'ba.id',
                'b.bookNo as book_no',
                'c.code as customer_code',
                'c.customer_name',
                'c.product',
                'ba.file_path',
                'ba.filename',
                'ba.created_at',
                'ba.updated_at',
                DB::raw("CONCAT_WS(' ', b.created_by, '') as insert_by") // Adjust if you have a created_by column in bookings
            )
            ->orderBy('ba.created_at', 'desc')
            ->paginate(25); // Use paginate for links to work

        return view('booking-attachments', compact('attachments'));
    }


    public function downloadBookingAttachment($id)
    {
        $attachment = DB::table('booking_attachments')->where('id', $id)->first();

        if (!$attachment || !Storage::disk('public')->exists($attachment->file_path)) {
            return back()->with('error', 'Attachment not found.');
        }

        return response()->download(
            storage_path("app/public/{$attachment->file_path}"),
            $attachment->filename,
            ['Content-Type' => $attachment->file_type]
        );
    }

    public function label()
    {
        return view('testing-label');
    }
}
