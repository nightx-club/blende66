<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home');
    }

    public function about(): View
    {
        return view('marketing.about');
    }

    public function portfolio(): View
    {
        return view('marketing.portfolio', [
            'portraits' => $this->images('portfolio'),
            'weddings' => $this->images('weddings'),
        ]);
    }

    public function shootings(): View
    {
        return view('marketing.shootings');
    }

    public function contact(): View
    {
        return view('marketing.contact');
    }

    public function imprint(): View
    {
        return view('marketing.imprint');
    }

    public function privacy(): View
    {
        return view('marketing.privacy');
    }

    /** @return array<int, string> */
    private function images(string $directory): array
    {
        $files = glob(public_path("images/blende6/{$directory}/*.jpg")) ?: [];
        sort($files, SORT_NATURAL);

        return array_map(fn (string $file) => asset("images/blende6/{$directory}/".basename($file)), $files);
    }
}
