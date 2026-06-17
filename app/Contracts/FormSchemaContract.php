<?php

namespace App\Contracts;

interface FormSchemaContract
{
    /**
     * Returns the system fields.
     *
     * @return array
     */
    public static function getSystemFields(): array;

    /**
     * Returns the default system field values.
     *
     * @return array
     */
    public static function systemFieldDefaults(): array;

    /**
     * Normalizes the schema.
     *
     * @param array $schema
     * @return array
     */
    public static function normalizeSchema(array $schema): array;

    /**
     * Returns the quick fields.
     *
     * @return array
     */
    public function quickFields(): array;

    /**
     * Returns the field with the given ID.
     *
     * @param string $id
     * @return array|null
     */
    public function field(string $id): ?array;

    /**
     * Returns the schema.
     *
     * @return array
     */
    public function getSchema(): array;
}
