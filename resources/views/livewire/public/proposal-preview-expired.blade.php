<div>
    <x-auth-shell
        eyebrow="Proposal access"
        heading="This share link has expired."
        lede="The sender set an expiry on this proposal that has now passed. Reach out to them for a fresh link.">

        <div class="flex flex-col items-center gap-3 text-center text-[13.5px] text-slate">
            <x-phosphor-clock-counter-clockwise class="size-8 text-slate-faint" />
            <p>Once the sender refreshes the link, you&rsquo;ll be able to view the proposal again.</p>
        </div>
    </x-auth-shell>
</div>
