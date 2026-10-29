<x-layouts::public :title="__('Contact')" :$settings>
    <section aria-labelledby="contact-title" class="mx-auto max-w-screen-2xl px-4 py-20 sm:px-8">
        <header class="max-w-3xl">
            <x-public.kicker>Letters · Get in touch</x-public.kicker>
            <h1 id="contact-title" class="mt-4 font-display text-[clamp(3.5rem,9vw,8rem)] leading-[0.85] font-black uppercase">Write in.</h1>
            <p class="mt-6 text-lg leading-relaxed text-portfolio-fog">Reach out for relevant introductions, product conversations, or thoughtful professional inquiries.</p>
            <p class="mt-6 font-ui text-xs font-bold tracking-[0.14em] text-portfolio-signal uppercase">{{ $settings->hero_kicker }}</p>
        </header>

        @if (session('status'))
            <div role="status" class="mt-10 border-l-4 border-portfolio-signal bg-portfolio-night-raised p-6">{{ session('status') }}</div>
        @endif

        <div class="mt-12 grid items-start gap-12 lg:grid-cols-[minmax(0,8fr)_minmax(0,4fr)]">
            <div class="portfolio-slant-top bg-portfolio-signal px-6 pt-[calc(4vw+3rem)] pb-12 text-portfolio-night sm:px-12 [&_:focus-visible]:outline-portfolio-night">
                @if ($errors->any())
                    <div role="alert" class="mb-8 border-l-4 border-portfolio-night pl-4">
                        <p class="font-ui font-bold">Please check the fields below.</p>
                        <ul class="mt-3 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
                    @csrf
                    <div class="grid gap-1">
                        <label for="name" class="font-ui text-xs font-bold tracking-[0.16em] uppercase">Name</label>
                        <input id="name" name="name" type="text" required maxlength="255" autocomplete="name" value="{{ old('name') }}" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror class="w-full min-w-0 border-0 border-b-2 border-portfolio-night bg-transparent py-2 text-lg font-medium focus:border-b-4">
                        @error('name') <p id="name-error" class="mt-1 text-sm font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-1">
                        <label for="email" class="font-ui text-xs font-bold tracking-[0.16em] uppercase">Email</label>
                        <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="w-full min-w-0 border-0 border-b-2 border-portfolio-night bg-transparent py-2 text-lg font-medium focus:border-b-4">
                        @error('email') <p id="email-error" class="mt-1 text-sm font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-1">
                        <label for="company" class="font-ui text-xs font-bold tracking-[0.16em] uppercase">Company <span class="font-medium tracking-normal normal-case">(optional)</span></label>
                        <input id="company" name="company" type="text" maxlength="255" autocomplete="organization" value="{{ old('company') }}" @error('company') aria-invalid="true" aria-describedby="company-error" @enderror class="w-full min-w-0 border-0 border-b-2 border-portfolio-night bg-transparent py-2 text-lg font-medium focus:border-b-4">
                        @error('company') <p id="company-error" class="mt-1 text-sm font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-1">
                        <label for="project_type" class="font-ui text-xs font-bold tracking-[0.16em] uppercase">Project type <span class="font-medium tracking-normal normal-case">(optional)</span></label>
                        <input id="project_type" name="project_type" type="text" maxlength="255" value="{{ old('project_type') }}" @error('project_type') aria-invalid="true" aria-describedby="project_type-error" @enderror class="w-full min-w-0 border-0 border-b-2 border-portfolio-night bg-transparent py-2 text-lg font-medium focus:border-b-4">
                        @error('project_type') <p id="project_type-error" class="mt-1 text-sm font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-1 sm:col-span-2">
                        <label for="message" class="font-ui text-xs font-bold tracking-[0.16em] uppercase">Message</label>
                        <textarea id="message" name="message" rows="5" required minlength="20" maxlength="5000" aria-describedby="message-hint{{ $errors->has('message') ? ' message-error' : '' }}" @error('message') aria-invalid="true" @enderror class="w-full min-w-0 resize-y border-0 border-b-2 border-portfolio-night bg-transparent py-2 text-lg font-medium focus:border-b-4">{{ old('message') }}</textarea>
                        <p id="message-hint" class="mt-1 text-sm">At least 20 characters, so I can respond usefully.</p>
                        @error('message') <p id="message-error" class="mt-1 text-sm font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <x-public.button variant="night">Transmit message →</x-public.button>
                    </div>
                </form>
            </div>

            <aside aria-labelledby="contact-details-title" class="border-t-4 border-portfolio-signal bg-portfolio-night-raised p-6">
                <h2 id="contact-details-title" class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-signal uppercase">Elsewhere</h2>
                <ul class="mt-6 grid gap-4 font-ui text-sm font-bold tracking-[0.12em] uppercase">
                    @foreach (['github_url' => 'GitHub', 'linkedin_url' => 'LinkedIn', 'x_url' => 'X'] as $field => $label)
                        @if (filled($settings->$field))
                            <li><a href="{{ $settings->$field }}" target="_blank" rel="noreferrer" class="underline decoration-2 underline-offset-4 hover:text-portfolio-signal">{{ $label }} ↗</a></li>
                        @endif
                    @endforeach
                </ul>
                <dl class="mt-8 grid gap-6">
                    @if (filled($settings->location_label))
                        <x-public.fact label="Location">{{ $settings->location_label }}</x-public.fact>
                    @endif
                    @if (filled($settings->schedule_label))
                        <x-public.fact label="Schedule">{{ $settings->schedule_label }}</x-public.fact>
                    @endif
                </dl>
            </aside>
        </div>
    </section>
</x-layouts::public>
