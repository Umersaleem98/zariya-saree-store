<?php

namespace App\Http\Controllers;

use App\Models\ShopUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $r)
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect('/?auth_error=google_setup');
        }$r->session()->put('checkout_after_google', $r->query('next') === 'checkout');

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $r)
    {
        try {
            $g = Socialite::driver('google')->user();
            $claims = $g->user;
            if (empty($claims['email_verified']) && empty($claims['verified_email'])) {
                throw new \RuntimeException('Email must be verified.');
            }$email = strtolower($g->getEmail());
            $u = ShopUser::where('google_subject', $g->getId())->first() ?? ShopUser::where('email', $email)->first();
            if ($u && ($u->role === 'admin' || ($u->google_subject && $u->google_subject !== $g->getId()))) {
                throw new \RuntimeException('Use password login.');
            }if ($u && ! $u->google_subject && ! str_ends_with($email, '@gmail.com') && empty($claims['hd'])) {
                throw new \RuntimeException('Use password login.');
            }if (! $u) {
                $id = DB::table('users')->insertGetId(['name' => mb_substr($g->getName() ?: explode('@', $email)[0], 0, 100), 'email' => $email, 'password_hash' => Hash::make(bin2hex(random_bytes(32))), 'role' => 'customer', 'google_subject' => $g->getId(), 'created_at' => now(), 'updated_at' => now()]);
                $u = ShopUser::find($id);
            } elseif (! $u->google_subject) {
                $u->google_subject = $g->getId();
                $u->save();
            }Auth::login($u);
            $checkout = $r->session()->pull('checkout_after_google');
            $r->session()->regenerate();

            return redirect($checkout ? '/?checkout=1' : '/?signed_in=1');
        } catch (\Throwable $e) {
            report($e);

            return redirect('/?auth_error=google_failed');
        }
    }
}
