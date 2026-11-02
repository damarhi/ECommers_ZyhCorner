@extends('emails.layout')

@section('title', 'Pesan Kontak Baru Masuk')

@section('header-style', 'background: linear-gradient(135deg, #4f46e5, #4338ca);')
@section('header-title', '📬 Pesan Kontak Baru Masuk!')
@section('header-subtitle', 'Dasbor Notifikasi Admin — Zyh Corner')

@section('extra-styles')
<style>
    .message-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #4f46e5;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 28px;
    }
    .message-box-title {
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .message-box-content {
        font-size: 14px;
        line-height: 1.6;
        color: #1e293b;
        white-space: pre-line;
        margin: 0;
    }
</style>
@endsection

@section('content')
    <div class="badge-status" style="background: #fef3c7; color: #92400e;">Status: Belum Dibaca</div>
    
    <p style="font-size: 14px; line-height: 1.6; margin: 0 0 20px 0; color: #475569;">
        Halo Admin Zyh Corner, terdapat pesan kontak baru yang dikirimkan oleh pengunjung melalui formulir <strong>Hubungi Kami</strong>:
    </p>
    
    <table class="info-table">
        <tr>
            <td class="label">Nama Pengirim</td>
            <td class="value"><strong>{{ $messageData->nama }}</strong></td>
        </tr>
        <tr>
            <td class="label">Alamat Email</td>
            <td class="value">
                <a href="mailto:{{ $messageData->email }}" style="color: #4f46e5; text-decoration: none;">
                    {{ $messageData->email }}
                </a>
            </td>
        </tr>
        <tr>
            <td class="label">Waktu Kirim</td>
            <td class="value">
                {{ $messageData->created_at ? $messageData->created_at->format('d M Y, H:i') . ' WIB' : now()->format('d M Y, H:i') . ' WIB' }}
            </td>
        </tr>
        <tr>
            <td class="label">Subjek Pesan</td>
            <td class="value"><strong>{{ $messageData->subjek }}</strong></td>
        </tr>
    </table>

    <div class="message-box">
        <div class="message-box-title">Isi Pesan:</div>
        <p class="message-box-content">{{ $messageData->pesan }}</p>
    </div>

    <a href="{{ url('admin/messages') }}" class="btn-action" style="width: 220px; background: #4f46e5; color: #ffffff !important; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);">Buka Dasbor Admin</a>
@endsection

@section('footer')
    Email ini dikirim secara otomatis oleh Sistem Toko Zyh Corner saat formulir kontak disubmit.<br>
    Anda dapat membalas pesan ini langsung ke alamat email pengirim di atas.
@endsection
