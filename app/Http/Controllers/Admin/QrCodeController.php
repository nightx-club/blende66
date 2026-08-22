<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    public function show(Wedding $wedding): Response
    {
        return $this->render($wedding, false);
    }

    public function download(Wedding $wedding): Response
    {
        return $this->render($wedding, true);
    }

    private function render(Wedding $wedding, bool $download): Response
    {
        abort_unless($wedding->guest_token, 404);
        $qr = new QrCode(data: route('weddings.qr-access', $wedding->guest_token), size: 480, margin: 28);
        $result = (new PngWriter)->write($qr);
        $headers = ['Content-Type' => $result->getMimeType(), 'Cache-Control' => 'private, no-store'];
        if ($download) {
            $headers['Content-Disposition'] = 'attachment; filename="'.Str::slug($wedding->couple_names).'-qr-code.png"';
        }

        return response($result->getString(), 200, $headers);
    }
}
