<?php

namespace App\Models\HRMS\MobileApp;

use App\Models\Core\UserM;
use Illuminate\Database\Eloquent\Model;

class MobileAppVersionM extends Model
{
    protected $table = 'mobile_app_versions';

    protected $fillable = [
        'app_name',
        'platform',
        'version_name',
        'version_code',
        'min_supported_version_code',
        'apk_file',
        'apk_original_name',
        'apk_size',
        'apk_mime_type',
        'apk_url',
        'release_notes',
        'is_force_update',
        'is_active',
        'release_date',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'version_code' => 'integer',
        'min_supported_version_code' => 'integer',
        'apk_size' => 'integer',
        'is_force_update' => 'boolean',
        'is_active' => 'boolean',
        'release_date' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(UserM::class, 'uploaded_by_user_id');
    }
}
