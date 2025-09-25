<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessageController extends Controller
{
    // Afficher la messagerie
    public function index()
    {
        $authUser = auth()->user(); // utilisateur connecté

        // Tous les utilisateurs sauf celui connecté
        $users = User::where('id', '!=', $authUser->id)->get();

        // Tous les messages liés à l'utilisateur connecté
        $messages = Message::with(['sender', 'receiver'])
            ->where('sender_id', $authUser->id)
            ->orWhere('receiver_id', $authUser->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Messages/Index', [
            'users' => $users,
            'messages' => $messages,
            'auth' => ['user' => $authUser], // attention au nom : 'auth', pas 'Auth'
        ]);
    }


    // Envoyer un message
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
        ]);

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);

        return redirect()->back();
    }
}
