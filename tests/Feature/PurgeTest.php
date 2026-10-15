<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_purge_removes_old_rejected_proofs_and_handled_messages_only(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('proofs/old.pdf', 'x');
        Storage::disk('local')->put('proofs/recent.pdf', 'x');

        $old = Claim::factory()->create(['status' => ClaimStatus::Rejected, 'handled_at' => now()->subDays(91), 'proof_path' => 'proofs/old.pdf']);
        $recent = Claim::factory()->create(['status' => ClaimStatus::Rejected, 'handled_at' => now()->subDays(10), 'proof_path' => 'proofs/recent.pdf']);
        ContactMessage::factory()->create(['handled_at' => now()->subMonths(13)]);
        $kept = ContactMessage::factory()->create(['handled_at' => null]);

        $this->artisan('doggys:purge')->assertSuccessful();

        $this->assertNull($old->fresh()->proof_path);
        Storage::disk('local')->assertMissing('proofs/old.pdf');
        $this->assertSame('proofs/recent.pdf', $recent->fresh()->proof_path);
        $this->assertSame([$kept->id], ContactMessage::pluck('id')->all());
    }
}
