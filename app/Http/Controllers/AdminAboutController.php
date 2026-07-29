<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AboutSetting;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Storage;

class AdminAboutController extends Controller
{
    /**
     * Display the About Us settings page for Admin.
     */
    public function index()
    {
        $about = AboutSetting::firstOrCreate(['id_about' => 1], [
            'judul_utama' => 'Tentang Zyh Corner',
            'subjudul_utama' => 'Menghadirkan Cita Rasa Terbaik & Kue Kualitas Premium Sejak 2020',
            'cerita_singkat' => 'Zyh Corner didirikan dengan cinta dan semangat untuk menyajikan berbagai hidangan kue, pastry, dessert, dan minuman lezat yang diolah dengan bahan-bahan berkualitas tinggi dan higienis. Kami percaya bahwa setiap perayaan dan momen berharga layak dirayakan dengan kelezatan istimewa.',
            'visi' => 'Menjadi toko pastry & bakery pilihan utama yang menghadirkan kebahagiaan manis di setiap momen istimewa keluarga dan pelanggan.',
            'misi' => "1. Menjaga kualitas rasa, kebersihan, dan kesegaran bahan baku terbaik.\n2. Memberikan layanan ramah dan profesional bagi setiap pelanggan.\n3. Berinovasi menciptakan kreasi kue dan dessert yang unik dan menarik.",
            'jam_operasional' => 'Senin - Minggu: 08.00 - 21.00 WIB',
            'alamat_toko' => 'Jl. Jendral Sudirman No. 123, Kota Samarinda, Kalimantan Timur',
            'telepon' => '081234567890',
            'email' => 'info@zyhcorner.com',
        ]);

        $teamMembers = TeamMember::orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();

        return view('Admin.kelola_carousel.about_settings', compact('about', 'teamMembers'));
    }

    /**
     * Update the About Us settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'judul_utama' => 'required|string|max:255',
            'subjudul_utama' => 'nullable|string|max:255',
            'gambar_tentang' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'cerita_singkat' => 'nullable|string',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
            'jam_operasional' => 'nullable|string|max:255',
            'alamat_toko' => 'nullable|string',
            'telepon' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'link_maps' => 'nullable|string',
            'foto_galeri_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'foto_galeri_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'foto_galeri_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'judul_fitur' => 'nullable|string|max:255',
            'deskripsi_fitur' => 'nullable|string',
            'judul_delivery' => 'nullable|string|max:255',
            'deskripsi_delivery' => 'nullable|string',
            'poin_delivery_1' => 'nullable|string|max:255',
            'poin_delivery_2' => 'nullable|string|max:255',
            'poin_delivery_3' => 'nullable|string|max:255',
            'judul_tim' => 'nullable|string|max:255',
            'deskripsi_tim' => 'nullable|string',
            'judul_testimoni' => 'nullable|string|max:255',
            'deskripsi_testimoni' => 'nullable|string',
        ]);

        $about = AboutSetting::firstOrCreate(['id_about' => 1]);

        $data = [
            'judul_utama' => $request->judul_utama,
            'subjudul_utama' => $request->subjudul_utama,
            'cerita_singkat' => $request->cerita_singkat,
            'judul_fitur' => $request->judul_fitur,
            'deskripsi_fitur' => $request->deskripsi_fitur,
            'judul_delivery' => $request->judul_delivery,
            'deskripsi_delivery' => $request->deskripsi_delivery,
            'poin_delivery_1' => $request->poin_delivery_1,
            'poin_delivery_2' => $request->poin_delivery_2,
            'poin_delivery_3' => $request->poin_delivery_3,
            'judul_tim' => $request->judul_tim,
            'deskripsi_tim' => $request->deskripsi_tim,
            'judul_testimoni' => $request->judul_testimoni,
            'deskripsi_testimoni' => $request->deskripsi_testimoni,
        ];

        if ($request->has('visi')) $data['visi'] = $request->visi;
        if ($request->has('misi')) $data['misi'] = $request->misi;
        if ($request->has('jam_operasional')) $data['jam_operasional'] = $request->jam_operasional;
        if ($request->has('alamat_toko')) $data['alamat_toko'] = $request->alamat_toko;
        if ($request->has('telepon')) $data['telepon'] = $request->telepon;
        if ($request->has('email')) $data['email'] = $request->email;
        if ($request->has('link_maps')) $data['link_maps'] = $request->link_maps;

        // Handle Main Image Upload
        if ($request->hasFile('gambar_tentang')) {
            if ($about->gambar_tentang && Storage::disk('public')->exists($about->gambar_tentang)) {
                Storage::disk('public')->delete($about->gambar_tentang);
            }
            $data['gambar_tentang'] = $request->file('gambar_tentang')->store('about', 'public');
        }

        // Handle Gallery Image 1 Upload
        if ($request->hasFile('foto_galeri_1')) {
            if ($about->foto_galeri_1 && Storage::disk('public')->exists($about->foto_galeri_1)) {
                Storage::disk('public')->delete($about->foto_galeri_1);
            }
            $data['foto_galeri_1'] = $request->file('foto_galeri_1')->store('about', 'public');
        }

        // Handle Gallery Image 2 Upload
        if ($request->hasFile('foto_galeri_2')) {
            if ($about->foto_galeri_2 && Storage::disk('public')->exists($about->foto_galeri_2)) {
                Storage::disk('public')->delete($about->foto_galeri_2);
            }
            $data['foto_galeri_2'] = $request->file('foto_galeri_2')->store('about', 'public');
        }

        // Handle Gallery Image 3 Upload
        if ($request->hasFile('foto_galeri_3')) {
            if ($about->foto_galeri_3 && Storage::disk('public')->exists($about->foto_galeri_3)) {
                Storage::disk('public')->delete($about->foto_galeri_3);
            }
            $data['foto_galeri_3'] = $request->file('foto_galeri_3')->store('about', 'public');
        }

        $about->update($data);

        return redirect()->back()->with('success', 'Pengaturan Halaman About Us berhasil diperbarui!');
    }

    /**
     * Store new Team Member.
     */
    public function storeTeam(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'urutan' => 'nullable|integer',
        ]);

        $data = [
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'urutan' => $request->urutan ?? 0,
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('team', 'public');
        }

        TeamMember::create($data);

        return redirect()->back()->with('success', 'Anggota tim baru berhasil ditambahkan!');
    }

    /**
     * Update Team Member.
     */
    public function updateTeam(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'urutan' => 'nullable|integer',
        ]);

        $member = TeamMember::findOrFail($id);

        $data = [
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'urutan' => $request->urutan ?? 0,
        ];

        if ($request->hasFile('foto')) {
            if ($member->foto && Storage::disk('public')->exists($member->foto)) {
                Storage::disk('public')->delete($member->foto);
            }
            $data['foto'] = $request->file('foto')->store('team', 'public');
        }

        $member->update($data);

        return redirect()->back()->with('success', 'Data anggota tim berhasil diperbarui!');
    }

    /**
     * Delete Team Member.
     */
    public function destroyTeam($id)
    {
        $member = TeamMember::findOrFail($id);

        if ($member->foto && Storage::disk('public')->exists($member->foto)) {
            Storage::disk('public')->delete($member->foto);
        }

        $member->delete();

        return redirect()->back()->with('success', 'Anggota tim berhasil dihapus!');
    }
}
