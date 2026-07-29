<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AboutSetting;

class AdminContactController extends Controller
{
    /**
     * Display the Contact Us settings page for Admin.
     */
    public function index()
    {
        $contact = AboutSetting::firstOrCreate(['id_about' => 1], [
            'alamat_toko' => 'Jl. Teropong, Sidomulyo Barat, Kec. Tampan, Kota Pekanbaru, Riau 28294',
            'email' => 'zyhcorner@gmail.com',
            'email_2' => 'support@zyhcorner.com',
            'telepon' => '(+62) 812-3456-789',
            'telepon_2' => '(+62) 811-7654-321',
            'judul_form_kontak' => 'Just Say Hello!',
            'deskripsi_form_kontak' => 'Apakah Anda ingin menyapa kami atau memesan kue khusus untuk hari bahagia Anda? Jangan ragu untuk mengirimkan pesan!',
            'link_maps' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7192150925703!2d101.85587509999999!3d0.40384089999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d5c5c32fbe821f%3A0xcc0417290c322aec!2sZyh%20Corner!5e0!3m2!1sen!2sid!4v1782150424180!5m2!1sen!2sid',
        ]);

        return view('Admin.kelola_carousel.contact_settings', compact('contact'));
    }

    /**
     * Update the Contact Us settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'alamat_toko' => 'required|string',
            'email' => 'required|email|max:150',
            'email_2' => 'nullable|email|max:150',
            'telepon' => 'required|string|max:100',
            'telepon_2' => 'nullable|string|max:100',
            'judul_form_kontak' => 'nullable|string|max:255',
            'deskripsi_form_kontak' => 'nullable|string',
            'link_maps' => 'nullable|string',
        ]);

        $contact = AboutSetting::firstOrCreate(['id_about' => 1]);

        $contact->update([
            'alamat_toko' => $request->alamat_toko,
            'email' => $request->email,
            'email_2' => $request->email_2,
            'telepon' => $request->telepon,
            'telepon_2' => $request->telepon_2,
            'judul_form_kontak' => $request->judul_form_kontak,
            'deskripsi_form_kontak' => $request->deskripsi_form_kontak,
            'link_maps' => $request->link_maps,
        ]);

        return redirect()->back()->with('success', 'Pengaturan Halaman Contact Us berhasil diperbarui!');
    }
}
