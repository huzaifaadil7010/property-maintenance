<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\AttachmentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'maintenance_request_id', 'uploaded_by', 'type', 'file_path', 'original_name', 'mime_type', 'size'])]
class MaintenanceRequestAttachment extends Model
{
    use BelongsToOrganization;

    protected $attributes = ['type' => AttachmentType::ISSUE->value];

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected function casts(): array
    {
        return ['type' => AttachmentType::class, 'size' => 'integer'];
    }
}
