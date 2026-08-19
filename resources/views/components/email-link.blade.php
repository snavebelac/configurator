@props(['email'])
<a href="mailto:{{ $email }}"
   class="text-ink underline decoration-slate-faint underline-offset-[3px] transition-colors hover:decoration-ink">{{ $email }}</a>
