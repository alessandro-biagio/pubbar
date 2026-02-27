<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Form profilo utente (dati base)
     */
    public function edit(Request $request): View
    {
        // passo l'utente loggato alla view
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Salvataggio dati profilo (name, email, ecc.)
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // fill solo con campi validati dal FormRequest
        $request->user()->fill($request->validated());

        // se cambia email, reset verifica
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Eliminazione account (richiede password)
     */
    public function destroy(Request $request): RedirectResponse
    {
        // validazione separata (error bag dedicato)
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // logout prima di cancellare l'utente
        Auth::logout();

        $user->delete();

        // invalido sessione e token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
