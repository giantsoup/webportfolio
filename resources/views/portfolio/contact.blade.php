<x-layouts::public :title="__('Contact')" :$settings>
    <section class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
        <div class="max-w-3xl space-y-6">
            <x-public.status-pulse :text="$settings->hero_kicker" />
            <h1 class="font-headline text-5xl font-bold tracking-tight text-portfolio-copy md:text-7xl">
                Get in <span class="portfolio-text-gradient">Touch</span>
            </h1>
            <p class="text-lg leading-8 text-portfolio-copy-muted">
                Reach out for relevant introductions, product conversations, or thoughtful professional inquiries.
            </p>
        </div>

        <div class="mt-14 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="portfolio-panel p-8 md:p-10">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-portfolio-secondary/30 bg-portfolio-secondary/10 px-4 py-3 text-sm text-portfolio-copy">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="grid gap-2">
                            <span class="font-label text-[10px] uppercase tracking-[0.2em] text-portfolio-copy-muted">Name</span>
                            <input type="text" name="name" value="{{ old('name') }}" class="rounded-2xl border border-portfolio-outline/20 bg-black/40 px-4 py-3 text-portfolio-copy outline-none transition focus:border-portfolio-secondary/60" required>
                            @error('name') <span class="text-sm text-portfolio-tertiary">{{ $message }}</span> @enderror
                        </label>

                        <label class="grid gap-2">
                            <span class="font-label text-[10px] uppercase tracking-[0.2em] text-portfolio-copy-muted">Email</span>
                            <input type="email" name="email" value="{{ old('email') }}" class="rounded-2xl border border-portfolio-outline/20 bg-black/40 px-4 py-3 text-portfolio-copy outline-none transition focus:border-portfolio-secondary/60" required>
                            @error('email') <span class="text-sm text-portfolio-tertiary">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <label class="grid gap-2">
                            <span class="font-label text-[10px] uppercase tracking-[0.2em] text-portfolio-copy-muted">Company</span>
                            <input type="text" name="company" value="{{ old('company') }}" class="rounded-2xl border border-portfolio-outline/20 bg-black/40 px-4 py-3 text-portfolio-copy outline-none transition focus:border-portfolio-secondary/60">
                            @error('company') <span class="text-sm text-portfolio-tertiary">{{ $message }}</span> @enderror
                        </label>

                        <label class="grid gap-2">
                            <span class="font-label text-[10px] uppercase tracking-[0.2em] text-portfolio-copy-muted">Project Type</span>
                            <input type="text" name="project_type" value="{{ old('project_type') }}" class="rounded-2xl border border-portfolio-outline/20 bg-black/40 px-4 py-3 text-portfolio-copy outline-none transition focus:border-portfolio-secondary/60">
                            @error('project_type') <span class="text-sm text-portfolio-tertiary">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <label class="grid gap-2">
                        <span class="font-label text-[10px] uppercase tracking-[0.2em] text-portfolio-copy-muted">Message</span>
                        <textarea name="message" rows="7" class="rounded-2xl border border-portfolio-outline/20 bg-black/40 px-4 py-3 text-portfolio-copy outline-none transition focus:border-portfolio-secondary/60" required>{{ old('message') }}</textarea>
                        @error('message') <span class="text-sm text-portfolio-tertiary">{{ $message }}</span> @enderror
                    </label>

                    <button type="submit" class="portfolio-button-primary w-full md:w-auto">Transmit Message</button>
                </form>
            </div>

            <div class="space-y-6">
                @if (filled($settings->github_url))
                    <x-public.social-card :href="$settings->github_url" label="GitHub" description="Browse repositories, experiments, and open source work." />
                @endif

                @if (filled($settings->linkedin_url))
                    <x-public.social-card :href="$settings->linkedin_url" label="LinkedIn" description="Professional history, project context, and current role details." />
                @endif

                <div class="rounded-3xl border border-portfolio-outline/20 bg-black/30 p-6">
                    <div class="space-y-3 text-sm uppercase tracking-[0.18em] text-portfolio-copy-muted">
                        @if (filled($settings->location_label))
                            <p>{{ $settings->location_label }}</p>
                        @endif
                        @if (filled($settings->schedule_label))
                            <p>{{ $settings->schedule_label }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts::public>
