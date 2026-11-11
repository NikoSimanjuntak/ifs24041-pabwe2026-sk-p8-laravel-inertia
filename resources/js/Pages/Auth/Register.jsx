import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout, { Spinner } from '../../Layouts/AppLayout';

const fields = [
    ['name', 'Nama lengkap', 'text'],
    ['username', 'Username', 'text'],
    ['email', 'Email', 'email'],
    ['password', 'Password', 'password'],
    ['password_confirmation', 'Konfirmasi password', 'password'],
];

export default function Register() {
    const form = useForm({ name: '', username: '', email: '', password: '', password_confirmation: '' });
    const submit = (e) => { e.preventDefault(); form.post('/register'); };

    return (
        <AppLayout mode="public">
            <Head title="Daftar" />
            <div className="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <h1 className="mb-1 text-2xl font-bold">Daftar Penulis</h1>
                <p className="mb-6 text-sm text-slate-500">Buat akun dan mulai menerbitkan artikel.</p>
                <form onSubmit={submit} className="space-y-4">
                    {fields.map(([key, label, type]) => (
                        <div key={key}>
                            <label htmlFor={`register-${key}`} className="mb-1 block text-sm font-medium">{label}</label>
                            <input id={`register-${key}`} type={type} value={form.data[key]} onChange={(e) => form.setData(key, e.target.value)}
                                className="w-full rounded-lg border border-slate-300 px-3 py-2" />
                            {form.errors[key] && <p className="mt-1 text-sm text-red-600">{form.errors[key]}</p>}
                        </div>
                    ))}
                    <button type="submit" disabled={form.processing}
                        className="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                        {form.processing && <Spinner />} Daftar
                    </button>
                </form>
                <p className="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <Link href="/login" className="font-medium text-indigo-700">Masuk</Link></p>
            </div>
        </AppLayout>
    );
}
