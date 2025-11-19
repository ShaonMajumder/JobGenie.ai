<div style="font-family: Arial, sans-serif; color:#0f172a;">
    <h1 style="font-size:20px; font-weight:600; margin-bottom:16px;">{{ $title }}</h1>
    @foreach($lines as $line)
        <p style="margin-bottom:12px; line-height:1.4;">{{ $line }}</p>
    @endforeach
    @isset($ctaUrl)
        <p style="margin-top:24px;">
            <a href="{{ $ctaUrl }}" style="display:inline-block; padding:10px 18px; background-color:#4f46e5; color:#ffffff; text-decoration:none; border-radius:6px;">
                {{ $ctaLabel ?? 'Open Billing Portal' }}
            </a>
        </p>
    @endisset
</div>
