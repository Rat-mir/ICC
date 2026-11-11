<?php
namespace App\Enums;
enum MakerStatus: string {
    case Draft = 'draft'; case Pending = 'pending'; case Approved = 'approved';
    case Revision = 'revision'; case Rejected = 'rejected'; case Hidden = 'hidden';
    public function canSubmit(): bool { return in_array($this, [self::Draft, self::Revision], true); }
    public function canEdit(): bool { return in_array($this, [self::Draft, self::Revision, self::Approved], true); }
}
