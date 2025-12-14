<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends Controller
{
    /**
     * Mostrar el aviso de verificación de correo electrónico.
     */
    public function show()
    {
        return view('auth.verify-email');
    }

    /**
     * Manejar la solicitud de verificación de correo electrónico.
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->route('home')->with('verified', true);
    }

    /**
     * Reenviar el correo electrónico de verificación.
     */
    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Enlace de verificación enviado!');
    }
}
