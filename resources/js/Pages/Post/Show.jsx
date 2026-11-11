import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, ImageMinus, Upload } from 'lucide-react';
import AppLayout, { MarkdownViewer, Spinner } from '../../Layouts/AppLayout';

export default function Show({ post }) {
    const form = useForm({ cover: null });

    const upload = (e) => {
        e.preventDefault();
        form.post(`/dashboard/posts/${post.id}/cover`, { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset() });
    };

    return (
        <AppLayout>
            <Head title={post.title} />
            <Link href="/dashboard/posts" className="mb-4 inline-flex items-center gap-1 text-sm text-indigo-700"><ArrowLeft className="h-4 w-4" /> Kembali ke daftar</Link>

            <div className="grid gap-6 lg:grid-cols-[1fr_320px]">
                <article className="rounded-2xl border border-slate-200 bg-white p-6">
                    <div className="mb-2 flex items-center gap-2">
                        <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{post.category}</span>
                        <span className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${post.is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>{post.is_published ? 'Published' : 'Draf'}</span>
                    </div>
                    <h1 className="text-2xl font-bold">{post.title}</h1>
                    {post.excerpt && <p className="mt-2 text-slate-600">{post.excerpt}</p>}
                    {post.cover_url && <img src={post.cover_url} alt={post.title} className="mt-4 w-full rounded-xl object-cover" />}
                    <div className="mt-6"><MarkdownViewer content={post.content} /></div>
                </article>

                <aside className="h-fit space-y-4 rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 className="font-semibold">Foto Sampul</h2>
                    <form onSubmit={upload} className="space-y-3">
                        <input type="file" accept="image/*" onChange={(e) => form.setData('cover', e.target.files[0] || null)} className="w-full text-sm" />
                        {form.errors.cover && <p className="text-sm text-red-600">{form.errors.cover}</p>}
                        <button disabled={!form.data.cover || form.processing} className="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                            {form.processing ? <Spinner /> : <Upload className="h-4 w-4" />} Unggah Sampul
                        </button>
                    </form>
                    {post.cover_url && (
                        <button onClick={() => router.delete(`/dashboard/posts/${post.id}/cover`, { preserveScroll: true })} className="flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                            <ImageMinus className="h-4 w-4" /> Hapus Sampul
                        </button>
                    )}
                    <button onClick={() => router.patch(`/dashboard/posts/${post.id}/publish`, {}, { preserveScroll: true })} className="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">
                        {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                    </button>
                    {post.is_published && <Link href={`/articles/${post.slug}`} className="block text-center text-sm text-indigo-700">Lihat di halaman publik</Link>}
                </aside>
            </div>
        </AppLayout>
    );
}
