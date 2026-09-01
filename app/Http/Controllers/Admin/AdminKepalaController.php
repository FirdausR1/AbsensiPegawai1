<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminKepalaController extends Controller
{
    public function index()
    {
        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: auth()->user();

        return view('admin.kepala.index', compact('kepala'));
    }

    public function updateSignature(Request $request)
    {
        $request->validate([
            'nama'           => ['required', 'string', 'max:255'],
            'jabatan_kepala' => ['required', 'string', 'max:255'],
            'signature_data' => ['nullable', 'string'],
            'foto_ttd'       => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: auth()->user();

        $signaturePath = $kepala->signature_path;

        // Process Base64 signature from canvas
        if ($request->filled('signature_data')) {
            $dataUri = $request->signature_data;
            if (preg_match('/^data:image\/(\w+);base64,/', $dataUri, $type)) {
                $data = substr($dataUri, strpos($dataUri, ',') + 1);
                $data = base64_decode($data);
                if ($data !== false) {
                    $filename = 'signatures/kepala_isw_' . time() . '.png';
                    Storage::disk('public')->put($filename, $data);
                    $signaturePath = $filename;
                }
            }
        } elseif ($request->hasFile('foto_ttd')) {
            $signaturePath = $request->file('foto_ttd')->store('signatures', 'public');
        }

        // Generate Digital Verification Payload & QR Code API URL
        $timestamp = now()->translatedFormat('d F Y H:i:s \W\I\B');
        $validationCode = 'ISW-DIR-VERIFIED-' . strtoupper(substr(md5($kepala->id . time()), 0, 10));

        $qrPayloadText = implode("\n", [
            "VERIFIKASI DIGITAL TANDA TANGAN RESMI",
            "PT INTI SARANA WIJAYA (ISW)",
            "----------------------------------------",
            "Nama     : " . $request->nama,
            "Jabatan  : " . $request->jabatan_kepala,
            "Kode Ver : " . $validationCode,
            "Waktu    : " . $timestamp,
            "Status   : DITANDATANGANI & SAH SECARA HUKUM",
        ]);

        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrPayloadText);

        $kepala->update([
            'nama'                 => $request->nama,
            'jabatan_kepala'       => $request->jabatan_kepala,
            'signature_path'       => $signaturePath,
            'qr_payload'           => $qrPayloadText,
            'qr_signature_path'    => $qrUrl,
            'signature_updated_at' => now(),
        ]);

        return back()->with('success', "Tanda Tangan Digital & QR Code Resmi Kepala ISW ({$request->nama}) berhasil disimpan dan diverifikasi!");
    }
}
