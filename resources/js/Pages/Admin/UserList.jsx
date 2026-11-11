import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, Search } from 'lucide-react';
import AppLayout, { Pagination } from '../../Layouts/AppLayout';

export default function UserList({ users, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    return (
        <AppLayout>
            <Head title="Kelola Pengguna" />
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Kelola Pengguna</h1>
                <a href="/admin/posts/export" className="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50">Ekspor Semua Artikel</a>
            </div>
            <form onSubmit={(e) => { e.preventDefault(); router.get('/admin/users', { search }, { preserveState: true, replace: true }); }} className="relative mb-4 max-w-md">
                <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari nama, username, atau email..." className="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3" />
            </form>
            <div className="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                <table className="w-full text-left text-sm">
                    <thead className="bg-slate-50 text-slate-500"><tr>
                        <th className="px-4 py-3">Nama</th><th className="px-4 py-3">Username</th><th className="px-4 py-3">Email</th>
                        <th className="px-4 py-3">Peran</th><th className="px-4 py-3">Artikel</th><th className="px-4 py-3" />
                    </tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {users.data.map((u) => (
                            <tr key={u.id}>
                                <td className="px-4 py-3 font-medium">{u.name}</td><td className="px-4 py-3">@{u.username}</td><td className="px-4 py-3">{u.email}</td>
                                <td className="px-4 py-3">{u.role}</td><td className="px-4 py-3">{u.posts_count}</td>
                                <td className="px-4 py-3 text-right"><Link href={`/admin/users/${u.id}/posts`} className="inline-flex items-center gap-1 text-indigo-700"><Eye className="h-4 w-4" /> Artikel</Link></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={users.links} />
        </AppLayout>
    );
}
