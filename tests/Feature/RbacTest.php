<?php

namespace Tests\Feature;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function sekolah(string $npsn): Sekolah
    {
        return Sekolah::create([
            'npsn' => $npsn, 'nama' => "Sekolah $npsn",
            'jenjang' => 'SD', 'status' => 'Negeri',
            'alamat' => 'Jl. Uji', 'kecamatan' => 'Uji',
            'latitude' => -3.3, 'longitude' => 114.59,
        ]);
    }

    private function user(string $role, ?Sekolah $sekolah = null): User
    {
        return User::factory()->create([
            'role' => $role,
            'sekolah_id' => $sekolah?->id,
        ]);
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_gagal_dan_berhasil(): void
    {
        $u = $this->user('dinas');

        $this->post('/login', ['email' => $u->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->post('/login', ['email' => $u->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($u);
    }

    public function test_admin_hanya_mengelola_pengguna(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/dinas/sekolah')->assertForbidden();
    }

    public function test_admin_menambah_pengguna_sekolah(): void
    {
        $sekolah = $this->sekolah('11111111');

        $this->actingAs($this->user('admin'))->post('/admin/users', [
            'name' => 'Operator', 'email' => 'op@uji.test',
            'role' => 'sekolah', 'sekolah_id' => $sekolah->id,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'op@uji.test', 'role' => 'sekolah',
            'sekolah_id' => $sekolah->id,
        ]);
    }

    public function test_role_sekolah_wajib_memilih_sekolah(): void
    {
        $this->actingAs($this->user('admin'))->post('/admin/users', [
            'name' => 'Operator', 'email' => 'op@uji.test',
            'role' => 'sekolah',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('sekolah_id');
    }

    public function test_dinas_mengelola_semua_sekolah(): void
    {
        $a = $this->sekolah('11111111');
        $dinas = $this->user('dinas');

        $this->actingAs($dinas)->get('/dinas/sekolah')->assertOk();
        $this->actingAs($dinas)->get("/sekolah/{$a->id}/edit")->assertOk();
        $this->actingAs($dinas)->get("/sekolah/{$a->id}/profil")->assertOk();
        $this->actingAs($dinas)->get('/admin/users')->assertForbidden();
    }

    public function test_sekolah_hanya_mengakses_sekolah_sendiri(): void
    {
        $a = $this->sekolah('11111111');
        $b = $this->sekolah('22222222');
        $op = $this->user('sekolah', $a);

        $this->actingAs($op)->get("/sekolah/{$a->id}/edit")->assertOk();
        $this->actingAs($op)->get("/sekolah/{$b->id}/edit")->assertForbidden();
        $this->actingAs($op)->get("/sekolah/{$b->id}/profil")->assertForbidden();
        $this->actingAs($op)->get('/dinas/sekolah')->assertForbidden();
        $this->actingAs($op)->get('/admin/users')->assertForbidden();
    }

    public function test_sekolah_tidak_dapat_mengubah_npsn(): void
    {
        $a = $this->sekolah('11111111');
        $op = $this->user('sekolah', $a);

        $this->actingAs($op)->put("/sekolah/{$a->id}", [
            'npsn' => '99999999', 'nama' => 'Nama Baru',
            'jenjang' => 'SD', 'status' => 'Negeri',
            'alamat' => 'Jl. Baru', 'kecamatan' => 'Uji',
            'latitude' => -3.301, 'longitude' => 114.591,
        ])->assertRedirect();

        $a->refresh();
        $this->assertSame('11111111', $a->npsn);
        $this->assertSame('Nama Baru', $a->nama);
    }

    public function test_sekolah_menyimpan_profil_sendiri(): void
    {
        $a = $this->sekolah('11111111');
        $op = $this->user('sekolah', $a);

        $this->actingAs($op)->put("/sekolah/{$a->id}/profil", [
            'kepala_sekolah' => 'Bapak Uji', 'akreditasi' => 'A',
        ])->assertRedirect();

        $this->assertDatabaseHas('profil_sekolahs', [
            'sekolah_id' => $a->id, 'kepala_sekolah' => 'Bapak Uji',
        ]);
    }

    public function test_sekolah_tidak_dapat_menghapus_sekolah(): void
    {
        $a = $this->sekolah('11111111');
        $op = $this->user('sekolah', $a);

        $this->actingAs($op)->delete("/dinas/sekolah/{$a->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('sekolahs', ['id' => $a->id]);
    }

    public function test_pengguna_dapat_mengubah_kata_sandi(): void
    {
        $u = $this->user('dinas');
        $this->actingAs($u)->put('/kata-sandi', [
            'current_password' => 'password',
            'password' => 'baru12345',
            'password_confirmation' => 'baru12345',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('baru12345', $u->fresh()->password));
    }

    public function test_ubah_kata_sandi_menolak_sandi_lama_salah(): void
    {
        $u = $this->user('dinas');
        $this->actingAs($u)->put('/kata-sandi', [
            'current_password' => 'salah',
            'password' => 'baru12345',
            'password_confirmation' => 'baru12345',
        ])->assertSessionHasErrors('current_password');
    }

        public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        $u = $this->user('dinas');
        $u->update(['is_active' => false]);
        $this->post('/login', ['email' => $u->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_akun_nonaktif_yang_sedang_login_dikeluarkan(): void
    {
        $u = $this->user('dinas');
        $u->update(['is_active' => false]);
        $this->actingAs($u)->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_dapat_menonaktifkan_pengguna(): void
    {
        $admin = $this->user('admin');
        $target = $this->user('dinas');
        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name, 'email' => $target->email,
            'role' => 'dinas', 'is_active' => '0',
        ])->assertRedirect('/admin/users');
        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_tidak_dapat_menonaktifkan_diri_sendiri(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email,
            'role' => 'admin', 'is_active' => '0',
        ])->assertSessionHasErrors('is_active');
        $this->assertTrue($admin->fresh()->is_active);
    }
}