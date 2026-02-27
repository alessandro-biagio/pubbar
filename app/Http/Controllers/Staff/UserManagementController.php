<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    /**
     * Lista utenti (staff panel) con ricerca base su nome/email/id
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q',''));

        // ordinamento: superuser in cima, poi staff, poi alfabetico
        $users = User::query()
            ->when($q !== '', function($qq) use ($q){
                $qq->where(function($w) use ($q){
                    $w->where('name','like',"%{$q}%")
                      ->orWhere('email','like',"%{$q}%")
                      ->orWhere('id',$q); // match rapido su id
                });
            })
            ->orderByDesc('is_superuser')
            ->orderByDesc('is_staff')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('staff.users.index', compact('users','q'));
    }

    /**
     * Toggle staff: abilita/disabilita accesso pannello staff
     */
    public function toggleStaff(Request $request, User $user)
    {
        $me = $request->user();

        // un superuser deve rimanere staff (coerenza ruoli)
        if ($user->is_superuser && $user->is_staff) {
            return back()->with('error', 'Un superuser è sempre staff. Rimuovi prima lo status superuser.');
        }

        // safety: non mi faccio fuori da solo mentre sono superuser
        if ($me->id === $user->id && $user->is_superuser) {
            return back()->with('error', 'Non puoi modificare lo staff sul tuo account mentre sei superuser.');
        }

        $user->is_staff = ! $user->is_staff;
        $user->save();

        return back()->with('success', "Utente {$user->name}: is_staff = ".($user->is_staff ? 'ON' : 'OFF'));
    }

    /**
     * Toggle superuser: abilita/disabilita permessi massimi (e forza staff=true quando ON)
     */
    public function toggleSuperuser(Request $request, User $user)
    {
        $me = $request->user();

        // se sto togliendo superuser a me stesso, devo lasciarne almeno un altro attivo
        if ($me->id === $user->id && $user->is_superuser) {
            $others = User::where('is_superuser', true)
                ->where('id','!=',$me->id)
                ->exists();

            if (!$others) {
                return back()->with('error','Non puoi rimuovere l’ultimo superuser.');
            }
        }

        $user->is_superuser = ! $user->is_superuser;

        // superuser => sempre anche staff
        if ($user->is_superuser) {
            $user->is_staff = true;
        }

        $user->save();

        return back()->with('success', "Utente {$user->name}: is_superuser = ".($user->is_superuser ? 'ON' : 'OFF'));
    }
}
