<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        $suggestedUsersCount = $this->suggestedUsersCount();
        $connectedUsersCount = User::join('connections_user', 'users.id', '=', 'connections_user.user_id')
                                    ->where('connections_user.user_id', auth()->user()->id)
                                    ->where('connections_user.status', 'connected')
                                    ->count();
        $pendingSendRequestCount = User::join('connections_user', 'users.id', '=', 'connections_user.user_id')
                                            ->where('connections_user.user_id', auth()->user()->id)
                                            ->where('connections_user.status', 'pending')
                                            ->count();
        $pendingReceivedConnectionsCount = DB::table('connections_user')
                                            ->where('connection_with', auth()->user()->id)                                    
                                            ->whereStatus('pending')->count();

        return view('home', compact('connectedUsersCount', 'pendingSendRequestCount', 'pendingReceivedConnectionsCount', 'suggestedUsersCount'));
    }

    public function getSuggestions(Request $request)
    {
        $limit = $request->limit;
        $skip = $request->skip;
        $suggestedUsers = $this->suggestedUsers($limit, $skip);
        return json_encode([
            'count' => count($suggestedUsers),
            'data' => view('components.suggestion', ['suggestions' => $suggestedUsers])->render()
        ]);
    }

    private function suggestedUsers($limit, $skip)
    {
        $userId = auth()->user()->id;
        $sentRequestUserIds = User::join('connections_user', 'users.id', '=', 'connections_user.user_id')
                                    ->where('users.id', $userId)
                                    ->where('connections_user.status', 'pending')
                                    ->pluck('connections_user.connection_with');
        $receivedRequestUserIds = DB::table('connections_user')
                                    ->where('connection_with', $userId)
                                    ->where('connections_user.status', 'pending')
                                    ->pluck('user_id');
        $userIds = Arr::collapse([$sentRequestUserIds, $receivedRequestUserIds]);
        return User::select('id', 'name', 'email')->where('id', '!=', $userId)->whereNotIn('id', $userIds)->limit($limit)->offset($skip)->get();
    }

    public function suggestedUsersCount()
    {
        $pendingUsers = User::join('connections_user', 'users.id', '=', 'connections_user.user_id')
                            ->where('connections_user.user_id', auth()->user()->id)
                            ->where('connections_user.status', 'pending')
                            ->pluck('connections_user.connection_with');

        $connectedUsers = User::join('connections_user', 'users.id', '=', 'connections_user.user_id')
                            ->where('connections_user.user_id', auth()->user()->id)
                            ->where('connections_user.status', 'connected')
                            ->pluck('connections_user.connection_with');
        $usersCount = Arr::collapse([$pendingUsers, $connectedUsers]);
        return User::where('id', '!=', $usersCount)->whereNotIn('id', $usersCount)->count();
    }

    public function getConnections(Request $request)
    {
        $limit = $request->limit;
        $skip = $request->skip;
        $connectedUsers = $this->connectedUsers(auth()->user()->id, $limit, $skip);
        foreach($connectedUsers as $user) {
            $connectionConnectedUsersCount = $this->connectionConnectedUsersCount($user->id, auth()->user()->id);
            $user->commonUsers = $connectionConnectedUsersCount;
        }
        return json_encode([
            'count' => count($connectedUsers),
            'data' => view('components.connection', ['connected' => $connectedUsers])->render()
        ]);
    }

    public function getRequest(Request $request)
    {
        $limit = $request->limit;
        $skip = $request->skip;
        $mode = $request->mode;
        $user = auth()->user();
        switch($mode) {
            case 'sent':
                $pendingSentConnections = $user->pendingSentConnections;
                return json_encode([
                    'count' => count($pendingSentConnections),
                    'data' => view('components.request', ['requests' => $pendingSentConnections, 'mode' => $mode])->render()
                ]);
                break;
            case 'received':
                $pendingReceivedConnections =   $user->pendingReceivedConnections;
                return json_encode([
                    'count' => $pendingReceivedConnections,
                    'data' => view('components.request', ['requests' => $pendingReceivedConnections, 'mode' => $mode])->render()
                ]);
                break;
            default:
                return view('components.request', ['requests' => []])->render();
        }
    }

    public function getConnectionsInCommon(Request $request)
    {
        return $request;
    }

    public function sendConnectionRequest(Request $request)
    {
        
        DB::table('connections_user')->insert([
            'user_id' => auth()->user()->id,
            'connection_with' => $request->connectionId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json([
            'status' => 1,
            'message' => 'Connection Request sent!'
        ]);
    }

    public function deleteConnectionRequest(Request $request)
    {
        
        $result = DB::table('connections_user')->where('user_id', auth()->user()->id)->where('connection_with', $request->connectionId)->delete();
        if($result) {
            return response()->json([
                'status' => 1,
                'message' => 'Connection Request withdrawed!'
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Error: Something went wrong!'
            ]);
        }
    }

    public function removeConnectionRequest(Request $request)
    {
        $userId = auth()->user()->id;
        $connectionId = $request->connectionId;
        $result = DB::table('connections_user')->where(function($q) use ($userId, $connectionId) {
            $q->where('user_id', $userId)->where('connection_with', $connectionId);
        })->orWhere(function($q) use ($userId, $connectionId) {
            $q->where('user_id', $connectionId)->where('connection_with', $userId);
        })->delete();
        if($result) {
            return response()->json([
                'status' => 1,
                'message' => 'Connection removed!'
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Error: Something went wrong!'
            ]);
        }
    }

    public function acceptConnectionRequest(Request $request)
    {
        
        $result = DB::table('connections_user')->where('user_id', $request->connectionId)->where('connection_with', auth()->user()->id)->update(['status' => 'connected']);
        if($result) {
            return response()->json([
                'status' => 1,
                'message' => 'Connection Request accepted!'
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Error: Something went wrong!'
            ]);
        }
    }

    private function connectedUsers($userId, $limit = null, $skip = null)
    {
        $forwardConnections = DB::table('connections_user')->where('user_id', $userId)->whereStatus('connected')->pluck('connection_with');
        $reverseConnections = DB::table('connections_user')->where('connection_with', $userId)->whereStatus('connected')->pluck('user_id');
        $userIds = Arr::collapse([$forwardConnections, $reverseConnections]);
        return User::select('id', 'name', 'email')->where('id', '!=', $userId)->whereIn('id', $userIds)->limit($limit)->offset($skip)->get();
    }
    private function connectionConnectedUsersCount($userId, $connectionId)
    {
        $forwardConnections = DB::table('connections_user')->where('user_id', $userId)->whereStatus('connected')->pluck('connection_with');
        $reverseConnections = DB::table('connections_user')->where('connection_with', $userId)->whereStatus('connected')->pluck('user_id');
        $userIds = Arr::collapse([$forwardConnections, $reverseConnections]);
        $users = User::select('id', 'name', 'email')->where('id', '!=', $userId)->where('id', '!=', $connectionId)->whereIn('id', $userIds);
        return $users->count();
    }
    public function connectedUsersCount()
    {
        $userId = auth()->user()->id;
        return DB::table('connections_user')->whereStatus('connected')->where(function($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('connection_with', $userId);
        })->count();
    }

}
