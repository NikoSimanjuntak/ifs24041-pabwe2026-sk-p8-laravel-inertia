<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostController extends Controller
{
    public function __construct(private readonly PostService $service) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');

        return Inertia::render('Post/Index', [
            'posts' => $this->service->listForUser($request->user(), $search)
                ->through(fn (Post $p) => $p->toSummary(true)),
            'stats' => $this->service->stats($request->user()),
            'filters' => ['search' => $search],
            'categories' => PostCategory::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->service->create($request->user(), $this->validated($request), $request->file('cover'));

        return back()->with('success', 'Artikel berhasil disimpan.');
    }

    public function show(Post $post): Response
    {
        return Inertia::render('Post/Show', ['post' => $post->load('user:id,name')->toSummary(true)]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->service->update($post, $this->validated($request), $request->file('cover'));

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->service->delete($post);

        return redirect()->route('dashboard.posts.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function publish(Post $post): RedirectResponse
    {
        $post = $this->service->togglePublish($post);

        return back()->with('success', $post->is_published ? 'Artikel dipublikasikan.' : 'Artikel dijadikan draf.');
    }

    public function uploadCover(Request $request, Post $post): RedirectResponse
    {
        $request->validate(['cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);
        $this->service->uploadCover($post, $request->file('cover'));

        return back()->with('success', 'Foto sampul berhasil diunggah.');
    }

    public function removeCover(Post $post): RedirectResponse
    {
        $this->service->removeCover($post);

        return back()->with('success', 'Foto sampul dihapus.');
    }

    public function export(Request $request, PostRepositoryInterface $posts, PostExcelService $excel): StreamedResponse
    {
        return $excel->export($posts->allForUser($request->user()), 'artikel-saya.xlsx');
    }

    public function import(Request $request, PostExcelService $excel): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']]);
        $count = $excel->import($request->file('file'), $request->user());

        return back()->with('success', "{$count} artikel berhasil diimpor.");
    }

    public function aiGenerate(Request $request, AiClientInterface $ai): JsonResponse
    {
        $data = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:5'],
            'category' => ['nullable', Rule::enum(PostCategory::class)],
        ]);

        try {
            $items = $ai->generate($data['topic'], (int) $data['quantity'], $data['category'] ?? null);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['items' => array_map(fn ($i) => $i->toArray(), $items)]);
    }

    public function aiSave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:5'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.category' => ['required', Rule::enum(PostCategory::class)],
            'items.*.excerpt' => ['nullable', 'string'],
            'items.*.content' => ['required', 'string'],
        ]);

        $count = $this->service->saveBatch($request->user(), $data['items']);

        return back()->with('success', "{$count} artikel hasil AI disimpan sebagai draf.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(PostCategory::class)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }
}
