<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'pertanyaan' => 'Bagaimana cara melakukan pemesanan kue di Zyh Corner?',
                'jawaban' => 'Anda dapat memesan produk langsung melalui menu Shop pada website ini, atau memilih Custom Products jika ingin membuat pesanan kue dengan desain khusus. Setelah memilih produk, silakan lanjutkan ke halaman Checkout.',
                'kategori' => 'Pemesanan',
                'urutan' => 1,
            ],
            [
                'pertanyaan' => 'Berapa lama estimasi waktu pengiriman pesanan?',
                'jawaban' => 'Untuk produk reguler/showcase, pengiriman dilakukan pada hari yang sama (Same Day) atau sesuai jadwal pengiriman yang Anda pilih. Untuk produk kustom, waktu pengerjaan biasanya membutuhkan 1-3 hari kerja.',
                'kategori' => 'Pengiriman',
                'urutan' => 2,
            ],
            [
                'pertanyaan' => 'Metode pembayaran apa saja yang didukung?',
                'jawaban' => 'Kami mendukung berbagai metode pembayaran online aman via Midtrans seperti Transfer Bank (BCA, Mandiri, BNI, BRI), QRIS, E-Wallet (GoPay, ShopeePay), serta pembayaran tunai langsung di kasir toko.',
                'kategori' => 'Pembayaran',
                'urutan' => 3,
            ],
            [
                'pertanyaan' => 'Apakah kue dan hidangan di Zyh Corner menggunakan bahan halal dan segar?',
                'jawaban' => 'Ya! Seluruh produk buatan Zyh Corner 100% menggunakan bahan-bahan halal berkualitas tinggi, higienis, dan tanpa bahan pengawet buatan sehingga aman dikonsumsi seluruh keluarga.',
                'kategori' => 'Produk',
                'urutan' => 4,
            ],
            [
                'pertanyaan' => 'Bagaimana jika pesanan kue yang diterima rusak atau tidak sesuai?',
                'jawaban' => 'Kami menyediakan garansi pengantian/retur. Silakan ajukan klaim retur melalui menu Riwayat Pesanan pada akun Anda atau hubungi layanan pelanggan kami dengan menyertakan foto/video pembukaan paket.',
                'kategori' => 'Pengiriman',
                'urutan' => 5,
            ],
            [
                'pertanyaan' => 'Apakah bisa melakukan pemesanan untuk acara besar atau catering ulang tahun?',
                'jawaban' => 'Tentu bisa! Kami melayani pemesanan skala besar untuk acara pernikahan, ulang tahun, gathering kantor, dan perayaan lainnya. Silakan hubungi tim kami melalui tombol WhatsApp pada halaman Contact Us.',
                'kategori' => 'Pemesanan',
                'urutan' => 6,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(['pertanyaan' => $faq['pertanyaan']], $faq);
        }
    }
}
