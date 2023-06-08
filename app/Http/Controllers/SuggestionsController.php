<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class SuggestionsController extends Controller
{
    public function Index(){
            $users = User::all();
            return response()->json($users);
    }
}
