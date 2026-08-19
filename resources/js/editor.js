/**
 * Rich-text editor for terms and conditions.
 *
 * TipTap and ProseMirror are ~380 kB, and the only screen that needs them is
 * the terms editor. They are therefore pulled in by dynamic import inside
 * init(), so Vite emits them as a separate chunk that is fetched on demand —
 * every other page, including the public client-facing proposal, stays on the
 * small bundle.
 *
 * The enabled nodes deliberately mirror App\Helpers\HtmlSanitiser's allowlist.
 * Anything enabled here that isn't allowed there would be silently stripped on
 * save, which reads as the editor losing the author's work — so the two lists
 * are kept in step by hand, and neither should be changed alone.
 */
export default function registerRichTextEditor(Alpine) {
    Alpine.data('richText', (initialContent = '', wireModel = null) => {
        // Held outside the returned object on purpose. Alpine wraps component
        // properties in reactive proxies, and a proxied editor instance throws
        // "Range Error: Applying a mismatched transaction" the moment TipTap
        // tries to apply one.
        let editor = null;

        return {
            ready: false,

            // Bumped by TipTap's lifecycle callbacks purely so Alpine
            // re-evaluates the toolbar's active states.
            updatedAt: Date.now(),

            async init() {
                const [{ Editor }, { default: StarterKit }, { default: Link }] = await Promise.all([
                    import('@tiptap/core'),
                    import('@tiptap/starter-kit'),
                    import('@tiptap/extension-link'),
                ]);

                editor = new Editor({
                    element: this.$refs.editor,
                    extensions: [
                        StarterKit.configure({
                            heading: { levels: [2, 3, 4] },
                            // Off: no allowlist entry, and neither belongs in
                            // a terms document.
                            codeBlock: false,
                            code: false,
                        }),
                        Link.configure({
                            openOnClick: false,
                            autolink: true,
                            protocols: ['http', 'https', 'mailto'],
                        }),
                    ],
                    content: initialContent || '',
                    editorProps: {
                        attributes: {
                            class: 'prose-terms min-h-[320px] focus:outline-none',
                        },
                    },
                    onCreate: () => { this.updatedAt = Date.now(); },
                    onSelectionUpdate: () => { this.updatedAt = Date.now(); },
                    onUpdate: () => {
                        this.updatedAt = Date.now();

                        if (wireModel) {
                            // Deliberately not .live — terms are long, and a
                            // round trip per keystroke would be miserable.
                            // The server sanitises whatever arrives on save.
                            this.$wire.set(wireModel, editor.getHTML(), false);
                        }
                    },
                });

                if (wireModel) {
                    this.$watch('$wire.' + wireModel, (value) => {
                        // Only react to changes made server-side (e.g. loading
                        // a different version in), never echo our own.
                        if (editor && value !== editor.getHTML()) {
                            editor.commands.setContent(value || '', false);
                        }
                    });
                }

                this.ready = true;
            },

            destroy() {
                editor?.destroy();
                editor = null;
            },

            isActive(name, attrs = {}) {
                // Touch updatedAt so Alpine knows to re-run this on every
                // selection change.
                return this.updatedAt && editor ? editor.isActive(name, attrs) : false;
            },

            run(command, ...args) {
                editor?.chain().focus()[command](...args).run();
            },

            setLink() {
                const previous = editor?.getAttributes('link').href ?? '';
                const url = window.prompt('Link URL', previous);

                if (url === null) {
                    return;
                }

                if (url === '') {
                    editor?.chain().focus().extendMarkRange('link').unsetLink().run();

                    return;
                }

                editor?.chain().focus().extendMarkRange('link')
                    .setLink({ href: url })
                    .run();
            },
        };
    });
}
