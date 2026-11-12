<?php

namespace App\Features\Post\Services;

use App\Features\Post\Enums\PostCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PostExcelService
{
    public function __construct(private readonly PostService $posts) {}

    public function export(Collection $posts, string $filename, bool $withAuthor = false): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Judul', 'Kategori', 'Ringkasan', 'Konten', 'Status'];
        if ($withAuthor) {
            array_unshift($headers, 'Penulis');
        }

        $rows = [$headers];
        foreach ($posts as $post) {
            $row = [$post->title, $post->category, (string) $post->excerpt, $post->content, $post->is_published ? 'Published' : 'Draft'];
            if ($withAuthor) {
                array_unshift($row, (string) $post->user?->name);
            }
            $rows[] = $row;
        }

        // Tulis sebagai string eksplisit agar isi sel tidak pernah dieksekusi sebagai rumus.
        foreach ($rows as $r => $row) {
            foreach ($row as $c => $value) {
                $sheet->setCellValueExplicit([$c + 1, $r + 1], (string) $value, DataType::TYPE_STRING);
            }
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function import(UploadedFile $file, User $user): int
    {
        try {
           $rows = IOFactory::load($file->getRealPath(), 0, [IOFactory::READER_XLSX, IOFactory::READER_XLS])
    ->getActiveSheet()->toArray(null, false, false, false);
        } catch (Throwable) {
            throw ValidationException::withMessages(['file' => 'Berkas Excel tidak dapat dibaca.']);
        }

        array_shift($rows); // baris header

        $count = 0;
        foreach ($rows as $row) {
            [$title, $category, $excerpt, $content, $status] = array_pad($row, 5, null);

            $title = trim((string) $title);
            $content = trim((string) $content);
            if ($title === '' || $content === '') {
                continue;
            }

            $this->posts->create($user, [
                'title' => mb_substr($title, 0, 255),
                'category' => PostCategory::tryFrom(trim((string) $category))?->value ?? PostCategory::Umum->value,
                'excerpt' => trim((string) $excerpt) ?: null,
                'content' => $content,
                'is_published' => strtolower(trim((string) $status)) === 'published',
            ]);
            $count++;
        }

        return $count;
    }
}
