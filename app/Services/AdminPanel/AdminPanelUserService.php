<?php


namespace App\Services\AdminPanel;


use App\Models\User;
use Illuminate\Http\Request;


class AdminPanelUserService
{
    public function getUsers(Request $request){

        $request->validate([
            'search'    => 'nullable|string',
            'sort'      => 'in:asc,desc|nullable',
            'sort_field'=> 'in:login,id|nullable',
            'status'    => 'in:created,active,blocked'
        ]);

        $userQuery = User::query();

        if ($request->input('status')) {
            $userQuery->where('status', $request->input('status'));
        }

        if($request->input('search')){

            $search = $request->input('search');

            $userQuery
                    ->where('phone', 'LIKE', "%{$search}%")
                    ->orWhere('login', 'LIKE', "%{$search}%");
        }

        if($request->input('sort')&&$request->input('sort_field')){
            $userQuery->orderBy($request->input('sort_field'), $request->input('sort'));
        }

       return $userQuery->paginate(30);
    }
}
