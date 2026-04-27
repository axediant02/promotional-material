# UI/UX Design Policy

Anti-generic UI/UX design enforcement for frontend work.

Version: 2.0

Use this policy whenever the task involves UI design, UX design, dashboard polish, layout refreshes, visual redesigns, component styling, or frontend art direction.

## Purpose

AI-generated UI has recurring visual tells. This policy names them precisely and defines code-level rules to avoid them.

## 1. Forbidden: Nested / Double-Layer Card Anti-Pattern

Do not place a card-like surface inside another card-like surface when both use similar backgrounds and borders.

Bad:

```jsx
<div className="rounded-xl bg-gray-50 border border-gray-200 p-4">
  <div className="rounded-lg bg-gray-100 border border-gray-200 p-3">
    <p>Content</p>
  </div>
</div>
```

Problem: two nested surfaces with nearly identical treatment create noise instead of hierarchy.

Good:

```jsx
<div className="rounded-2xl border border-[#e5e7eb] bg-white shadow-sm p-5">
  <pre className="rounded-lg bg-[#f8f9fa] border border-[#efefef] p-4 text-sm font-mono">
    Content
  </pre>
</div>
```

Rule:
- Never nest a card inside a card unless the inner surface is a clearly different content type such as code, media, table, or tinted utility block.

## 2. Forbidden: Same-Tone Background + Border

Borders must be visible. A soft background paired with a barely lighter or darker border is not structure.

Bad combinations:
- `bg-slate-50` with `border-slate-100`
- `bg-neutral-50` with `border-neutral-100`
- `bg-blue-50` with `border-blue-100`

Good:

```jsx
<div className="bg-white border border-[#d1d5db] rounded-2xl shadow-sm" />
<span className="bg-blue-50 border border-blue-300 text-blue-800 rounded-full px-2.5 py-0.5 text-xs font-medium" />
```

Rule:
- Border lightness must be at least 10% darker than the background.
- As a practical Tailwind rule, the border should usually be at least two steps darker than the background.

## 3. Forbidden: Emoji Icons in UI

Never use emoji as UI icons.

Bad:

```jsx
<span>[shield emoji] Security</span>
<button>[check emoji] Confirm</button>
```

Good:

```jsx
import { ShieldCheck } from "lucide-react"

<ShieldCheck className="size-4 text-emerald-500" />
```

Rule:
- Use Lucide, Heroicons, or Radix Icons.
- If no exact icon exists, use a close metaphor. Do not fall back to emoji.

## 4. Forbidden: Em Dashes in Interface Copy

Do not use em dashes in rendered UI labels, badges, buttons, card titles, or state lines.

Bad:
- `Status [em dash] Active`
- `Plan [em dash] Pro Tier`

Good:
- `Status: Active`
- separate label/value elements
- visual divider elements instead of punctuation

## 5. Forbidden: Generic Color Abuse and Rainbow Syndrome

Avoid multi-hue card grids and unreasoned color sprawl.

Rules:
- Do not cycle blue, purple, green, red, and orange across peer cards without semantic reason.
- Do not default to purple just because it feels "tech."
- Limit non-semantic color use to one brand hue.
- Allow semantic support colors only when they mean something, typically success and danger.
- Use muted colors for secondary states and reserve saturated color for the primary action.

Required ratio:
- 60% dominant neutral canvas
- 30% secondary neutral or low-brand-tint surfaces
- 10% saturated accent

## 6. Forbidden: Poor Gradient Combinations

Gradients must be restrained and harmonious.

Bad:

```css
linear-gradient(to right, #6366f1, #ec4899, #f97316)
linear-gradient(135deg, purple, blue, green, yellow)
```

Good rules:
- Use one or two colors only.
- Keep hue shifts within roughly 40 degrees when using two colors.
- Large surfaces should use subtle gradients with low contrast.
- Stronger gradients are acceptable for hero accents or buttons only.
- Do not place rainbow gradients behind body text.

## 7. Forbidden: Typography Mismatch

Typography must fit the product type.

Do not:
- use generic default stacks without intent
- combine editorial serifs with utility dashboards unless there is a clear reason
- use thin all-caps body text
- create heading scales with negligible size differences

Required type scale:

