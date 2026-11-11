import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, LogIn, UserPlus } from 'lucide-react';
import AppLayout, { MarkdownViewer } from '../../Layouts/AppLayout';

export default function PostShow({ post }) {
    const { auth } = usePage().props;

    return (
        <AppLayout mode="public">
            <Head title={post.title} />
            <article className="mx-auto max-w-3xl">
                <Link href="/articles" className="mb-6 inline-flex items-center gap-1 text-sm text-indigo-700"><ArrowLeft className="h-4 w-4" /> Kembali ke artikel</Link>
                <span className="block text-sm font-medium text-indigo-700">{post.category}</span>
                <h1 className="mt-2 text-3xl font-extrabold sm:text-4xl">{post.title}</h1>
                <p className="mt-3 text-sm text-slate-500">Oleh {post.author} · {post.published_at}</p>
                {post.cover_url && <img src={post.cover_url} alt={post.title} className="mt-6 w-full rounded-2xl object-cover" />}
                {post.excerpt && <p className="mt-6 border-l-4 border-indigo-500 pl-4 text-lg italic text-slate-600">{post.excerpt}</p>}
                <div className="mt-8"><MarkdownViewer content={post.content} /></div>

                {!auth?.user && (
                    <aside className="mt-12 rounded-2xl bg-indigo-50 p-8 text-center">
                        <h3 className="text-xl font-bold text-indigo-900">Suka tulisan ini? Bergabunglah dengan komunitas kami.</h3>
                        <p className="mt-2 text-sm text-indigo-800">Masuk atau daftar untuk mulai menulis dan menerbitkan artikel Anda sendiri.</p>
                        <div className="mt-5 flex flex-wrap justify-center gap-3">
                            <Link href="/login" className="flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 font-medium text-white hover:bg-indigo-700"><LogIn className="h-4 w-4" /> Halaman Login</Link>
                            <Link href="/register" className="flex items-center gap-2 rounded-lg border border-indigo-300 bg-white px-5 py-2.5 font-medium text-indigo-700 hover:bg-indigo-100"><UserPlus className="h-4 w-4" /> Halaman Register</Link>
                        </div>
                    </aside>
                )}
            </article>
        </AppLayout>
    );
}
