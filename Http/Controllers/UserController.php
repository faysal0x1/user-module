<?php

namespace App\Modules\User\Http\Controllers;

use App\Helpers\QueryBuilderHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
	private const ROLE_LABELS = [
		'admin'          => 'Admin',
		'superadmin'     => 'Super Admin',
		'moderator'      => 'Moderator',
		'supermoderator' => 'Super Moderator',
		'user'           => 'User',
		'guest'          => 'Guest',
	];

	/**
	 * Display a listing of the resource.
	 */

	public function index(Request $request) {
		// Combine request methods to handle both GET and POST
		$query = User::query();

		$params = $request->isMethod('post') ? $request->all() : $request->query();

		$combinedRequest = new Request($params);

		$query = QueryBuilderHelper::apply($combinedRequest, $query, ['name', 'email', 'created_at', 'role'], ['name', 'created_at', 'role']);
		$users = QueryBuilderHelper::paginate($combinedRequest, $query);

		return Inertia::render('user/Index', [
			'users' => $users,
			'filters' => QueryBuilderHelper::filters($combinedRequest),
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create() {
		return Inertia::render('user/Create', [
			'roleOptions' => $this->roleOptions(),
			'defaultRole' => 'user',
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request) {
		$validated = $this->validateUser($request);

		try {
			User::create($validated);

			return success_route('users.index', 'User created successfully.');
		} catch (\Throwable $th) {
			report($th);

			return error_route('users.index', 'Failed to create user. Please try again.');
		}
	}

	/**
	 * Display the specified resource.
	 */
	public function show(User $user) {
		$user->load('roles:id,name');

		return Inertia::render('user/Show', [
			'user'          => $user,
			'assignedRoles' => $user->roles->pluck('name'),
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(User $user) {
		$user->load('roles:id,name');

		return Inertia::render('user/Edit', [
			'user'        => $user,
			'roleOptions' => $this->roleOptions(),
		]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, User $user) {
		$validated = $this->validateUser($request, $user);

		try {
			$user->update($validated);

			return success_route('users.index', 'User updated successfully.');
		} catch (\Throwable $th) {
			report($th);

			return error_route('users.index', 'Failed to update user. Please try again.');
		}
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(User $user) {
		try {
			$user->delete();

			return success_route('users.index', 'User deleted successfully.');
		} catch (\Throwable $th) {
			report($th);

			return error_route('users.index', 'Failed to delete user. Please try again.');
		}
	}

	private function validateUser(Request $request, ?User $user = null): array {
		$userId = $user?->id;

		$rules = [
			'name'                  => ['required', 'string', 'max:255'],
			'username'              => [
				'required',
				'string',
				'max:255',
				Rule::unique('users', 'username')->ignore($userId),
			],
			'email'                 => [
				'required',
				'email',
				'max:255',
				Rule::unique('users', 'email')->ignore($userId),
			],
			'phone'                 => [
				'nullable',
				'string',
				'max:30',
				Rule::unique('users', 'phone')->ignore($userId),
			],
			'role'                  => ['required', Rule::in(array_keys(self::ROLE_LABELS))],
			'password'              => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
			'is_verified'           => ['nullable', 'boolean'],
			'is_banned'             => ['nullable', 'boolean'],
		];

		$validated = $request->validate($rules);

		$validated['is_verified'] = $request->boolean('is_verified');
		$validated['is_banned']   = $request->boolean('is_banned');

		if ($user && empty($validated['password'])) {
			unset($validated['password']);
		}

		unset($validated['password_confirmation']);

		return $validated;
	}

	private function roleOptions(): array {
		$options = [];

		foreach (self::ROLE_LABELS as $value => $label) {
			$options[] = [
				'label' => $label,
				'value' => $value,
			];
		}

		return $options;
	}
}
