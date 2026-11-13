import { Head, Link, router, useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';
import axios from 'axios';
import {
    Download, Eye, FileCheck2, FilePen, FileText, MoreVertical, Pencil, Plus, Search, Sparkles, Trash2, Upload,
} from 'lucide-react';
import AppLayout, { FullScreenModal, MarkdownEditor, Pagination, Spinner } from '../../Layouts/AppLayout';

const blank = { title: '', category: 'Umum', excerpt: '', content: '', is_published: false, cover: null };

function Stat({ icon: Icon, label, value, tone }) {
    return (
        <div className="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5">
            <span className={`rounded-xl p-3 ${tone}`}><Icon className="h-6 w-6" /></span>
            <div><p className="text-sm text-slate-500">{label}</p><p className="text-2xl font-bold">{value}</p></div>
        </div>
    );
}

function RowMenu({ post, onEdit, onDelete }) {
    const [open, setOpen] = useState(false);
    const item = 'flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-slate-50';
    return (
        <div className="relative">
            <button onClick={() => setOpen(!open)} aria-label="Menu tindakan" className="rounded-lg p-2 hover:bg-slate-100"><MoreVertical className="h-5 w-5" /></button>
            {open && (
                <>
                    <div className="fixed inset-0 z-10" onClick={() => setOpen(false)} />
                    <div className="absolute right-0 z-20 mt-1 w-40 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                        <Link href={`/dashboard/posts/${post.id}`} className={`${item} text-indigo-700`}><Eye className="h-4 w-4" /> Detail</Link>
                        <button onClick={() => { setOpen(false); onEdit(post); }} className={`${item} text-indigo-700`}><Pencil className="h-4 w-4" /> Ubah</button>
                        <button onClick={() => { setOpen(false); onDelete(post); }} className={`${item} text-red-600`}><Trash2 className="h-4 w-4" /> Hapus</button>
                    </div>
                </>
            )}
        </div>
    );
}

export default function Index({ posts, stats, filters, categories }) {
    const [search, setSearch] = useState(filters.search || '');
    const [modal, setModal] = useState(null); // null | 'form' | 'ai'
    const [editing, setEditing] = useState(null);
    const [toDelete, setToDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);
    const [importing, setImporting] = useState(false);
    const fileRef = useRef(null);
    const form = useForm(blank);

    // ----- AI generator -----
    const [ai, setAi] = useState({ topic: '', quantity: 1, category: '' });
    const [aiItems, setAiItems] = useState([]);
    const [aiLoading, setAiLoading] = useState(false);
    const [aiSaving, setAiSaving] = useState(false);
    const [aiError, setAiError] = useState('');

    const openCreate = () => { setEditing(null); form.setData(blank); form.clearErrors(); setModal('form'); };
    const openEdit = (p) => {
        setEditing(p);
        form.setData({ title: p.title, category: p.category, excerpt: p.excerpt || '', content: p.content || '', is_published: p.is_published, cover: null });
        form.clearErrors();
        setModal('form');
    };

    const submit = () => {
        const opts = { forceFormData: true, preserveScroll: true, onSuccess: () => setModal(null) };
        if (editing) form.transform((d) => ({ ...d, _method: 'put' })).post(`/dashboard/posts/${editing.id}`, opts);
        else form.transform((d) => d).post('/dashboard/posts', opts);
    };

    const confirmDelete = () => {
        setDeleting(true);
        router.delete(`/dashboard/posts/${toDelete.id}`, { preserveScroll: true, onFinish: () => { setDeleting(false); setToDelete(null); } });
    };

    const onImport = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        setImporting(true);
        router.post('/dashboard/posts/import', { file }, { forceFormData: true, onFinish: () => { setImporting(false); fileRef.current.value = ''; } });
    };

    const setQty = (v) => {
        const n = Math.max(1, Math.min(5, parseInt(v, 10) || 1));
        setAi({ ...ai, quantity: n });
    };

    const generate = async () => {
        setAiError(''); setAiItems([]); setAiLoading(true);
        try {
            const { data } = await axios.post('/dashboard/posts/ai-generate', { ...ai, category: ai.category || null });
            setAiItems(data.items);
        } catch (err) {
            const errors = err.response?.data?.errors;
            setAiError(err.response?.data?.message || (errors && Object.values(errors)[0][0]) || 'Terjadi kesalahan.');
        } finally { setAiLoading(false); }
    };

    const saveAi = () => {
        setAiSaving(true);
        router.post('/dashboard/posts/ai-save', { items: aiItems }, {
            preserveScroll: true,
            onSuccess: () => { setModal(null); setAiItems([]); },
            onFinish: () => setAiSaving(false),
        });
    };

    const toggle = (p) => router.patch(`/dashboard/posts/${p.id}/publish`, {}, { preserveScroll: true });
    const doSearch = (e) => { e.preventDefault(); router.get('/dashboard/posts', { search }, { preserveState: true, replace: true }); };

    const field = 'w-full rounded-lg border border-slate-300 px-3 py-2';

    return (
        <AppLayout>
            <Head title="Artikel Saya" />
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Artikel Saya</h1>
                <div className="flex flex-wrap gap-2">
                    <button onClick={() => { setAiItems([]); setAiError(''); setModal('ai'); }} className="flex items-center gap-2 rounded-lg border border-violet-300 bg-violet-50 px-4 py-2 text-sm font-medium text-violet-700 hover:bg-violet-100"><Sparkles className="h-4 w-4" /> AI Generator</button>
                    <a href="/dashboard/posts/export" className="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"><Download className="h-4 w-4" /> Ekspor Excel</a>
                    <button onClick={() => fileRef.current.click()} disabled={importing} className="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50 disabled:opacity-60">
                        {importing ? <Spinner /> : <Upload className="h-4 w-4" />} Impor Excel
                    </button>
                    <input ref={fileRef} type="file" accept=".xlsx,.xls" className="hidden" onChange={onImport} />
                    <button onClick={openCreate} className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"><Plus className="h-4 w-4" /> Tambah Artikel</button>
                </div>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <Stat icon={FileText} label="Total Artikel" value={stats.total} tone="bg-indigo-50 text-indigo-600" />
                <Stat icon={FileCheck2} label="Total Selesai" value={stats.published} tone="bg-emerald-50 text-emerald-600" />
                <Stat icon={FilePen} label="Belum Selesai" value={stats.draft} tone="bg-amber-50 text-amber-600" />
            </div>

            <form onSubmit={doSearch} className="relative mb-4 max-w-md">
                <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel saya..." className={`${field} pl-9`} />
            </form>

            <div className="divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white">
                {posts.data.length === 0 && <p className="py-12 text-center text-slate-500">Belum ada artikel.</p>}
                {posts.data.map((p) => (
                    <div key={p.id} className="flex items-center gap-3 px-4 py-3">
                        <div className="min-w-0 flex-1">
                            <p className="truncate font-medium">{p.title}</p>
                            <p className="text-xs text-slate-500">{p.category} · dibuat {p.created_at}</p>
                        </div>
                        <span className={`hidden rounded-full px-2.5 py-0.5 text-xs font-medium sm:inline ${p.is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>{p.is_published ? 'Published' : 'Draf'}</span>
                        <button onClick={() => toggle(p)} className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-50">{p.is_published ? 'Jadikan Draf' : 'Publikasikan'}</button>
                        <RowMenu post={p} onEdit={openEdit} onDelete={setToDelete} />
                    </div>
                ))}
            </div>
            <Pagination links={posts.links} />

            {modal === 'form' && (
                <FullScreenModal title={editing ? 'Ubah Artikel' : 'Tambah Artikel'} onClose={() => setModal(null)}
                    footer={<>
                        <button onClick={() => setModal(null)} className="rounded-lg border border-slate-300 bg-white px-5 py-2 font-medium hover:bg-slate-50">Batal</button>
                        <button onClick={submit} disabled={form.processing} className="flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">{form.processing && <Spinner />} Simpan</button>
                    </>}>
                    <div className="space-y-4">
                        {Object.keys(form.errors).length > 0 && (
                            <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <p className="font-medium">Artikel belum bisa disimpan:</p>
                                <ul className="list-disc pl-5">
                                    {Object.values(form.errors).map((message, i) => <li key={i}>{message}</li>)}
                                </ul>
                            </div>
                        )}
                        <div>
                            <label className="mb-1 block text-sm font-medium">Judul</label>
                            <input value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} className={field} />
                            {form.errors.title && <p className="mt-1 text-sm text-red-600">{form.errors.title}</p>}
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm font-medium">Kategori</label>
                                <select value={form.data.category} onChange={(e) => form.setData('category', e.target.value)} className={field}>
                                    {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                                </select>
                                {form.errors.category && <p className="mt-1 text-sm text-red-600">{form.errors.category}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-medium">Foto sampul (opsional)</label>
                                <input type="file" accept="image/*" onChange={(e) => form.setData('cover', e.target.files[0] || null)} className={field} />
                                {form.errors.cover && <p className="mt-1 text-sm text-red-600">{form.errors.cover}</p>}
                            </div>
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium">Ringkasan (1-2 kalimat)</label>
                            <textarea rows={2} value={form.data.excerpt} onChange={(e) => form.setData('excerpt', e.target.value)} className={field} />
                            {form.errors.excerpt && <p className="mt-1 text-sm text-red-600">{form.errors.excerpt}</p>}
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium">Konten (Markdown)</label>
                            <MarkdownEditor value={form.data.content} onChange={(v) => form.setData('content', v)} />
                            {form.errors.content && <p className="mt-1 text-sm text-red-600">{form.errors.content}</p>}
                        </div>
                        <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.data.is_published} onChange={(e) => form.setData('is_published', e.target.checked)} /> Publikasikan ke publik</label>
                    </div>
                </FullScreenModal>
            )}

            {modal === 'ai' && (
                <FullScreenModal title="AI Generator Artikel" onClose={() => setModal(null)}
                    footer={<>
                        <button onClick={() => setModal(null)} className="rounded-lg border border-slate-300 bg-white px-5 py-2 font-medium hover:bg-slate-50">Tutup</button>
                        <button onClick={saveAi} disabled={aiItems.length === 0 || aiSaving} className="flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">{aiSaving && <Spinner />} Simpan {aiItems.length || ''} Artikel</button>
                    </>}>
                    <div className="grid gap-4 sm:grid-cols-[1fr_200px_120px]">
                        <div>
                            <label className="mb-1 block text-sm font-medium">Topik</label>
                            <input value={ai.topic} onChange={(e) => setAi({ ...ai, topic: e.target.value })} placeholder="mis. Dasar-dasar Laravel" className={field} />
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium">Kategori</label>
                            <select value={ai.category} onChange={(e) => setAi({ ...ai, category: e.target.value })} className={field}>
                                <option value="">Otomatis</option>
                                {categories.map((c) => <option key={c.value} value={c.value}>{c.value}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium">Jumlah (1-5)</label>
                            <input type="number" min={1} max={5} value={ai.quantity} onChange={(e) => setQty(e.target.value)} className={field} />
                        </div>
                    </div>
                    <button onClick={generate} disabled={aiLoading || !ai.topic.trim()} className="mt-4 flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2.5 font-medium text-white hover:bg-violet-700 disabled:opacity-60">
                        {aiLoading ? <Spinner /> : <Sparkles className="h-4 w-4" />} Hasilkan Artikel
                    </button>
                    {aiError && <p className="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{aiError}</p>}
                    <div className="mt-6 space-y-4">
                        {aiItems.map((it, i) => (
                            <div key={i} className="rounded-xl border border-slate-200 p-4">
                                <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{it.category}</span>
                                <h3 className="mt-2 font-semibold">{it.title}</h3>
                                <p className="mt-1 text-sm text-slate-600">{it.excerpt}</p>
                            </div>
                        ))}
                    </div>
                </FullScreenModal>
            )}

            {toDelete && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-sm rounded-2xl bg-white p-6">
                        <h3 className="text-lg font-semibold">Hapus artikel?</h3>
                        <p className="mt-2 text-sm text-slate-600">“{toDelete.title}” akan dihapus permanen.</p>
                        <div className="mt-6 flex justify-end gap-2">
                            <button onClick={() => setToDelete(null)} className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">Batal</button>
                            <button onClick={confirmDelete} disabled={deleting} className="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-60">{deleting && <Spinner />} Hapus</button>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}