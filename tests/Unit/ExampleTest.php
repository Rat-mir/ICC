<?php

namespace Tests\Unit;
use App\Enums\{MakerStatus,UserRole};
use PHPUnit\Framework\TestCase;
class ExampleTest extends TestCase {
    public function test_only_draft_and_revision_can_be_submitted(): void {
        foreach(MakerStatus::cases() as $status) $this->assertSame(in_array($status,[MakerStatus::Draft,MakerStatus::Revision],true),$status->canSubmit());
    }
    public function test_pending_rejected_and_hidden_are_not_editable(): void {
        foreach([MakerStatus::Pending,MakerStatus::Rejected,MakerStatus::Hidden] as $status) $this->assertFalse($status->canEdit());
        $this->assertTrue(MakerStatus::Approved->canEdit());
    }
    public function test_roles_match_the_access_model(): void { $this->assertSame(['user','creator','admin'],array_map(fn($r)=>$r->value,UserRole::cases())); }
}
