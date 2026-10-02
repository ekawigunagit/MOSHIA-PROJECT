<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title>Verifikasi email akun Moshia</title>
</head>
<body style="margin:0;padding:0;background-color:#09090c;color:#f6f5f7;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">Satu langkah lagi untuk memulai bersama Moshia. Verifikasi email Anda dalam {{ $expiresInMinutes }} menit.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#09090c" style="background-color:#09090c;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;">
                    <tr>
                        <td style="padding:0 8px 28px;">
                            <div style="font-size:28px;line-height:34px;font-weight:800;letter-spacing:2px;color:#f6f5f7;">MOSHIA<span style="color:#f02b35;">.</span></div>
                            <div style="margin-top:8px;font-size:12px;line-height:20px;color:#a2a0a7;">Better Together. Beyond Tomorrow.</div>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#111114" style="background-color:#111114;border:1px solid #29292d;border-top:4px solid #f02b35;border-radius:16px;padding:32px 24px;">
                            <p style="margin:0 0 16px;font-size:11px;line-height:18px;font-weight:700;letter-spacing:2px;color:#ff7a82;">SELAMAT DATANG DI MOSHIA</p>
                            <h1 style="margin:0 0 24px;font-size:30px;line-height:38px;font-weight:700;color:#f6f5f7;">Satu langkah lagi<br>untuk mulai bersama.</h1>
                            <p style="margin:0 0 12px;font-size:16px;line-height:26px;color:#f6f5f7;overflow-wrap:anywhere;">Halo, {{ $recipientName }}!</p>
                            <p style="margin:0 0 28px;font-size:15px;line-height:25px;color:#b9b7bf;">Terima kasih sudah bergabung dengan Moshia. Konfirmasikan alamat email Anda untuk membuka dashboard dan mulai menyiapkan workspace Anda.</p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" bgcolor="#f02b35" style="background-color:#f02b35;border-radius:28px;mso-padding-alt:16px 28px;">
                                        <a href="{{ $verificationUrl }}" style="display:inline-block;padding:16px 28px;border:1px solid #f02b35;border-radius:28px;font-size:16px;line-height:22px;font-weight:700;color:#ffffff;text-decoration:none;">Verifikasi email saya</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:18px 0 28px;font-size:13px;line-height:22px;color:#a2a0a7;">Tautan ini berlaku selama <strong style="color:#f6f5f7;">{{ $expiresInMinutes }} menit</strong>. Jika sudah kedaluwarsa, masuk ke Moshia untuk meminta tautan baru.</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr><td style="border-top:1px solid #29292d;padding-top:24px;">
                                    <p style="margin:0 0 10px;font-size:12px;line-height:20px;color:#a2a0a7;">Tombol tidak dapat dibuka? Salin tautan berikut ke browser Anda:</p>
                                    <p style="margin:0;font-size:12px;line-height:20px;word-break:break-all;overflow-wrap:anywhere;"><a href="{{ $verificationUrl }}" style="color:#ff7a82;text-decoration:underline;word-break:break-all;">{{ $verificationUrl }}</a></p>
                                </td></tr>
                            </table>
                            <p style="margin:28px 0 0;font-size:14px;line-height:23px;color:#f6f5f7;">Sampai bertemu di Moshia,<br><strong>Tim Moshia</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 8px 0;text-align:center;">
                            <p style="margin:0 0 10px;font-size:12px;line-height:20px;color:#a2a0a7;">Jika Anda tidak membuat akun Moshia, abaikan email ini.<br>Akun tetap belum terverifikasi tanpa tindakan Anda.</p>
                            <p style="margin:0;font-size:11px;line-height:18px;letter-spacing:1px;color:#a2a0a7;">MOSHIA &middot; BETTER TOGETHER.</p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
