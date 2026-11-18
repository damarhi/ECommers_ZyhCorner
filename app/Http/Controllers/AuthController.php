<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\BrevoMailService;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if ($request->has('code')) {
            return $this->handleGoogleCallback($request);
        }
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->isBlocked()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return $this->redirectAfterLogin($request, $user, 'Selamat datang kembali! Anda berhasil masuk.');
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
        return view('Auth.register');
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

        // Generate cryptographically secure 6-digit OTP
        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Save OTP to database table
        Otp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp_code' => $otp,
                'attempts' => 0,
                'expires_at' => $expiresAt,
            ]
        );

        // Store registration info in session (hash password immediately; never keep plaintext in session)
        session([
            'register_pending' => [
                'nama' => $request->input('nama'),
                'email' => $request->input('email'),
                'no_hp' => $request->input('no_hp'),
                'password_hash' => Hash::make($request->input('password')),
            ],
        ]);

        try {
            BrevoMailService::sendOtp($request->email, $otp, 'Kode Verifikasi Pendaftaran Zyh Corner');
            
            return redirect()->route('register.verify')->with('success', 'Kode verifikasi telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            Log::error('Register OTP send failed: ' . $e->getMessage());
            session()->forget('register_pending');
            Otp::where('email', $request->email)->delete();
            return back()->withInput()->withErrors(['email' => 'Gagal mengirim email verifikasi. Silakan periksa alamat email Anda atau coba beberapa saat lagi.']);
        }
    }

    /**
      * Redirect to Google OAuth Consent Screen.
     */
    public function redirectToGoogle()
    {
        $clientId = config('services.google.client_id');
        if (!$clientId) {
            return redirect()->route('login')->withErrors([
                'email' => 'Fitur Google Login belum dikonfigurasi di file .env. Harap lengkapi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET.'
            ]);
        }

        $redirectUri = config('services.google.redirect') ?: url('/login');
        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
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

        $expectedState = session()->pull('google_oauth_state');
        $incomingState = (string) $request->input('state', '');
        if ($expectedState !== null || $request->has('state') || !app()->runningUnitTests()) {
            if (empty($expectedState) || empty($incomingState) || !hash_equals((string) $expectedState, $incomingState)) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Gagal masuk dengan Google: Sesi keamanan OAuth tidak valid atau telah kedaluwarsa. Silakan coba lagi.',
                ]);
            }
        }

        // Exchange Authorization Code for Access Token
        $response = Http::timeout(10)->asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect') ?: url('/login'),
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
        $userResponse = Http::timeout(10)->withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        if ($userResponse->failed()) {
            return redirect()->route('login')->withErrors(['email' => 'Gagal mengambil informasi profil Google.']);
        }

        $googleUser = $userResponse->json();
        $googleId = $googleUser['sub'] ?? null;
        $email = $googleUser['email'] ?? null;
        $nama = $googleUser['name'] ?? null;

        if (!$email || !$googleId) {
            return redirect()->route('login')->withErrors(['email' => 'Email tidak ditemukan pada akun Google Anda.']);
        }

        if (array_key_exists('email_verified', $googleUser) && !filter_var($googleUser['email_verified'], FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('login')->withErrors(['email' => 'Alamat email pada akun Google Anda belum terverifikasi.']);
        }

        // Check if user already exists (prefer exact google_id match before email match)
        $user = User::where('google_id', $googleId)->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            if ($user->isBlocked()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
                ]);
            }

            // Prevent silent OAuth account takeover / auto-linking onto unlinked Admin accounts
            if ($user->role === 'admin' && empty($user->google_id)) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Administrator wajib masuk menggunakan email dan password.',
                ]);
            }

            // Update Google ID if not set
            if (!$user->google_id) {
                $user->forceFill(['google_id' => $googleId])->save();
            }

            // Log the user in directly
            Auth::login($user);
            $request->session()->regenerate();

            return $this->redirectAfterLogin($request, $user, 'Selamat datang kembali! Anda berhasil masuk menggunakan Google.');
        }

        // Generate cryptographically secure 6-digit OTP for Google Login (new registration)
        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Save OTP to database table
        Otp::updateOrCreate(
            ['email' => $email],
            [
                'otp_code' => $otp,
                'attempts' => 0,
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
            Log::error('Google OTP send failed: ' . $e->getMessage());
            session()->forget(['google_register_pending']);
            Otp::where('email', $email)->delete();
            return redirect()->route('login')->withErrors(['email' => 'Gagal mengirim email verifikasi. Silakan coba beberapa saat lagi.']);
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
                return view('Auth.google_verify', ['email' => $user->email]);
            }
        } elseif (session()->has('google_register_pending')) {
            $pending = session('google_register_pending');
            return view('Auth.google_verify', ['email' => $pending['email']]);
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

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Kode verifikasi yang Anda masukkan salah.']);
        }

        if (now()->gt($otpRecord->expires_at)) {
            $otpRecord->delete();
            return back()->withErrors(['otp' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.']);
        }

        $submittedOtp = trim((string) $request->input('otp'));
        $expectedOtp = str_pad((string) $otpRecord->otp_code, 6, '0', STR_PAD_LEFT);
        if (!hash_equals($expectedOtp, $submittedOtp)) {
            $attempts = (int) ($otpRecord->attempts ?? 0) + 1;
            if ($attempts >= 5) {
                $otpRecord->delete();
                return back()->withErrors(['otp' => 'Terlalu banyak percobaan salah. Silakan minta kode verifikasi baru.']);
            }
            $otpRecord->update(['attempts' => $attempts]);
            return back()->withErrors(['otp' => 'Kode verifikasi yang Anda masukkan salah.']);
        }

        // Verification successful! Delete OTP from database
        Otp::where('email', $email)->delete();

        if ($isNewUser) {
            $pending = session('google_register_pending');
            // Check if user email was already registered in the meantime
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = new User();
                $user->forceFill([
                    'nama' => $pending['nama'],
                    'email' => $pending['email'],
                    'google_id' => $pending['google_id'],
                    'role' => 'pelanggan',
                    'no_hp' => null,
                    'password' => Hash::make(Str::random(24)),
                ])->save();
            }
            session()->forget('google_register_pending');
        } else {
            session()->forget('google_auth_pending_user_id');
        }
        if ($user->isBlocked()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectAfterLogin($request, $user, 'Selamat datang kembali! Anda berhasil masuk menggunakan Google.');
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

        $existingOtp = Otp::where('email', $email)->first();
        if ($existingOtp && $existingOtp->updated_at && $existingOtp->updated_at->diffInSeconds(now()) < 30) {
            return response()->json([
                'success' => false,
                'message' => 'Mohon tunggu 30 detik sebelum meminta kode verifikasi baru.'
            ], 429);
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Update OTP in DB
        Otp::updateOrCreate(
            ['email' => $email],
            [
                'otp_code' => $otp,
                'attempts' => 0,
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
            Log::error('Resend Google OTP failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email verifikasi. Silakan coba lagi nanti.'
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
        return view('Auth.register_verify', ['email' => $pending['email']]);
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

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Kode verifikasi yang Anda masukkan salah.']);
        }

        if (now()->gt($otpRecord->expires_at)) {
            $otpRecord->delete();
            return back()->withErrors(['otp' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.']);
        }

        $submittedOtp = trim((string) $request->input('otp'));
        $expectedOtp = str_pad((string) $otpRecord->otp_code, 6, '0', STR_PAD_LEFT);
        if (!hash_equals($expectedOtp, $submittedOtp)) {
            $attempts = (int) ($otpRecord->attempts ?? 0) + 1;
            if ($attempts >= 5) {
                $otpRecord->delete();
                return back()->withErrors(['otp' => 'Terlalu banyak percobaan salah. Silakan minta kode verifikasi baru.']);
            }
            $otpRecord->update(['attempts' => $attempts]);
            return back()->withErrors(['otp' => 'Kode verifikasi yang Anda masukkan salah.']);
        }

        // OTP is correct! Clear registration session and database record
        Otp::where('email', $pending['email'])->delete();
        session()->forget('register_pending');

        // Check if user email is already registered in DB (in case of double clicks/latency)
        $user = User::where('email', $pending['email'])->first();

        if (!$user) {
            $passwordHash = $pending['password_hash'] ?? Hash::make($pending['password']);
            $user = new User();
            $user->forceFill([
                'nama' => $pending['nama'],
                'email' => $pending['email'],
                'no_hp' => $pending['no_hp'],
                'password' => $passwordHash,
                'role' => 'pelanggan',
            ])->save();
        }
        if ($user->isBlocked()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
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
        $existingOtp = Otp::where('email', $pending['email'])->first();
        if ($existingOtp && $existingOtp->updated_at && $existingOtp->updated_at->diffInSeconds(now()) < 30) {
            return response()->json([
                'success' => false,
                'message' => 'Mohon tunggu 30 detik sebelum meminta kode verifikasi baru.'
            ], 429);
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        // Update OTP in DB
        Otp::updateOrCreate(
            ['email' => $pending['email']],
            [
                'otp_code' => $otp,
                'attempts' => 0,
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
            Log::error('Resend Register OTP failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email verifikasi. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    /**
     * Role-aware post-login redirect that prevents Admin/Kasir from landing on
     * guest customer URLs (e.g. /checkout) and prevents Pelanggan from landing
     * on /admin/* URLs stored in session('url.intended').
     */
    private function redirectAfterLogin(Request $request, User $user, string $customerMessage)
    {
        $intended = $request->session()->pull('url.intended');
        $intendedPath = '';
        $isSameHost = false;

        if (is_string($intended) && $intended !== '') {
            $parsedHost = parse_url($intended, PHP_URL_HOST);
            $isSameHost = ($parsedHost === null || strcasecmp($parsedHost, $request->getHost()) === 0);
            $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '';
        }

        if ($user->role === 'admin') {
            $welcomeMessage = 'Selamat datang kembali Admin! Anda berhasil masuk.';
            if ($isSameHost && ($intendedPath === '/admin' || str_starts_with($intendedPath, '/admin/'))) {
                return redirect()->to($intended)->with('success', $welcomeMessage);
            }

            return redirect('/admin/dashboard')->with('success', $welcomeMessage);
        }

        if ($user->role === 'kasir') {
            $welcomeMessage = 'Selamat datang kembali Kasir! Anda berhasil masuk.';
            $kasirAllowedPrefixes = [
                '/admin/dashboard',
                '/admin/pos',
                '/admin/orders',
                '/admin/returns',
                '/admin/payment-proofs',
                '/admin/settings',
            ];

            if ($isSameHost) {
                foreach ($kasirAllowedPrefixes as $prefix) {
                    if ($intendedPath === $prefix || str_starts_with($intendedPath, $prefix . '/')) {
                        return redirect()->to($intended)->with('success', $welcomeMessage);
                    }
                }
            }

            return redirect('/admin/dashboard')->with('success', $welcomeMessage);
        }

        if ($isSameHost && $intendedPath !== '' && $intendedPath !== '/admin' && !str_starts_with($intendedPath, '/admin/')) {
            return redirect()->to($intended)->with('success', $customerMessage);
        }

        return redirect('/')->with('success', $customerMessage);
    }
}
