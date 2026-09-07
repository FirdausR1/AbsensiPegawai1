<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pegawai - PT Inti Sarana Wijaya</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #000d6b; padding-bottom: 10px; }
        .header h1 { font-size: 16px; font-weight: bold; color: #000d6b; }
        .header p { font-size: 10px; color: #555; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table th { background: #000d6b; color: #fff; padding: 6px 5px; text-align: left; font-weight: 600; font-size: 9px; white-space: nowrap; }
        table td { padding: 5px; border-bottom: 1px solid #ddd; vertical-align: top; }
        table tr:hover { background: #f5f5f5; }
        .btn-print { position: fixed; top: 15px; right: 15px; padding: 10px 20px; background: #000d6b; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; z-index: 100; }
        .btn-print:hover { background: #001253; }
        .footer { margin-top: 15px; text-align: right; font-size: 9px; color: #999; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 9px; font-weight: 600; }
        .badge-admin { background: #e0e7ff; color: #000d6b; }
        .badge-staff { background: #f1f5f9; color: #475569; }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print no-print">Cetak / Print</button>

    <div class="header">
        <h1>DATA PEGAWAI PT INTI SARANA WIJAYA</h1>
        <p>Dicetak pada: {{ now()->translatedFormat('l, d F Y - H:i') }} WIB | Total: {{ $pegawais->count() }} pegawai</p>
        @if(request('site'))
            <p>Filter Area Kerja: {{ request('site') }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>Email</th>
                <th>No. HP</th>
                <th>TTL</th>
                <th>JK</th>
                <th>Alamat</th>
                <th>Pendidikan</th>
                <th>Status</th>
                <th>Area Kerja</th>
                <th>Role</th>
                <th>Kontak Darurat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pegawais as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight:600; white-space:nowrap;">{{ $p->nama }}</td>
                    <td>{{ $p->nik ?: '-' }}</td>
                    <td>{{ $p->email }}</td>
                    <td>{{ $p->no_hp ?: '-' }}</td>
                    <td style="white-space:nowrap;">
                        {{ $p->tempat_lahir ?: '-' }}{{ $p->tanggal_lahir ? ', ' . $p->tanggal_lahir->format('d/m/Y') : '' }}
                    </td>
                    <td>{{ $p->jenis_kelamin ? substr($p->jenis_kelamin, 0, 1) : '-' }}</td>
                    <td style="max-width:180px;">{{ $p->alamat ?: '-' }}</td>
                    <td>{{ $p->pendidikan_terakhir ?: '-' }}</td>
                    <td>{{ $p->status_pernikahan ?: '-' }}</td>
                    <td>{{ $p->area_kerja ?: '-' }}</td>
                    <td>
                        @if($p->isSuperAdmin())
                            <span class="badge badge-admin">Admin</span>
                        @elseif($p->isDivisionAdmin())
                            <span class="badge badge-admin">Koord.</span>
                        @else
                            <span class="badge badge-staff">Staff</span>
                        @endif
                    </td>
                    <td>{{ $p->kontak_darurat ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} PT Inti Sarana Wijaya — ISW Attendance System
    </div>
</body>
</html>