```css
--text-xs: 11px;
--text-sm: 13px;
--text-base: 15px;
--text-md: 17px;
--text-lg: 20px;
--text-xl: 24px;
--text-2xl: 30px;
--text-3xl: 40px;
```

Pairing rules:
- Utility tools: geometric sans such as Inter, DM Sans, Plus Jakarta Sans
- Editorial content: serif accent headline with neutral body
- Creative work: expressive display for hero only, neutral elsewhere
- Never mix more than two font families

## 8. Required: Subtle Shadows

Interactive surfaces should not be totally flat, and shadows should not be heavy.

Required shadow tokens:

```css
--shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
--shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
--shadow-md: 0 4px 16px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.04);
--shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.09), 0 4px 8px rgba(0, 0, 0, 0.05);
```

Usage rules:
- cards at rest: `--shadow-sm`
- cards on hover: `--shadow-md`
- modals: `--shadow-lg`
- dropdowns: `--shadow-md`
- buttons: no shadow at rest, `--shadow-xs` on hover

Never use dark opaque shadows such as `rgba(0,0,0,0.3)` on light UI.

## 9. Required: Border Radius Consistency

Use only a defined radius scale.

```css
--radius-xs: 4px;
--radius-sm: 6px;
--radius-md: 10px;
--radius-lg: 14px;
--radius-xl: 18px;
--radius-full: 9999px;
```

Nesting rule:
- outer radius should equal inner radius plus the visual padding gap between them

Examples:
- buttons: `--radius-sm`
- inputs: `--radius-sm`
- small badges: `--radius-xs` to `--radius-sm`
- status pills: `--radius-full`
- cards: `--radius-md` to `--radius-lg`
- modals: `--radius-lg` to `--radius-xl`

Do not mix tiny outer radii with oversized inner radii.

## 10. Required: Minimum Animation Layer

Interactive UI must have motion, but the motion should stay purposeful.

Required transition tokens:

```css
--transition-fast: 100ms ease;
--transition-base: 180ms ease;
--transition-slow: 320ms ease;
```

Global base rule:

```css
button,
a,
[role="button"] {
  transition:
    color var(--transition-base),
    background-color var(--transition-base),
    border-color var(--transition-base),
    box-shadow var(--transition-base),
    transform var(--transition-base),
    opacity var(--transition-base);
}
```

Minimum interaction rules:
- button hover: slight scale plus shadow upgrade
- card hover: `translateY(-2px)` plus shadow upgrade
- focus rings should transition
- accordions should animate open/close rather than jump
- chip hover should shift background only
- loading states should use shimmer or pulse

Do not add heavy JS animation libraries for simple micro-interactions that CSS can handle.

## 11. Required: Global CSS Token System

Reusable visual values must live in `:root` tokens. Avoid inline magic numbers when the value is intended to be reusable.

Reference token set:

```css
:root {
  --color-brand: hsl(245, 78%, 58%);
  --color-brand-light: hsl(245, 78%, 96%);
  --color-brand-dark: hsl(245, 78%, 42%);

  --color-canvas: hsl(40, 20%, 98%);
  --color-surface: hsl(0, 0%, 100%);
  --color-overlay: hsl(220, 14%, 97%);

  --color-text-primary: hsl(220, 15%, 12%);
  --color-text-secondary: hsl(220, 10%, 44%);
  --color-text-muted: hsl(220, 8%, 64%);

  --color-border: hsl(220, 13%, 88%);
  --color-border-strong: hsl(220, 13%, 72%);

  --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.04);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.09), 0 4px 8px rgba(0, 0, 0, 0.05);

  --radius-xs: 4px;
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 14px;
  --radius-xl: 18px;
  --radius-full: 9999px;

  --transition-fast: 100ms ease;
  --transition-base: 180ms ease;
  --transition-slow: 320ms ease;

  --text-xs: 11px;
  --text-sm: 13px;
  --text-base: 15px;
  --text-md: 17px;
  --text-lg: 20px;
  --text-xl: 24px;
  --text-2xl: 30px;
  --text-3xl: 40px;
}
```

Rule:
- If a visual value is meant to repeat across the product, promote it to a token.
- If a one-off value is necessary, it must still respect this policy's contrast, spacing, radius, and hierarchy rules.
