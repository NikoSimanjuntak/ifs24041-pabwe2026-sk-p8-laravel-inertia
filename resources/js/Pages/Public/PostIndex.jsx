import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { LogIn, Search, UserPlus } from 'lucide-react';
import AppLayout, { Pagination } from '../../Layouts/AppLayout';

export default function PostIndex({ posts, filters, categories }) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState(filters.search || '');

    const go = (params) => router.get('/articles', { ...filters, ...params }, { preserveState: true, replace: true });

    return (
        <AppLayout mode="public">
            <Head title="Beranda" />

            <section className="mb-10 rounded-3xl bg-gradient-to-br from-indigo-700 to-violet-600 px-6 py-14 text-center text-white sm:px-12">
                <h1 className="text-3xl font-extrabold sm:text-5xl">BlogVerse Pro</h1>
                <p className="mx-auto mt-4 max-w-2xl text-indigo-50">Platform publikasi artikel multi-tier. Baca tulisan terbaik dari para penulis, atau bergabung dan terbitkan gagasan Anda sendiri.</p>
                {!auth?.user && (
                    <div className="mt-8 flex flex-wrap justify-center gap-3">
                        <Link href="/login" className="flex items-center gap-2 rounded-lg bg-white px-5 py-3 font-semibold text-indigo-700 hover:bg-indigo-50"><LogIn className="h-4 w-4" aria-hidden="true" /> Masuk</Link>
                        <Link href="/register" className="flex items-center gap-2 rounded-lg border border-white/60 px-5 py-3 font-semibold hover:bg-white/10"><UserPlus className="h-4 w-4" aria-hidden="true" /> Daftar Penulis</Link>
                    </div>
                )}
            </section>

            <form role="search" onSubmit={(e) => { e.preventDefault(); go({ search, page: 1 }); }} className="mb-4 flex gap-2">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-3 h-4 w-4 text-slate-500" aria-hidden="true" />
                    <input aria-label="Cari artikel" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari berdasarkan judul atau isi artikel..."
                        className="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3" />
                </div>
                <button className="rounded-lg bg-indigo-600 px-5 font-medium text-white hover:bg-indigo-700">Cari</button>
            </form>

            <div className="mb-8 flex flex-wrap gap-2" role="group" aria-label="Filter kategori">
                <button onClick={() => go({ category: '', page: 1 })} aria-pressed={!filters.category} className={`rounded-full border px-3 py-1 text-sm ${!filters.category ? 'border-indigo-600 bg-indigo-600 text-white' : 'bg-white hover:bg-slate-100'}`}>Semua</button>
                {categories.map((c) => (
                    <button key={c.value} onClick={() => go({ category: c.value, page: 1 })} aria-pressed={filters.category === c.value}
                        className={`rounded-full border px-3 py-1 text-sm ${filters.category === c.value ? 'border-indigo-600 bg-indigo-600 text-white' : 'bg-white hover:bg-slate-100'}`}>{c.value}</button>
                ))}
            </div>

            {posts.data.length === 0 ? (
                <p className="rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center text-slate-600">Belum ada artikel yang cocok.</p>
            ) : (
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {posts.data.map((p) => (
                        <Link key={p.id} href={`/articles/${p.slug}`} className="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                            {p.cover_url ? <img src={p.cover_url} alt="" className="h-44 w-full object-cover" />
                                : <div className="h-44 bg-gradient-to-br from-indigo-100 to-violet-100" aria-hidden="true" />}
                            <div className="p-5">
                                <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{p.category}</span>
                                <h2 className="mt-3 text-lg font-semibold group-hover:text-indigo-700">{p.title}</h2>
                                <p className="mt-2 line-clamp-3 text-sm text-slate-600">{p.excerpt}</p>
                                <p className="mt-4 text-xs text-slate-600">{p.author} · {p.published_at}</p>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
            <Pagination links={posts.links} />
        </AppLayout>
    );
}