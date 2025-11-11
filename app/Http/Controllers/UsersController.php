<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%")
                       ->orWhere('description', 'like', "%{$q}%"); // <-- username ki jagah description
                });
            })
            // IMPORTANT: yahan 'username' bilkul mat rakhna
            ->select('id','name','email','avatar','description')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('services.users', [
            'title'       => 'Users',
            'description' => 'Service Provider Users',
            'users'       => $users,
            'q'           => $q,
        ]);
    }
}
