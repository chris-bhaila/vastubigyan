@extends('layouts.marketing')

@section('title', 'Pricing - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="mx-auto max-w-[1120px] px-6 py-16 sm:py-24">
        {{-- Hero --}}
        <div class="grid grid-cols-1 items-center gap-12 md:grid-cols-2 md:gap-16">
            <div>
                <p class="mb-3 text-sm font-bold tracking-wide text-teal uppercase">Pricing</p>

                <h1 class="font-heading text-[clamp(38px,8vw,64px)] leading-[1.05] font-bold text-ink">
                    Choose your Vastu plan.
                </h1>

                <p class="mt-5 max-w-prose text-lg text-muted">
                    Start free with the mobile compass, or unlock detailed Vastu reports with the Premium App and Web + App plans.
                </p>

                <p lang="ne" class="mt-5 font-devanagari font-semibold text-teal">
                    घर, कार्यालयको वास्तु सुख, शान्ति, समृद्धि तथास्तु
                </p>
            </div>

            <div class="flex justify-center">
                <svg viewBox="0 0 400 400" width="360" height="360" role="img" aria-label="Sixteen-zone Vastu compass" style="max-width:100%;height:auto">
                    <circle cx="200" cy="200" r="190" fill="#FFFFFF" stroke="#DCE3DF" stroke-width="2"/>
                    <circle cx="200" cy="200" r="150" fill="none" stroke="#0E4A43" stroke-width="1.5"/>
                    <circle cx="200" cy="200" r="96" fill="none" stroke="#0E4A43" stroke-width="1.5" stroke-dasharray="2 6"/>
                    <g stroke="#0E4A43" stroke-width="1" opacity="0.4">
                        <line x1="200" y1="50" x2="200" y2="350"/>
                        <line x1="200" y1="50" x2="200" y2="350" transform="rotate(22.5 200 200)"/>
                        <line x1="200" y1="50" x2="200" y2="350" transform="rotate(45 200 200)"/>
                        <line x1="200" y1="50" x2="200" y2="350" transform="rotate(67.5 200 200)"/>
                        <line x1="200" y1="50" x2="200" y2="350" transform="rotate(90 200 200)"/>
                        <line x1="200" y1="50" x2="200" y2="350" transform="rotate(112.5 200 200)"/>
                        <line x1="200" y1="50" x2="200" y2="350" transform="rotate(135 200 200)"/>
                        <line x1="200" y1="50" x2="200" y2="350" transform="rotate(157.5 200 200)"/>
                    </g>
                    <polygon points="200,62 218,200 182,200" fill="#D9822B"/>
                    <polygon points="200,338 218,200 182,200" fill="#10231F"/>
                    <circle cx="200" cy="200" r="7" fill="#FFFFFF" stroke="#10231F" stroke-width="3"/>
                    <g font-family="Manrope, system-ui, sans-serif" font-size="17" font-weight="700" fill="#10231F" text-anchor="middle">
                        <text x="200" y="38">N</text>
                        <text x="368" y="206">E</text>
                        <text x="200" y="374">S</text>
                        <text x="32" y="206">W</text>
                    </g>
                </svg>
            </div>
        </div>

        {{-- Plans --}}
        <div class="mt-20 sm:mt-28">
            <h2 class="font-heading text-3xl font-bold text-ink">Plans</h2>
            <p class="mt-2 text-muted">All prices are in Nepalese rupees (Nrs.).</p>

            <div class="mt-10 flex flex-wrap gap-6">
                @foreach ($plans as $plan)
                    <div
                        class="flex min-w-0 flex-1 basis-[300px] flex-col rounded-[20px] p-8 {{ $plan['featured']
                            ? 'relative bg-ink text-dark'
                            : 'border border-line bg-white' }}"
                    >
                        @if ($plan['badge'])
                            <span class="absolute -top-3.5 left-8 rounded-full bg-saffron px-3 py-1 text-[13px] font-bold tracking-[0.06em] text-ink">
                                {{ $plan['badge'] }}
                            </span>
                        @endif

                        <p class="text-xs font-bold tracking-wide uppercase {{ $plan['featured'] ? 'text-dark-muted' : 'text-teal' }}">
                            {{ $plan['label'] }}
                        </p>

                        <h3 class="font-heading mt-2 text-2xl font-bold {{ $plan['featured'] ? 'text-dark' : 'text-ink' }}">
                            {{ $plan['name'] }}
                        </h3>

                        <div class="mt-4">
                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                <span class="font-heading text-xl font-bold whitespace-nowrap {{ $plan['featured'] ? 'text-dark' : 'text-ink' }}">Nrs.</span>
                                <span class="font-heading text-[56px] leading-none font-bold {{ $plan['featured'] ? 'text-dark' : 'text-ink' }}">
                                    {{ number_format($plan['price']) }}
                                </span>
                                @if ($plan['original_price'])
                                    <span class="text-base whitespace-nowrap line-through {{ $plan['featured'] ? 'text-dark-muted' : 'text-muted' }}">
                                        Nrs. {{ number_format($plan['original_price']) }}
                                    </span>
                                @endif
                            </div>

                            @if ($plan['price_note'])
                                <p class="mt-1 text-sm {{ $plan['featured'] ? 'text-dark-muted' : 'text-muted' }}">
                                    {{ $plan['price_note'] }}
                                </p>
                            @endif
                        </div>

                        <hr class="my-6 border-t {{ $plan['featured'] ? 'border-dark-line' : 'border-line' }}">

                        <ul class="mb-8 flex flex-col gap-3 text-[15px] leading-[1.5]">
                            @foreach ($plan['features'] as $feature)
                                <li class="flex items-start gap-2 {{ $plan['featured'] ? 'text-dark-muted' : 'text-muted-strong' }}">
                                    <svg
                                        class="mt-0.5 h-5 w-5 flex-shrink-0 {{ $plan['featured'] ? 'text-saffron' : 'text-teal' }}"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M5 12.5l4.5 4.5L19 7.5"/>
                                    </svg>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a
                            href="{{ $plan['cta_url'] }}"
                            class="mt-auto flex min-h-12 cursor-pointer items-center justify-center rounded-xl px-5 text-center font-bold transition {{ $plan['featured']
                                ? 'bg-saffron text-ink hover:opacity-90'
                                : ($plan['slug'] === 'free'
                                    ? 'border-2 border-ink text-ink hover:bg-ink hover:text-dark'
                                    : 'bg-ink text-dark hover:opacity-90') }}"
                        >
                            {{ $plan['cta_label'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
