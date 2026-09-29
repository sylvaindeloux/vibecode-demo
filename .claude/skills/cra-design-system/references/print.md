# Printable CRA — rules

The CRA is a dedicated HTML page (route `app_cra`, e.g. `/cra/{client}/{month}`) that the user prints to PDF from the browser ("Imprimer" → "Enregistrer au format PDF"). There is **no server-side PDF generation**. The page must look like a sheet of paper on screen and print exactly that sheet, on A4 portrait, in Chrome/Edge, Firefox and Safari.

Files: `templates/examples/cra_print.html.twig`, `templates/components/Cra/*.html.twig`, `assets/styles/components/cra-document.css` (sheet, screen + print), `assets/styles/print.css` (`@page` + `@media print`, loaded in the last cascade layer).

## Page structure

```twig
{% extends 'base.html.twig' %}
{% block title %}CRA - {{ … }} - {{ cra.monthLabel }}{% endblock %}
{% block body %}
    <div class="cra-screen">
        <twig:Cra:Toolbar :backUrl="…" :title="…" />   {# screen only #}
        <twig:Cra:Help />                                {# screen only #}
        <twig:Cra:Sheet :cra="cra" />                    {# screen + print #}
    </div>
{% endblock %}
```

### `<title>` = PDF file name

Browsers propose the page title as the PDF file name. Always set it to `CRA - {client} - {Mois AAAA}` (e.g. `CRA - Pharmacie Lumière - Novembre 2026`):

- month capitalised (`ucfirst` of IntlDateFormatter `LLLL y`);
- characters invalid in file names (`/ \ :`) replaced by `-` in the client name;
- no app name suffix on this page.

### Data (`cra` view model)

| Key | Example |
|---|---|
| `monthLabel` | `'Novembre 2026'` |
| `periodStart`, `periodEnd` | `'2026-11-01'`, `'2026-11-30'` |
| `mission` | `'Refonte du back-office'` |
| `freelancer` | `{name, company, siret, address (multi-line), email}` — from the Profile; `siret` is 14 digits without spaces |
| `client` | `{name, address, contactName, contactEmail}` |
| `days` | `[{date: '2026-11-02', quantity: 1, note: ''}, …]` — `full` (1) and `half` (0.5) days only, chronological |
| `total` | `11.5` (sum of quantities) |
| `generatedAt` | `'2026-11-30'` |

## Sheet content (in order)

1. **Header**: eyebrow "COMPTE RENDU D’ACTIVITÉ" (`color-paper-accent`, caps, `letter-spacing-caps`), month as `<h1>` (`font-size-print-heading` 16pt); right: Mission, Période (dd/MM/yyyy – dd/MM/yyyy), Jours travaillés, Établi le. 2px black rule under.
2. **Parties**: two framed blocks side by side — Prestataire (name, company, address, SIRET in mono and in groups of 3, 3, 3 and 5 digits: `812 345 678 00013`, email) and Client (name, address, contact).
3. **Days table** (`Cra:DaysTable`): Date (mono, dd/MM/yyyy) · Jour (capitalised weekday) · Temps (j) (mono, right-aligned, `1` / `0,5`) · Commentaire (note, muted). Header row with 2px bottom rule; light rules between rows; **total row** in `tfoot` (2px top rule, 12pt bold, `22,5 jours travaillés`). Empty month: one row "Aucun jour travaillé ce mois-ci."
4. **Signatures**: two columns "Le prestataire" / "Le client", name, box `size-signature-height` (20mm) with `color-paper-rule` border, hint "Date, nom et signature".

A full month (23 rows including worked weekends) fits on **one** A4 page; longer content flows onto page 2 with the table header repeated.

## Screen preview

- `.cra-screen`: toolbar (Back ghost button, document name, PrintButton), help panel (`color-info-*`), then the sheet.
- `.cra-sheet`: `size-sheet-width` (210mm) × min `size-sheet-height` (297mm), padding `size-sheet-padding` (18mm ≈ `@page` margin), `color-paper-bg`, `elevation-overlay`, square corners. It uses **only `color-paper-*` tokens and print font sizes (pt)**, so it stays white with black text in dark theme and matches the printout.
- Help text (`Cra:Help`): click the button; choose "Enregistrer au format PDF"; in "Plus de paramètres" uncheck "En-têtes et pieds de page"; save (the file name is already right).
- Mobile: toolbar title hidden, sheet reflows to the screen width (min-height removed, padding `space-inset-md`, parties and signatures stacked, table scrolls horizontally if needed).

