<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(50);
        return view('add-user', compact('users'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4|confirmed',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        // ✅ Create user
        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'permissions' => $request->permissions ?? [],
            'userRole'    => 1,
            'is_admin'    => 1,
        ]);

        // ✅ Generate Sanctum token
        $fullToken = $user->createToken('app-token')->plainTextToken;

        // ✅ Remove token ID (1|)
        $plainToken = explode('|', $fullToken)[1];

        // ✅ Save only clean token
        $user->app_token = $plainToken;
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully!');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:4|confirmed',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->permissions = $request->permissions ?? [];

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }
    public function edit(User $user)
    {
        return view('edit-user', compact('user'));
    }

    // Delete User
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    // Download CSV
    public function download()
    {
        $users = User::all();

        $csv = "ID,Name,Email,Permissions\n";

        foreach ($users as $user) {
            $perms = implode('|', $user->permissions ?? []);
            $csv .= $user->id . ',"' . $user->name . '","' . $user->email . '","' . $perms . "\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="users.csv"');
    }

    // Filter by column
    public function filter(Request $request)
    {
        $query = User::query();

        if ($request->filled('filter_column') && $request->filled('filter_operator') && $request->filled('filter_value')) {
            $column = $request->filter_column;
            $operator = $request->filter_operator;
            $value = $request->filter_value;

            if ($operator === 'like') {
                $query->where($column, 'like', "%{$value}%");
            } else {
                $query->where($column, $operator, $value);
            }
        }

        $users = $query->with('role')->paginate(50);
        $roles = Role::all(); // <-- added
        return view('add-user', compact('users', 'roles'));
    }

    // Row Filter (expression-based)
    public function rowFilter(Request $request)
    {
        $users = User::with('role')->get();

        if ($request->filled('row_filter_expression')) {
            $expression = $request->row_filter_expression;

            $users = $users->filter(function ($item) use ($expression) {
                $expr = $expression;
                foreach ($item->getAttributes() as $key => $val) {
                    $expr = str_replace($key, "'" . $val . "'", $expr);
                }
                return eval("return {$expr};"); // ⚠️ careful with eval
            });
        }

        $roles = Role::all(); // <-- added
        return view('add-user', ['users' => $users, 'roles' => $roles]);
    }

    // Sort
    public function sort(Request $request)
    {
        $query = User::query();

        if ($request->filled('sort_columns')) {
            foreach ($request->sort_columns as $sort) {
                if (!empty($sort['column']) && in_array($sort['direction'], ['asc', 'desc'])) {
                    $query->orderBy($sort['column'], $sort['direction']);
                }
            }
        }

        $users = $query->with('role')->paginate(50);
        $roles = Role::all(); // <-- added
        return view('add-user', compact('users', 'roles'));
    }

    // Aggregate
    public function aggregate(Request $request)
    {
        $function = $request->aggregate_function;
        $column = $request->aggregate_column;

        $result = null;
        switch ($function) {
            case 'count':
                $result = User::count();
                break;
            case 'sum':
                $result = User::sum($column);
                break;
            case 'avg':
                $result = User::avg($column);
                break;
            case 'min':
                $result = User::min($column);
                break;
            case 'max':
                $result = User::max($column);
                break;
        }

        return redirect()->back()->with('aggregateResult', [
            'function' => $function,
            'column' => $column,
            'result' => $result
        ]);
    }

    // Compute (expression-based)
    public function compute(Request $request)
    {
        $expression = $request->compute_expression;
        $users = User::all();
        $results = [];

        foreach ($users as $user) {
            $expr = $expression;
            foreach ($user->getAttributes() as $key => $val) {
                $expr = str_replace($key, "'" . $val . "'", $expr);
            }
            $results[] = eval("return {$expr};"); // ⚠️ careful with eval
        }

        return redirect()->back()->with('computeResult', implode(', ', $results));
    }
}
