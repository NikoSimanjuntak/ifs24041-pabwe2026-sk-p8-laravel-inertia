import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import {
    BookOpen, FileText, LayoutDashboard, Loader2, LogIn, LogOut, ShieldCheck,
    User as UserIcon, UserPlus, Users, X, CheckCircle2, AlertCircle,
} from 'lucide-react';
import Editor from '@toast-ui/editor';
import Viewer from '@toast-ui/editor/dist/toastui-editor-viewer';
import '@toast-ui/editor/dist/toastui-editor.css';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';

/* ---------- Komponen bersama ---------- */

export function Spinner({ className = 'h-4 w-4' }) {
    return <Loader2 className={`${className} animate-spin`} aria-hidden="true" />;
}

function LoadingPill() {
    const [busy, setBusy] = useState(false);
    useEffect(() => {
        const a = router.on('start', () => setBusy(true));
        const b = router.on('finish', () => setBusy(false));
        return () => { a(); b(); };
    }, []);
    if (!busy) return null;
    return (
        <div role="status" className="fixed bottom-6 left-1/2 z-[100] flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm text-white shadow-lg">
            <Spinner /> Memproses tindakan...
        </div>
    );
}

function Flash() {
    const { flash } = usePage().props;
    const [hidden, setHidden] = useState(false);
    const message = flash?.success || flash?.error;
    useEffect(() => { setHidden(false); }, [message]);
    if (!message || hidden) return null;
    const ok = Boolean(flash.success);
    return (
        <div role="status" className={`mb-4 flex items-start gap-2 rounded-lg border px-4 py-3 text-sm ${ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800'}`}>
            {ok ? <CheckCircle2 className="mt-0.5 h-4 w-4" aria-hidden="true" /> : <AlertCircle className="mt-0.5 h-4 w-4" aria-hidden="true" />}
            <span className="flex-1">{message}</span>
            <button onClick={() => setHidden(true)} aria-label="Tutup pesan"><X className="h-4 w-4" aria-hidden="true" /></button>
        </div>
    );
}

export function Pagination({ links = [] }) {
    if (links.length <= 3) return null;
    return (
        <nav aria-label="Navigasi halaman" className="mt-6 flex flex-wrap justify-center gap-1">
            {links.map((l, i) => (
                <Link key={i} href={l.url || '#'} preserveScroll preserveState
                    aria-current={l.active ? 'page' : undefined}
                    className={`rounded-md border px-3 py-1.5 text-sm ${l.active ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100'} ${l.url ? '' : 'pointer-events-none opacity-40'}`}
                    dangerouslySetInnerHTML={{ __html: l.label }} />
            ))}
        </nav>
    );
}

export function MarkdownViewer({ content }) {
    const ref = useRef(null);
    useEffect(() => {
        const viewer = new Viewer({ el: ref.current, initialValue: content || '' });
        return () => viewer.destroy();
    }, [content]);
    return <div ref={ref} />;
}

