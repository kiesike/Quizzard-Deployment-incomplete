@extends('admin.layouts.app')

@section('content')
<div class="space-y-8">
    <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 p-8 text-white shadow-xl">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="mb-2 text-sm font-medium uppercase tracking-widest text-violet-100">Admin Profile</p>
                <h1 class="text-3xl font-bold md:text-4xl">Manage Your Profile</h1>
                <p class="mt-2 max-w-2xl text-sm text-violet-100 md:text-base">
                    Update your administrator details and personalize your account with a profile picture.
                </p>
            </div>

            <div class="rounded-2xl bg-white/15 px-5 py-4 backdrop-blur-md">
                <p class="text-sm text-violet-100">Signed in as</p>
                <p class="text-lg font-semibold">{{ $admin->name }}</p>
                <p class="text-sm text-violet-100">{{ $admin->email }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="rounded-3xl bg-white p-8 shadow-lg ring-1 ring-slate-200">
                <div class="flex flex-col items-center text-center">
                    <img
                        id="profilePreview"
                        src="{{ $admin->profile_image ? asset('storage/' . $admin->profile_image) : asset('images/default-avatar.png') }}"
                        alt="Profile Picture"
                        class="h-36 w-36 rounded-full object-cover ring-4 ring-violet-100 shadow-md"
                    >

                    <h2 class="mt-5 text-2xl font-bold text-slate-800">{{ $admin->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $admin->email }}</p>

                    <div class="mt-4 inline-flex rounded-full bg-violet-100 px-4 py-1 text-sm font-semibold text-violet-700">
                        Administrator
                    </div>

                    <div class="mt-8 w-full rounded-2xl bg-slate-50 p-4 text-left">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Account Information</p>

                        <div class="mt-4 space-y-3">
                            <div>
                                <p class="text-xs text-slate-400">Name</p>
                                <p class="font-medium text-slate-700">{{ $admin->name }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">Email</p>
                                <p class="font-medium text-slate-700">{{ $admin->email }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">Role</p>
                                <p class="font-medium capitalize text-slate-700">{{ $admin->role ?? 'admin' }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">Status</p>
                                <p class="font-medium capitalize text-slate-700">{{ $admin->status ?? 'active' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-3xl bg-white p-8 shadow-lg ring-1 ring-slate-200">
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-slate-800">Edit Profile</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Keep your administrator account details up to date.
                    </p>
                </div>

                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Full Name</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $admin->name) }}"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-700 shadow-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100"
                                required
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Email Address</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $admin->email) }}"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-700 shadow-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Profile Picture</label>
                        <input
                            type="file"
                            name="profile_image"
                            id="profileImageInput"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-violet-600 file:px-4 file:py-2 file:font-medium file:text-white hover:file:bg-violet-700"
                        >
                        <p class="mt-2 text-xs text-slate-400">Accepted formats: JPG, JPEG, PNG, WEBP. Max size: 2MB.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">New Password</label>
                            <input
                                type="password"
                                name="password"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-700 shadow-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100"
                                placeholder="Leave blank to keep current password"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Confirm New Password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-700 shadow-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100"
                                placeholder="Confirm new password"
                            >
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-6 py-3 font-semibold text-white shadow-md transition hover:bg-violet-700"
                        >
                            Save Changes
                        </button>

                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('profileImageInput');
        const preview = document.getElementById('profilePreview');

        if (input && preview) {
            input.addEventListener('change', function (event) {
                const file = event.target.files[0];

                if (file) {
                    preview.src = URL.createObjectURL(file);
                }
            });
        }
    });
</script>
@endpush