## Print stylesheet (`print.css`)

```css
@page { size: A4 portrait; margin: 15mm; }   /* size-print-margin; literal: @page cannot read var() */
@media print { … }
```

Rules, all required:

- **Hide everything but the sheet**: `.skip-link`, `.app-header`, `.app-nav`, `.toast-stack`, `.page-header`, and any `[data-print-hide]` (toolbar, help, PrintButton, selection bars, dialogs). New screen-only UI on this page must carry `data-print-hide`.
- **Reset containers**: `.page` and `.cra-screen` become plain blocks without max-width, margin or padding; `.cra-sheet` loses width, min-height, padding, border and shadow (the `@page` margin replaces its padding).
- **Force the light theme**: dark tokens only exist under `@media screen` (tokens.css), the sheet uses theme-independent `color-paper-*`, `html, body` get `color-paper-bg` / `color-paper-text`.
- **Black & white**: text is pure black; meaning is carried by text and rules (2px rules for header/total, 1px light rules between rows), never by a fill — `color-paper-fill` may be dropped by "Background graphics: off" and nothing is lost.
- **Page breaks**: `break-inside: avoid` (+ `page-break-inside` for Safari) on `.cra-doc__header`, `.cra-parties`, `.cra-party`, `.cra-signatures`, every `.cra-days tr`; `break-after: avoid` on headings; `.cra-signatures` `break-before: avoid` so the total row and signatures stay together; `thead { display: table-header-group }` (repeated on each page), `tfoot { display: table-row-group }` (total printed once, at the end).
- Links print as plain text.

## Typography for print

| Element | Token | Value |
|---|---|---|
| Month title | `font-size-print-heading` | 16pt bold |
| Party names, total row | `font-size-print-subheading` | 12pt bold |
| Table, body | `font-size-print-body` | 10pt, `line-height-print` 1.35 |
| Header meta, party blocks, signatures | `font-size-print-meta` | 9pt |
| Role labels, hints | `font-size-print-small` | 8pt |
| Dates, quantities, SIRET | `font-family-numeric` + tabular nums | |

Physical units (pt, mm) are allowed **only** through these tokens.

## Browser compatibility

| Feature | Chrome/Edge | Firefox | Safari |
|---|---|---|---|
| `@page { size: A4 portrait }` | yes | yes | recent versions; older ones ignore it and use the paper size chosen in the dialog (A4 by default in France) |
| `@page` margin | yes | yes | yes |
| `break-inside: avoid` on blocks | yes | yes | yes (`page-break-inside` fallback kept) |
| `break-inside: avoid` on `<tr>` | yes | yes | partial: rows are single-line, so they rarely split |
| repeated `thead` | yes | yes | yes |
| Headers/footers checkbox | "Plus de paramètres" → "En-têtes et pieds de page" | "Imprimer les en-têtes et pieds de page" | "Imprimer les en-têtes et pieds de page" in the details |

Why not `@page { margin: 0 }` (which hides browser headers in Chrome)? It removes the margin of every page after the first. The help panel asks the user to untick headers instead.

## Checklist for any change to the CRA page

Verify **both** the screen and the print preview (Ctrl/⌘+P) — required by SKILL.md:

1. Screen, light and dark: the sheet is white, readable, toolbar and help visible; mobile width reflows.
2. Print preview (Chrome at least; Firefox/Safari when touching `print.css`): only the sheet; A4 portrait; nothing cut at the right edge; one page for a full month (use the dev screen `/_design-system/cra`); no row, party block or signature zone split; black-and-white readable with "Background graphics" off.
3. The proposed PDF file name equals `CRA - {client} - {Mois AAAA}`.

Automatable with Playwright: `page.emulateMedia({media: 'print'})` + `page.pdf({preferCSSPageSize: true})`, then count pages.
