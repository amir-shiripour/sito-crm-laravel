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

        if ($isBookingActive && $user->canAccessDoctorTab()) {
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
        if (!auth()->user()->canAccessDoctorTab()) {
            abort(403);
        }

        $data = $request->validate([
            'medical_system_number' => 'nullable|string|max:255',
            'specialty'             => 'nullable',
            'experience'            => 'nullable|string|max:255',
            'education'             => 'nullable',
            'clinic_name'           => 'nullable|string|max:255',
            'province'              => 'nullable|string|max:100',
            'city'                  => 'nullable|string|max:100',
            'clinic_address'        => 'nullable|string|max:500',
            'about_me'              => 'nullable|string|max:2000',
        ]);

        if (array_key_exists('specialty', $data)) {
            $spec = $data['specialty'];
            if (is_string($spec)) {
                $decoded = json_decode($spec, true);
                if (is_array($decoded)) {
                    $spec = $decoded;
                } elseif (trim($spec) !== '') {
                    $spec = [trim($spec)];
                } else {
                    $spec = [];
                }
            }
            if (is_array($spec)) {
                $clean = array_values(array_filter(array_map('trim', $spec)));
                $data['specialty'] = count($clean) > 0 ? json_encode($clean, JSON_UNESCAPED_UNICODE) : null;
            } else {
                $data['specialty'] = null;
            }
        }

        if (array_key_exists('education', $data)) {
            $edu = $data['education'];
            if (is_string($edu)) {
                $decoded = json_decode($edu, true);
                if (is_array($decoded)) {
                    $edu = $decoded;
                } elseif (trim($edu) !== '') {
                    $edu = [trim($edu)];
                } else {
                    $edu = [];
                }
            }
            if (is_array($edu)) {
                $clean = array_values(array_filter(array_map('trim', $edu)));
                $data['education'] = count($clean) > 0 ? json_encode($clean, JSON_UNESCAPED_UNICODE) : null;
            } else {
                $data['education'] = null;
            }
        }

        if (array_key_exists('experience', $data)) {
            $exp = $data['experience'];
            if ($exp !== null && $exp !== '') {
                // Convert Persian/Arabic digits to English
                $enExp = str_replace(
                    ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
                    ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
                    (string)$exp
                );
                if (preg_match('/\d+/', $enExp, $matches)) {
                    $data['experience'] = (string)(int)$matches[0];
                } else {
                    $data['experience'] = trim((string)$exp);
                }
            } else {
                $data['experience'] = null;
            }
        }

        $profile = DoctorProfile::firstOrCreate(['user_id' => auth()->id()]);
        $visibility = $profile->visibility ?? [];
        $checkboxMap = [
            'visibility_about_me'              => 'about_me',
            'visibility_about'                 => 'about_me',       // fallback alias
            'visibility_specialty'             => 'specialty',
            'visibility_clinic_name'           => 'clinic_name',
            'visibility_education'             => 'education',
            'visibility_medical_system_number' => 'medical_system_number',
            'visibility_experience'            => 'experience',
            'visibility_location'              => 'location',
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
        if (!auth()->user()->canAccessDoctorTab()) {
            abort(403);
        }

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
        if (!auth()->user()->canAccessDoctorTab()) {
            abort(403);
        }

        $request->validate([
            'photos'   => 'required|array|max:12',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $userId = auth()->id();

        foreach ($request->file('photos') as $file) {
            $path = $this->uploadFile($file, "doctor-media/$userId/photos");
            $fullPath = Storage::disk('public')->path($path);
            DoctorMedia::create([
                'user_id'       => $userId,
                'type'          => 'photo',
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => file_exists($fullPath) ? (mime_content_type($fullPath) ?: 'image/webp') : $file->getMimeType(),
                'file_size'     => file_exists($fullPath) ? filesize($fullPath) : $file->getSize(),
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
        if (!auth()->user()->canAccessDoctorTab()) {
            abort(403);
        }

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
        if (!auth()->user()->canAccessDoctorTab()) {
            abort(403);
        }

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

    /**
     * Update doctor stats, ratings & trust indicators
     */
    public function updateStats(Request $request): RedirectResponse
    {
        if (!auth()->user()->canAccessDoctorTab()) {
            abort(403);
        }

        $request->validate([
            'mode'                      => 'required|in:manual,auto',
            'rating'                    => 'nullable|numeric|min:0|max:5',
            'reviews_count'             => 'nullable|integer|min:0',
            'satisfaction_rate'         => 'nullable|integer|min:0|max:100',
            'successful_bookings_count' => 'nullable|integer|min:0',
            'platform_name'             => 'nullable|string|max:100',
            'endorsements_count'        => 'nullable|integer|min:0',
            'endorsements_text'         => 'nullable|string|max:255',
        ]);

        $profile = DoctorProfile::firstOrCreate(['user_id' => auth()->id()]);

        $stats = [
            'mode'                      => $request->input('mode', 'manual'),
            'rating'                    => (float) $request->input('rating', 4.8),
            'reviews_count'             => (int) $request->input('reviews_count', 0),
            'satisfaction_rate'         => (int) $request->input('satisfaction_rate', 95),
            'successful_bookings_count' => (int) $request->input('successful_bookings_count', 0),
            'platform_name'             => trim((string) $request->input('platform_name', '')),
            'endorsements_count'        => (int) $request->input('endorsements_count', 0),
            'endorsements_text'         => trim((string) $request->input('endorsements_text', '')),
        ];

        $profile->stats = $stats;

        // Visibility switches
        $visibility = $profile->visibility ?? [];
        $checkboxMap = [
            'visibility_stats'               => 'stats',
            'visibility_rating'              => 'rating',
            'visibility_satisfaction'        => 'satisfaction',
            'visibility_successful_bookings' => 'successful_bookings',
            'visibility_endorsements'        => 'endorsements',
        ];

        foreach ($checkboxMap as $formKey => $visKey) {
            $visibility[$visKey] = $request->boolean($formKey);
        }

        $profile->visibility = $visibility;
        $profile->save();

        return redirect()
            ->route('user.doctor-profile.show')
            ->with('success', 'تنظیمات آمار و اعتبارسنجی با موفقیت ذخیره شد.')
            ->with('active_tab', 'doctor');
    }

    public function toggleVisibility(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!auth()->user()->canAccessDoctorTab()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