export function MarkdownEditor({ value, onChange }) {
    const ref = useRef(null);
    const cb = useRef(onChange);
    cb.current = onChange;
    useEffect(() => {
        const editor = new Editor({
            el: ref.current, height: '420px', initialEditType: 'markdown',
            previewStyle: 'vertical', initialValue: value || '', usageStatistics: false,
        });
        editor.on('change', () => cb.current(editor.getMarkdown()));
        return () => editor.destroy();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);
    return <div ref={ref} />;
}

export function FullScreenModal({ title, onClose, children, footer }) {
    return (
        <div role="dialog" aria-modal="true" aria-label={title} className="fixed inset-0 z-50 flex flex-col bg-white">
            <header className="flex items-center justify-between bg-indigo-700 px-6 py-4 text-white">
                <h2 className="text-lg font-semibold">{title}</h2>
                <button onClick={onClose} aria-label="Tutup" className="rounded p-1 hover:bg-indigo-600"><X className="h-5 w-5" aria-hidden="true" /></button>
            </header>
            <div className="flex-1 overflow-y-auto p-6"><div className="mx-auto max-w-4xl">{children}</div></div>
            <footer className="flex justify-end gap-3 border-t bg-slate-100 px-6 py-4">{footer}</footer>
        </div>
    );
}

/* ---------- Tata letak utama ---------- */

function PublicShell({ children }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    return (
        <div className="flex min-h-screen flex-col">
            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                    <Link href="/" className="flex items-center gap-2 text-lg font-bold text-indigo-700"><BookOpen className="h-5 w-5" aria-hidden="true" /> BlogVerse Pro</Link>
                    <nav aria-label="Navigasi utama" className="flex items-center gap-2 text-sm">
                        <Link href="/" className="hidden px-3 py-2 text-slate-700 hover:text-indigo-700 sm:inline">Beranda</Link>
                        <Link href="/articles" className="hidden px-3 py-2 text-slate-700 hover:text-indigo-700 sm:inline">Artikel</Link>
                        {user ? (
                            <>
                                <Link href={user.role === 'admin' ? '/admin/users' : '/dashboard/posts'} className="flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-2 font-medium text-white hover:bg-indigo-700">
                                    {user.role === 'admin' ? <ShieldCheck className="h-4 w-4" aria-hidden="true" /> : <LayoutDashboard className="h-4 w-4" aria-hidden="true" />}
                                    {user.role === 'admin' ? 'Panel Admin' : 'Dashboard Penulis'}
                                </Link>
                                <span className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700" title={user.name}>{user.name.charAt(0).toUpperCase()}</span>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-2 font-medium hover:bg-slate-100"><LogIn className="h-4 w-4" aria-hidden="true" /> Masuk</Link>
                                <Link href="/register" className="flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-2 font-medium text-white hover:bg-indigo-700"><UserPlus className="h-4 w-4" aria-hidden="true" /> Daftar</Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>
            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8"><Flash />{children}</main>
            <footer className="border-t border-slate-200 bg-white">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-slate-600 sm:flex-row">
                    <p>© {new Date().getFullYear()} BlogVerse Pro — platform publikasi artikel.</p>
                    <nav aria-label="Navigasi footer" className="flex gap-4">
                        <Link href="/" className="hover:text-indigo-700">Beranda Publik</Link>
                        <Link href="/login" className="hover:text-indigo-700">Halaman Login</Link>
                        <Link href="/register" className="hover:text-indigo-700">Halaman Register</Link>
                    </nav>
                </div>
            </footer>
        </div>
    );
}

function SidebarShell({ children }) {
    const { props, url } = usePage();
    const user = props.auth?.user;
    const links = user?.role === 'admin'
        ? [['/admin/users', 'Pengguna', Users], ['/admin/posts', 'Semua Artikel', FileText], ['/profile', 'Profil', UserIcon]]
        : [['/dashboard/posts', 'Artikel Saya', FileText], ['/profile', 'Profil', UserIcon]];
    return (
        <div className="flex min-h-screen flex-col md:flex-row">
            <aside aria-label="Sidebar dashboard" className="flex shrink-0 flex-col bg-slate-900 p-4 text-slate-200 md:min-h-screen md:w-60">
                <Link href="/" className="mb-6 flex items-center gap-2 text-lg font-bold text-white"><BookOpen className="h-5 w-5" aria-hidden="true" /> BlogVerse Pro</Link>
                <nav aria-label="Menu dashboard" className="flex flex-1 flex-row gap-1 overflow-x-auto md:flex-col">
                    {links.map(([href, label, Icon]) => (
                        <Link key={href} href={href} className={`flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 text-sm ${url?.startsWith(href) ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800'}`}>
                            <Icon className="h-4 w-4" aria-hidden="true" /> {label}
                        </Link>
                    ))}
                    <Link href="/" className="flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 text-sm hover:bg-slate-800"><BookOpen className="h-4 w-4" aria-hidden="true" /> Beranda Publik</Link>
                </nav>
                <div className="mt-4 border-t border-slate-700 pt-4 text-sm">
                    <p className="font-medium text-white">{user?.name}</p>
                    <p className="mb-3 text-xs text-slate-300">@{user?.username} · {user?.role}</p>
                    <button onClick={() => router.post('/logout')} className="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-slate-800"><LogOut className="h-4 w-4" aria-hidden="true" /> Keluar</button>
                </div>
            </aside>
            <main className="min-w-0 flex-1 p-4 md:p-8"><Flash />{children}</main>
        </div>
    );
}

/** mode: 'public' (navbar atas, tanpa sidebar) atau 'dashboard' (sidebar). */
export default function AppLayout({ children, mode = 'dashboard' }) {
    return (
        <>
            {mode === 'public' ? <PublicShell>{children}</PublicShell> : <SidebarShell>{children}</SidebarShell>}
            <LoadingPill />
        </>
    );
}