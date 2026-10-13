<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Helpers\ProvinceCity;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (!Schema::hasColumn('properties', 'province')) {
                    $table->string('province', 100)->nullable()->after('area');
                }
                if (!Schema::hasColumn('properties', 'city')) {
                    $table->string('city', 100)->nullable()->after('province');
                }
            });

            // استخراج و مطابقت هوشمند استان و شهر برای املاک ثبت‌شده قبلی از روی متن آدرس
            $allProvinces = ProvinceCity::getProvinces();
            $properties = DB::table('properties')->whereNotNull('address')->get(['id', 'address']);

            foreach ($properties as $prop) {
                $address = (string) $prop->address;
                if (trim($address) === '') {
                    continue;
                }

                $detectedProvince = null;
                $detectedCity = null;

                // ۱. تشخیص استان از روی لیست رسمی استان‌ها
                foreach ($allProvinces as $provinceName) {
                    $cleanProv = str_replace(['استان ', ' '], ['', ''], $provinceName);
                    $cleanAddr = str_replace(' ', '', $address);
                    if (str_contains($cleanAddr, $cleanProv)) {
                        $detectedProvince = $provinceName;
                        break;
                    }
                }

                // ۲. در صورت تشخیص استان، جستجوی شهر در میان شهرهای همان استان
                if ($detectedProvince) {
                    $cities = ProvinceCity::getCities($detectedProvince);
                    foreach ($cities as $cityName) {
                        $cleanCity = str_replace(' ', '', $cityName);
                        $cleanAddr = str_replace(' ', '', $address);
                        if (str_contains($cleanAddr, $cleanCity)) {
                            $detectedCity = $cityName;
                            break;
                        }
                    }
                }

                if ($detectedProvince || $detectedCity) {
                    DB::table('properties')->where('id', $prop->id)->update([
                        'province' => $detectedProvince,
                        'city'     => $detectedCity,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'city')) {
                    $table->dropColumn('city');
                }
                if (Schema::hasColumn('properties', 'province')) {
                    $table->dropColumn('province');
                }
            });
        }
    }
};
