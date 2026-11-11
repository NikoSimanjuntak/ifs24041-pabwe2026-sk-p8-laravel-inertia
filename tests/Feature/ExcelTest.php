<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelTest extends TestCase
{
    use RefreshDatabase;

    private function rows($response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xl');
        file_put_contents($path, $response->streamedContent());

        return IOFactory::load($path)->getActiveSheet()->toArray();
    }

    private function xlsx(array $rows): UploadedFile
    {
        $book = new Spreadsheet;
        $book->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'im').'.xlsx';
        (new Xlsx($book))->save($path);

        return new UploadedFile($path, 'artikel.xlsx', null, null, true);
    }

    public function test_member_exports_only_own_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->create(['title' => 'Milik Saya', 'content' => '=SUM(1,2)']);
        Post::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/posts/export')->assertOk()
            ->assertDownload('artikel-saya.xlsx');
        $rows = $this->rows($response);

        $this->assertCount(2, $rows);
        $this->assertSame(['Judul', 'Kategori', 'Ringkasan', 'Konten', 'Status'], $rows[0]);
        $this->assertSame('Milik Saya', $rows[1][0]);
        $this->assertSame('=SUM(1,2)', $rows[1][3]); // tidak dievaluasi sebagai rumus
    }

    public function test_member_imports_posts_from_excel(): void
    {
        $user = User::factory()->create();
        $file = $this->xlsx([
            ['Judul', 'Kategori', 'Ringkasan', 'Konten', 'Status'],
            ['Artikel Satu', 'Bisnis', 'ringkas', 'isi satu', 'Published'],
            ['Artikel Dua', 'Tidak Dikenal', '', 'isi dua', 'Draft'],
            ['', 'Umum', '', 'tanpa judul', 'Draft'],
            ['Tanpa Isi', 'Umum', '', '', 'Draft'],
        ]);

        $this->actingAs($user)->post('/dashboard/posts/import', ['file' => $file])
            ->assertRedirect()->assertSessionHas('success', '2 artikel berhasil diimpor.');

        $this->assertDatabaseHas('posts', ['title' => 'Artikel Satu', 'category' => 'Bisnis', 'is_published' => true, 'user_id' => $user->id]);
        $this->assertDatabaseHas('posts', ['title' => 'Artikel Dua', 'category' => 'Umum', 'is_published' => false, 'excerpt' => null]);
    }

    public function test_import_rejects_invalid_files(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/dashboard/posts/import', [])->assertSessionHasErrors('file');
        $this->actingAs($user)->post('/dashboard/posts/import', ['file' => UploadedFile::fake()->create('a.txt', 1, 'text/plain')])
            ->assertSessionHasErrors('file');
    }

    public function test_service_throws_validation_exception_for_unreadable_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'bad');
        file_put_contents($path, 'bukan excel');

        $this->expectException(ValidationException::class);
        app(PostExcelService::class)->import(new UploadedFile($path, 'bad.xlsx', null, null, true), User::factory()->create());
    }

    public function test_admin_exports_user_posts_and_all_posts(): void
    {
        $admin = User::factory()->admin()->create();
        $writer = User::factory()->create(['username' => 'penulis1', 'name' => 'Penulis Satu']);
        Post::factory()->count(2)->for($writer)->create();
        Post::factory()->draft()->create();

        $one = $this->rows($this->actingAs($admin)->get("/admin/users/{$writer->id}/posts/export")->assertDownload('artikel-penulis1.xlsx'));
        $this->assertCount(3, $one);

        $all = $this->rows($this->actingAs($admin)->get('/admin/posts/export')->assertDownload('semua-artikel.xlsx'));
        $this->assertCount(4, $all);
        $this->assertSame('Penulis', $all[0][0]);
        $this->assertSame('Penulis Satu', $all[1][0]);
    }
}
