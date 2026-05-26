<?php

namespace Modules\Market\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\FormSchemaContract;
use Illuminate\Support\Str;
use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\Category;

class CheckoutForm extends Model implements FormSchemaContract
{
    protected $table = 'checkout_forms';

    protected $fillable = ['name', 'key', 'is_active', 'schema', 'product_id', 'category_id'];

    protected $casts = [
        'schema'    => 'array',
        'is_active' => 'bool',
    ];

    public const SYSTEM_FIELDS = [
        'full_name'     => ['label' => 'نام و نام خانوادگی', 'column' => 'full_name'],
        'email'         => ['label' => 'ایمیل',              'column' => 'email'],
        'phone'         => ['label' => 'شماره تماس',         'column' => 'phone'],
        'national_code' => ['label' => 'کد ملی',             'column' => 'national_code'],
    ];

    public function product()
    {
        return $this->belongsTo(MasterProduct::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public static function getSystemFields(): array
    {
        return self::SYSTEM_FIELDS;
    }

    public static function systemFieldDefaults(): array
    {
        return [
            'full_name' => [
                'id' => 'full_name',
                'type' => 'text',
                'label' => 'نام و نام خانوادگی',
                'required' => true,
                'is_system' => true,
            ],
            'phone' => [
                'id' => 'phone',
                'type' => 'text',
                'label' => 'شماره تماس',
                'required' => true,
                'is_system' => true,
            ],
            'email' => [
                'id' => 'email',
                'type' => 'email',
                'label' => 'ایمیل',
                'required' => false,
                'is_system' => true,
            ],
            'national_code' => [
                'id' => 'national_code',
                'type' => 'text',
                'label' => 'کد ملی',
                'required' => false,
                'is_system' => true,
            ],
        ];
    }

    public static function normalizeSchema(array $schema): array
    {
        $fields = $schema['fields'] ?? [];
        $systemDefaults = self::systemFieldDefaults();
        $normalized = [];

        foreach ($fields as $field) {
            if (!is_array($field) || empty($field['id'])) {
                $field['id'] = Str::uuid()->toString();
            }

            if (isset(self::SYSTEM_FIELDS[$field['id']])) {
                $field = array_merge($systemDefaults[$field['id']], $field);
                $field['is_system'] = true;
            }

            $normalized[] = $field;
        }

        $schema['fields'] = $normalized;
        return $schema;
    }

    public function quickFields(): array
    {
        return array_filter($this->schema['fields'] ?? [], function ($field) {
            return $field['quick_create'] ?? false;
        });
    }

    public function field(string $id): ?array
    {
        return collect($this->schema['fields'] ?? [])->firstWhere('id', $id);
    }

    public function getSchema(): array
    {
        return $this->schema;
    }
}
