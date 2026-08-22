<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Imagick;

class WeddingController extends Controller
{
    public function index(): View
    {
        $weddings = Wedding::withCount(['media as photo_count' => fn ($q) => $q->where('type', 'photo'), 'media as video_count' => fn ($q) => $q->where('type', 'video')])->withSum('media as storage_bytes', 'file_size')->latest()->get();

        return view('admin.weddings.index', compact('weddings'));
    }

    public function create(): View
    {
        return view('admin.weddings.form', ['wedding' => new Wedding]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['pin_hash'] = Hash::make($data['pin']);
        unset($data['pin']);
        $data['cover_image_path'] = $this->storeCover($request);
        $wedding = Wedding::create($data);

        return redirect()->route('admin.weddings.edit', $wedding)->with('success', 'Hochzeit / Event wurde angelegt.');
    }

    public function edit(Wedding $wedding): View
    {
        return view('admin.weddings.form', compact('wedding'));
    }

    public function update(Request $request, Wedding $wedding): RedirectResponse
    {
        $data = $this->validated($request, $wedding);
        if (! empty($data['pin'])) {
            $data['pin_hash'] = Hash::make($data['pin']);
        }
        unset($data['pin']);
        if ($request->hasFile('cover_image')) {
            Storage::disk('local')->delete($wedding->cover_image_path);
            $data['cover_image_path'] = $this->storeCover($request);
        }
        $wedding->update($data);

        return back()->with('success', 'Änderungen wurden gespeichert.');
    }

    private function validated(Request $request, ?Wedding $wedding = null): array
    {
        return $request->validate([
            'couple_names' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('weddings', 'slug')->ignore($wedding)],
            'wedding_date' => ['required', 'date'],
            'pin' => [$wedding ? 'nullable' : 'required', 'digits_between:4,10'],
            'welcome_text' => ['nullable', 'string', 'max:2000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:25600'],
            'is_active' => ['nullable', 'boolean'],
            'photo_max_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'photo_batch_max' => ['required', 'integer', 'min:1', 'max:20'],
            'video_max_mb' => ['required', 'integer', 'min:10', 'max:100'],
            'video_max_seconds' => ['required', 'integer', 'min:10', 'max:600'],
            'video_batch_max' => ['required', 'integer', 'min:1', 'max:5'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function storeCover(Request $request): ?string
    {
        if (! $request->hasFile('cover_image')) {
            return null;
        }
        $image = new Imagick($request->file('cover_image')->getRealPath());
        if (method_exists($image, 'autoOrient')) {
            $image->autoOrient();
        }
        $image->stripImage();
        $image->thumbnailImage(1800, 1000, true, true);
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality(82);
        $path = 'covers/'.Str::uuid().'.webp';
        Storage::disk('local')->put($path, $image->getImagesBlob());
        $image->clear();

        return $path;
    }
}
