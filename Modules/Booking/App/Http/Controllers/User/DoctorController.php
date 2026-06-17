<?php

namespace Modules\Booking\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Booking\App\Models\DoctorMedia;
use Modules\Booking\App\Models\DoctorProfile;
use Nwidart\Modules\Facades\Module;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    use FileUploadTrait;
    /**
     * Show doctor profile page
     */
    public function show()
    {
        $user = auth()->user();

        $isBookingActive = Module::has('Booking')
            && Module::isEnabled('Booking')
            && Schema::hasTable('doctor_profiles');

        $profile = null;
        $photos  = collect();
        $videos  = collect();

        if ($isBookingActive && $user->hasRole('doctor')) {
            $profile = DoctorProfile::firstOrCreate(['user_id' => $user->id]);

            $photos = DoctorMedia::where('user_id', $user->id)
                ->where('type', 'photo')->latest()->get();

            $videos = DoctorMedia::where('user_id', $user->id)
                ->where('type', 'video')->latest()->get();
        }

        return view('profile.show', [
            'user'    => $user,
            'profile' => $profile,
            'photos'  => $photos,
            'videos'  => $videos,
        ]);
    }

    /**
     * Update doctor basic info + visibility (single source of truth)
     */
    public function updateAbout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'about_me'              => 'nullable|string|max:2000',
            'education'             => 'nullable|string|max:255',
            'experience'            => 'nullable|string|max:255',
            'clinic_name'           => 'nullable|string|max:255',
            'medical_system_number' => 'required|string|max:255',
            'specialty'             => 'nullable|string|max:255',
            'clinic_address'        => 'nullable|string|max:255',
        ]);

        $profile = DoctorProfile::firstOrCreate(['user_id' => auth()->id()]);
        $visibility = $profile->visibility ?? [];
        $checkboxMap = [
            'visibility_about_me'              => 'about_me',
            'visibility_about'                 => 'about_me',       // fallback alias
            'visibility_specialty'             => 'specialty',
            'visibility_clinic_name'           => 'clinic_name',
            'visibility_education'             => 'education',
            'visibility_medical_system_number' => 'medical_system_number',
            'visibility_insurances'            => 'insurances',
            'visibility_gallery'               => 'gallery',
            'visibility_video'                 => 'video',
        ];

        foreach ($checkboxMap as $formKey => $visKey) {
            $visibility[$visKey] = $request->boolean($formKey);
        }

        $data['visibility'] = $visibility;

        $profile->update($data);

        return redirect()
            ->route('user.doctor-profile.show')
            ->with('success', 'اطلاعات با موفقیت ذخیره شد.')
            ->with('active_tab', 'doctor');
    }
    /**
     * Update insurances JSON + logos upload
     */
    public function updateInsurance(Request $request): RedirectResponse
    {
        // We only need to validate the insurances JSON string now
        $request->validate([
            'insurances' => 'nullable|string',
        ]);

        $profile = DoctorProfile::firstOrCreate(['user_id' => auth()->id()]);

        $insurances = $request->filled('insurances')
            ? json_decode($request->insurances, true)
            : [];

        if (!is_array($insurances)) {
            $insurances = [];
        }

        foreach ($insurances as &$insurance) {
            // Check if a new image was passed as a Base64 encoded string inside 'preview'
            if (!empty($insurance['preview']) && str_starts_with($insurance['preview'], 'data:image')) {

                // Extract type and raw base64 data string
                @list($type, $fileData) = explode(';', $insurance['preview']);
                @list(, $fileData)      = explode(',', $fileData);

                // Determine file extension (jpg, png, webp, etc.)
                $extension = explode('/', $type)[1] ?? 'png';
                if ($extension === 'jpeg') $extension = 'jpg';

                // Build a unique safe filename
                $fileName = 'insurance_' . Str::random(10) . '_' . time() . '.' . $extension;
                $filePath = 'insurances/' . $fileName;

                // Decode data and save to public storage disk
                Storage::disk('public')->put($filePath, base64_decode($fileData));

                // Update the array record to store the persistent file path
                $insurance['logo'] = $filePath;
            }

            // CRITICAL: Always remove the giant base64 text string before saving to database
            unset($insurance['preview']);
        }

        $profile->insurances = $insurances ?: null;

        // Maintain your visibility configuration
        $visibility = $profile->visibility ?? [];
        $visibility['insurances'] = $request->boolean('visibility_insurances');
        $profile->visibility = $visibility;

        $profile->save();

        return back()->with('success', 'بیمه‌ها با موفقیت ذخیره شدند.');
    }

    /**
     * Upload photos
     */
    public function uploadPhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photos'   => 'required|array|max:12',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $userId = auth()->id();

        foreach ($request->file('photos') as $file) {
            $path = $this->uploadFile($file, "doctor-media/$userId/photos");
            DoctorMedia::create([
                'user_id'       => $userId,
                'type'          => 'photo',
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
                'sort_order'    => 0,
            ]);
        }

        return back()->with('success', 'تصاویر با موفقیت آپلود شدند.');
    }

    /**
     * Upload videos
     */
    public function uploadVideo(Request $request): RedirectResponse
    {
        $request->validate([
            'videos'   => 'required|array|max:5',
            'videos.*' => 'required|file|mimes:mp4,webm,ogg|max:20480',
        ]);

        $userId = auth()->id();

        foreach ($request->file('videos') as $file) {
            $path = $this->uploadFile($file, "doctor-media/$userId/videos");
            DoctorMedia::create([
                'user_id'       => $userId,
                'type'          => 'video',
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
                'sort_order'    => 0,
            ]);
        }

        return back()->with('success', 'ویدیوها با موفقیت آپلود شدند.');
    }

    /**
     * Delete media (photo/video)
     */
    public function deleteMedia(int $id): RedirectResponse
    {
        $media = DoctorMedia::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return back()->with('success', 'فایل حذف شد.');
    }


    public function store(Request $request): RedirectResponse
    {
        return back();
    }

    public function update(Request $request, $id): RedirectResponse
    {
        return back();
    }

    public function destroy($id): RedirectResponse
    {
        return back();
    }

    public function toggleVisibility(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'key'   => 'required|string',
            'value' => 'required|boolean',
        ]);

        $profile = DoctorProfile::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $visibility = $profile->visibility ?? [];

        $visibility[$request->key] = $request->value;

        $profile->visibility = $visibility;
        $profile->save();

        return response()->json([
            'success' => true,
            'key'     => $request->key,
            'value'   => $request->value,
        ]);
    }
}
