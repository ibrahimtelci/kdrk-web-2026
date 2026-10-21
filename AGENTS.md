# Project instructions

- For UI work, read `CLAUDE.md` and `UI_STANDARDS_V2.md` before editing. They define the shared component contract.
- The active application for this task is `kdrk_v2/` with public root `public_htm_v2/`. Preserve the original V1 folders unless the user explicitly requests V1 work.
- Reuse `admin/components.css`, `admin/ui.js`, the icon registry and the shared list partials. Add semantic variants to the common layer instead of introducing a separate design per page.
- Follow the reference layout in `tools_v2/design-reference/image-10.png` while preserving KDRK corporate colors: use the existing `--accent` orange for primary actions and `--accent-2` blue for selection; do not substitute purple/indigo hues. Keep calm light surfaces, clear density, and the same hierarchy in dark mode. Responsive navigation and touch behavior are part of the shared contract.
- Every tab needs a meaningful icon and a clearly selected state. List filters/search/create actions and form save/cancel/delete actions follow the positions in `UI_STANDARDS_V2.md`.
- Preserve authorization, CSRF, validation, modal completion and existing unsaved-change guards during UI changes.
- Preserve unrelated working-tree changes. Validate PHP/JS syntax and the affected UI before reporting completion. Do not create duplicate tests solely for cosmetic changes.
