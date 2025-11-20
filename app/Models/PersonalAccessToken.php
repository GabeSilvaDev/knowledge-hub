<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use MongoDB\Laravel\Eloquent\DocumentModel;
use Override;

/**
 * Personal Access Token Model.
 *
 * Custom implementation of Sanctum's PersonalAccessToken model adapted for MongoDB.
 * Uses _id as primary key instead of id for MongoDB compatibility.
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use DocumentModel;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection = 'personal_access_tokens';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personal_access_tokens';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Get the value of the model's primary key.
     *
     * Overrides parent method to ensure MongoDB _id is returned as string.
     * Handles conversion of ObjectId to string for Sanctum compatibility.
     *
     * @return string|null The primary key value as string or null if not set
     */
    #[Override]
    public function getKey(): ?string
    {
        $key = parent::getKey();

        if ($key === null) {
            return null;
        }

        if (is_scalar($key)) {
            return (string) $key;
        }

        return null;
    }
}
