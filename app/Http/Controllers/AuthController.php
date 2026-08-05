<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Services\BrevoMailService;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if ($request->has('code')) {
            return $this->handleGoogleCallback($request);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin/dashboard')->with('success', 'Selamat datang kembali Admin! Anda berhasil masuk.');
            } elseif (Auth::user()->role === 'kasir') {
                return redirect()->intended('/admin/dashboard')->with('success', 'Selamat datang kembali Kasir! Anda berhasil masuk.');
            }

            return redirect()->intended('/')->with('success', 'Selamat datang kembali! Anda berhasil masuk.');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar dari akun.');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function registerPost(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'no_hp' => ['required', 'numeric', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.numeric' => 'Nomor HP harus berupa angka.',
            'no_hp.unique' => 'Nomor HP sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Generate 6-digit OTP
        $otp = mt_rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Save OTP to database table
        Otp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp_code' => $otp,
                'expires_at' => $expiresAt,
            ]
        );

        // Store registration info in session
        session([
            'register_pending' => $request->only(['nama', 'email', 'no_hp', 'password']),
        ]);

        try {
            BrevoMailService::sendOtp($request->email, $otp, 'Kode Verifikasi Pendaftaran Zyh Corner');
            
            return redirect()->route('register.verify')->with('success', 'Kode verifikasi telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            session()->forget('register_pending');
            Otp::where('email', $request->email)->delete();
            return back()->withInput()->withErrors(['email' => 'Gagal mengirim email verifikasi: ' . $e->getMessage()]);
        }
    }

    /**
      * Redirect to Google OAuth Consent Screen.
     */
    public function redirectToGoogle()
    {
        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect', url('/login')),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => csrf_token(),
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    /**
     * Handle Callback from Google OAuth.
     */
    public function handleGoogleCallback(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('login')->withErrors(['email' => 'Gagal masuk dengan Google: Kode otorisasi tidak ditemukan.']);
        }

        // Exchange Authorization Code for Access Token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect', url('/login')),
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            return redirect()->route('login')->withErrors(['email' => 'Gagal menukarkan kode akses Google.']);
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            return redirect()->route('login')->withErrors(['email' => 'Token akses Google tidak valid.']);
        }

        // Fetch User Info using the Access Token
        $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        if ($userResponse->failed()) {
            return redirect()->route('login')->withErrors(['email' => 'Gagal mengambil informasi profil Google.']);
        }

        $googleUser = $userResponse->json();
        $googleId = $googleUser['sub'] ?? null;
        $email = $googleUser['email'] ?? null;
        $nama = $googleUser['name'] ?? null;

        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Email tidak ditemukan pada akun Google Anda.']);
        }

        // Check if user already exists
        $user = User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            // Update Google ID if not set
            if (!$user->google_id) {
                $user->update(['google_id' => $googleId]);
            }
            
            // Log the user in directly
            Auth::login($user);
            $request->session()->regenerate();

            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin/dashboard')->with('success', 'Selamat datang kembali Admin! Anda berhasil masuk.');
            }

            return redirect()->intended('/')->with('success', 'Selamat datang kembali! Anda berhasil masuk menggunakan Google.');
        }

        // Generate 6-digit OTP for Google Login (new registration)
        $otp = mt_rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Save OTP to database table
        Otp::updateOrCreate(
            ['email' => $email],
            [
                'otp_code' => $otp,
                'expires_at' => $expiresAt,
            ]
        );

        // New user registering via Google OAuth
        session([
            'google_register_pending' => [
                'nama' => $nama ?? explode('@', $email)[0],
                'email' => $email,
                'google_id' => $googleId,
            ]
        ]);

        try {
            BrevoMailService::sendOtp($email, $otp);
            return redirect()->route('login.google.verify')->with('success', 'Kode verifikasi OTP telah dikirim ke Gmail Anda.');
        } catch (\Exception $e) {
            // If mail failed, clear session and redirect back with error
            session()->forget(['google_register_pending']);
            Otp::where('email', $email)->delete();
            return redirect()->route('login')->withErrors(['email' => 'Gagal mengirim email verifikasi: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the Google OTP Verification Form.
     */
    public function showGoogleVerifyForm()
    {
        if (session()->has('google_auth_pending_user_id')) {
            $userId = session('google_auth_pending_user_id');
            $user = User::find($userId);
            if ($user) {
                return view('auth.google_verify', ['email' => $user->email]);
            }
        } elseif (session()->has('google_register_pending')) {
            $pending = session('google_register_pending');
            return view('auth.google_verify', ['email' => $pending['email']]);
        }

        return redirect()->route('login')->withErrors(['email' => 'Silakan masuk dengan Google terlebih dahulu.']);
    }

    /**
     * Verify Google OTP and authenticate the user.
     */
    public function verifyGoogleOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'numeric', 'digits:6'],
        ], [
            'otp.required' => 'Kode verifikasi wajib diisi.',
            'otp.numeric' => 'Kode verifikasi harus berupa angka.',
            'otp.digits' => 'Kode verifikasi harus terdiri dari 6 angka.',
        ]);

        $email = null;
        $user = null;
        $isNewUser = false;

        if (session()->has('google_auth_pending_user_id')) {
            $userId = session('google_auth_pending_user_id');
            $user = User::find($userId);
            if ($user) {
                $email = $user->email;
            }
        } elseif (session()->has('google_register_pending')) {
            $pending = session('google_register_pending');
            $email = $pending['email'];
            $isNewUser = true;
        }

        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi Google verifikasi Anda telah kedaluwarsa. Silakan masuk kembali.']);
        }

        // Check OTP from DB
        $otpRecord = Otp::where('email', $email)->first();

        if (!$otpRecord || (int)$request->otp !== (int)$otpRecord->otp_code) {
            return back()->withErrors(['otp' => 'Kode verifikasi yang Anda masukkan salah.']);
        }

        if (now()->gt($otpRecord->expires_at)) {
            return back()->withErrors(['otp' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.']);
        }

        // Verification successful! Delete OTP from database
        Otp::where('email', $email)->delete();

        if ($isNewUser) {
            $pending = session('google_register_pending');
            // Check if user email was already registered in the meantime
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'nama' => $pending['nama'],
                    'email' => $pending['email'],
                    'google_id' => $pending['google_id'],
                    'role' => 'pelanggan',
                    'no_hp' => null,
                    'password' => Hash::make(Str::random(16)),
                ]);
            }
            session()->forget('google_register_pending');
        } else {
            session()->forget('google_auth_pending_user_id');
        }

        Auth::login($user);
        $request->session()->regenerate();

        if (Auth::user()->role === 'admin') {
            return redirect()->intended('/admin/dashboard')->with('success', 'Selamat datang kembali Admin! Anda berhasil masuk.');
        } elseif (Auth::user()->role === 'kasir') {
            return redirect()->intended('/admin/dashboard')->with('success', 'Selamat datang kembali Kasir! Anda berhasil masuk.');
        }

        return redirect()->intended('/')->with('success', 'Selamat datang kembali! Anda berhasil masuk menggunakan Google.');
    }

    /**
     * Resend Google OTP code.
     */
    public function resendGoogleOtp(Request $request)
    {
        $email = null;

        if (session()->has('google_auth_pending_user_id')) {
            $userId = session('google_auth_pending_user_id');
            $user = User::find($userId);
            if ($user) {
                $email = $user->email;
            }
        } elseif (session()->has('google_register_pending')) {
            $pending = session('google_register_pending');
            $email = $pending['email'];
        }

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak ditemukan. Silakan login kembali.'
            ], 422);
        }

        $otp = mt_rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Update OTP in DB
        Otp::updateOrCreate(
            ['email' => $email],
            [
                'otp_code' => $otp,
                'expires_at' => $expiresAt,
            ]
        );

        try {
            BrevoMailService::sendOtp($email, $otp);
            
            return response()->json([
                'success' => true,
                'message' => 'Kode verifikasi baru telah dikirim ke ' . $email . '.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the Registration OTP Verification Form.
     */
    public function showRegisterVerifyForm()
    {
        if (!session()->has('register_pending')) {
            return redirect()->route('register')->withErrors(['email' => 'Silakan lengkapi formulir pendaftaran terlebih dahulu.']);
        }

        $pending = session('register_pending');
        return view('auth.register_verify', ['email' => $pending['email']]);
    }

    /**
     * Verify the Registration OTP and create the user account.
     */
    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'numeric', 'digits:6'],
        ], [
            'otp.required' => 'Kode verifikasi wajib diisi.',
            'otp.numeric' => 'Kode verifikasi harus berupa angka.',
            'otp.digits' => 'Kode verifikasi harus terdiri dari 6 angka.',
        ]);

        if (!session()->has('register_pending')) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi pendaftaran Anda telah kedaluwarsa. Silakan daftar kembali.']);
        }

        $pending = session('register_pending');

        // Check OTP from DB
        $otpRecord = Otp::where('email', $pending['email'])->first();

        if (!$otpRecord || (int)$request->otp !== (int)$otpRecord->otp_code) {
            return back()->withErrors(['otp' => 'Kode verifikasi yang Anda masukkan salah.']);
        }

        if (now()->gt($otpRecord->expires_at)) {
            return back()->withErrors(['otp' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.']);
        }

        // OTP is correct! Clear registration session and database record
        Otp::where('email', $pending['email'])->delete();
        session()->forget('register_pending');

        // Check if user email is already registered in DB (in case of double clicks/latency)
        $user = User::where('email', $pending['email'])->first();

        if (!$user) {
            $user = User::create([
                'nama' => $pending['nama'],
                'email' => $pending['email'],
                'no_hp' => $pending['no_hp'],
                'password' => Hash::make($pending['password']),
                'role' => 'pelanggan',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Registrasi berhasil! Selamat datang di Zyh Corner.');
    }

    /**
     * Resend Registration OTP code.
     */
    public function resendRegisterOtp(Request $request)
    {
        if (!session()->has('register_pending')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi pendaftaran tidak ditemukan. Silakan daftar kembali.'
            ], 422);
        }

        $pending = session('register_pending');
        $otp = mt_rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Update OTP in DB
        Otp::updateOrCreate(
            ['email' => $pending['email']],
            [
                'otp_code' => $otp,
                'expires_at' => $expiresAt,
            ]
        );

        try {
            BrevoMailService::sendOtp($pending['email'], $otp, 'Kode Verifikasi Pendaftaran Zyh Corner');
            
            return response()->json([
                'success' => true,
                'message' => 'Kode verifikasi baru telah dikirim ke ' . $pending['email'] . '.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }
}
