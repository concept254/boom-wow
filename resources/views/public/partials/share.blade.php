{{-- Share buttons. Vars: $url, $title. WhatsApp, Facebook, X, email, copy link, and the phone's native share sheet when available. --}}
@php
    $enc = rawurlencode($url);
    $encTitle = rawurlencode($title);
@endphp
<div x-data="shareLinks(@js($url), @js($title))" class="flex flex-wrap items-center gap-2">
    <span class="mr-1 flex items-center gap-1.5 text-sm font-bold text-brand-graphite"><x-icon name="share" class="h-4 w-4" />Share</span>
    <a href="https://wa.me/?text={{ $encTitle }}%20{{ $enc }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp" title="WhatsApp" class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-offwhite text-brand-graphite hover:bg-green-500 hover:text-brand-white"><x-icon name="whatsapp" class="h-4 w-4" /></a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $enc }}" target="_blank" rel="noopener" aria-label="Share on Facebook" title="Facebook" class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-offwhite text-brand-graphite hover:bg-blue-600 hover:text-brand-white"><x-icon name="facebook" class="h-4 w-4" /></a>
    <a href="https://twitter.com/intent/tweet?text={{ $encTitle }}&amp;url={{ $enc }}" target="_blank" rel="noopener" aria-label="Share on X" title="X" class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-offwhite text-brand-graphite hover:bg-brand-charcoal hover:text-brand-white"><x-icon name="x-social" class="h-4 w-4" /></a>
    <a href="mailto:?subject={{ $encTitle }}&amp;body={{ $encTitle }}%20-%20{{ $enc }}" aria-label="Share by email" title="Email" class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-offwhite text-brand-graphite hover:bg-brand-orange hover:text-brand-white"><x-icon name="mail" class="h-4 w-4" /></a>
    <button type="button" x-on:click="copy()" aria-label="Copy link" title="Copy link" class="flex h-9 items-center justify-center gap-1.5 rounded-full bg-brand-offwhite px-3 text-xs font-bold text-brand-graphite hover:bg-brand-orange hover:text-brand-white"><x-icon name="link" class="h-4 w-4" /><span x-text="copied ? 'Copied!' : 'Copy link'">Copy link</span></button>
    <button type="button" x-cloak x-show="canNative" x-on:click="native()" class="flex h-9 items-center gap-1.5 rounded-full bg-brand-charcoal px-3 text-xs font-bold text-brand-white hover:bg-brand-orange"><x-icon name="share" class="h-4 w-4" />Share&hellip;</button>
</div>
