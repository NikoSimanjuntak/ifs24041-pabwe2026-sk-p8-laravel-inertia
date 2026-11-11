import { Head, useForm } from '@inertiajs/react';
import AppLayout, { Spinner } from '../../Layouts/AppLayout';

const fields = [
    ['name', 'Nama lengkap', 'text'], ['username', 'Username', 'text'], ['email', 'Email', 'email'],
    ['password', 'Password baru (opsional)', 'password'], ['password_confirmation', 'Konfirmasi password baru', 'password'],
];

export default function Edit({ profile }) {
    const form = useForm({ ...profile, password: '', password_confirmation: '' });

    return (
        <AppLayout>
            <Head title="Profil" />
            <h1 className="mb-6 text-2xl font-bold">Profil Penulis</h1>
            <form onSubmit={(e) => { e.preventDefault(); form.put('/profile', { preserveScroll: true }); }} className="max-w-lg space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                {fields.map(([key, label, type]) => (
                    <div key={key}>
                        <label className="mb-1 block text-sm font-medium">{label}</label>
                        <input type={type} value={form.data[key]} onChange={(e) => form.setData(key, e.target.value)} className="w-full rounded-lg border border-slate-300 px-3 py-2" />
                        {form.errors[key] && <p className="mt-1 text-sm text-red-600">{form.errors[key]}</p>}
                    </div>
                ))}
                <button disabled={form.processing} className="flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                    {form.processing && <Spinner />} Simpan Perubahan
                </button>
            </form>
        </AppLayout>
    );
}
