import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout, { Spinner } from '../../Layouts/AppLayout';

export default function Login() {
    const form = useForm({ username: '', password: '', remember: false });
    const submit = (e) => { e.preventDefault(); form.post('/login'); };

    return (
        <AppLayout mode="public">
            <Head title="Masuk" />
            <div className="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <h1 className="mb-1 text-2xl font-bold">Masuk</h1>
                <p className="mb-6 text-sm text-slate-500">Masuk untuk mengelola artikel Anda.</p>
                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label htmlFor="login-username" className="mb-1 block text-sm font-medium">Username</label>
                        <input id="login-username" value={form.data.username} onChange={(e) => form.setData('username', e.target.value)}
                            className="w-full rounded-lg border border-slate-300 px-3 py-2" autoComplete="username" />
                        {form.errors.username && <p className="mt-1 text-sm text-red-600">{form.errors.username}</p>}
                    </div>
                    <div>
                        <label htmlFor="login-password" className="mb-1 block text-sm font-medium">Password</label>
                        <input id="login-password" type="password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)}
                            className="w-full rounded-lg border border-slate-300 px-3 py-2" autoComplete="current-password" />
                        {form.errors.password && <p className="mt-1 text-sm text-red-600">{form.errors.password}</p>}
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input type="checkbox" checked={form.data.remember} onChange={(e) => form.setData('remember', e.target.checked)} /> Ingat saya
                    </label>
                    <button id="login-submit" type="submit" disabled={form.processing}
                        className="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                        {form.processing && <Spinner />} Masuk
                    </button>
                </form>
                <p className="mt-6 text-center text-sm text-slate-500">Belum punya akun? <Link href="/register" className="font-medium text-indigo-700">Daftar sebagai penulis</Link></p>
            </div>
        </AppLayout>
    );
}
