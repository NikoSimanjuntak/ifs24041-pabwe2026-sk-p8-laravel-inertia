import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeft, Download, Search } from 'lucide-react';
import AppLayout, { Pagination } from '../../Layouts/AppLayout';

export default function UserPostList({ owner, posts, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    return (
        <AppLayout>
            <Head title={`Artikel ${owner.name}`} />
            <Link href="/admin/users" className="mb-4 inline-flex items-center gap-1 text-sm text-indigo-700"><ArrowLeft className="h-4 w-4" /> Kembali ke pengguna</Link>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Artikel {owner.name} <span className="text-sm font-normal text-slate-500">(read-only)</span></h1>
                <a href={`/admin/users/${owner.id}/posts/export`} className="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"><Download className="h-4 w-4" /> Ekspor Excel</a>
            </div>
            <form onSubmit={(e) => { e.preventDefault(); router.get(`/admin/users/${owner.id}/posts`, { search }, { preserveState: true, replace: true }); }} className="relative mb-4 max-w-md">
                <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel..." className="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3" />
            </form>
            <div className="divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white">
                {posts.data.length === 0 && <p className="py-12 text-center text-slate-500">Pengguna ini belum memiliki artikel.</p>}
                {posts.data.map((p) => (
                    <div key={p.id} className="flex items-center gap-3 px-4 py-3">
                        <div className="min-w-0 flex-1">
                            <p className="truncate font-medium">{p.title}</p>
                            <p className="text-xs text-slate-500">{p.category} · {p.created_at}</p>
                        </div>
                        <span className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${p.is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>{p.is_published ? 'Published' : 'Draf'}</span>
                    </div>
                ))}
            </div>
            <Pagination links={posts.links} />
        </AppLayout>
    );
}
