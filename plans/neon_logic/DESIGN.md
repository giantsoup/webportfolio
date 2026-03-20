```markdown
# Design System Strategy: The Neon Editorial

## 1. Overview & Creative North Star
**Creative North Star: "The Digital Architect’s Nocturne"**

This design system moves away from the "80s arcade" cliché to embrace a high-end, editorial synthwave aesthetic. It is designed for a Full Stack Developer whose work is both technically precise and visually evocative. Instead of chaotic neon flickers, we focus on **Atmospheric Depth** and **Intentional Asymmetry**. 

The "template" look is shattered through the use of high-contrast typography scales—pairing the brutalist geometry of *Space Grotesk* with the hyper-legibility of *Inter*. We treat the screen not as a flat canvas, but as a deep, layered terminal where components hum with subtle "light-leak" glows and glassmorphism, suggesting a sophisticated, modern retro-futurism.

---

## 2. Colors & Surface Logic
The palette is rooted in the deep void of `#0d0d15` (Surface), punctuated by electric pulses of Magenta and Cyan.

*   **The "No-Line" Rule:** Standard 1px borders are strictly prohibited for sectioning. Structural definition must be achieved through background shifts. For example, a `surface-container-low` hero section should transition into a `surface` main content area without a hard line.
*   **Surface Hierarchy & Nesting:** Use the `surface-container` tiers to create a physical sense of "stacking." 
    *   Base Layer: `surface` (#0d0d15)
    *   Information Cards: `surface-container` (#191923)
    *   Nested Code Snippets: `surface-container-high` (#1f1f29)
*   **The "Glass & Gradient" Rule:** To achieve a premium feel, floating navigation or modal elements must use Glassmorphism. Utilize `surface-bright` at 60% opacity with a `backdrop-blur` of 12px.
*   **Signature Textures:** Apply a 20% opacity linear gradient from `primary` (#df8eff) to `secondary` (#00eefc) on large headline spans or primary CTAs to inject "visual soul" into the dark UI.

---

## 3. Typography
We utilize a dual-font system to balance character with professional rigor.

*   **Display & Headlines (Space Grotesk):** These are your "Brand Moments." Use `display-lg` (3.5rem) with tight letter-spacing (-0.02em) to create an authoritative, architectural feel. The geometric nature of Space Grotesk evokes the digital-grid aesthetic without being dated.
*   **Body & Labels (Inter):** All functional text uses Inter. It provides the "Full Stack" professional balance—clean, objective, and highly readable.
*   **Hierarchy as Identity:** Use `label-md` in all-caps with 0.1em letter-spacing for category tags (e.g., "TECH STACK," "YEAR"). This creates a technical, metadata-heavy aesthetic typical of high-end developer consoles.

---

## 4. Elevation & Depth
Depth in this system is a simulation of light emission, not physical shadows.

*   **The Layering Principle:** Place `surface-container-lowest` (#000000) cards inside a `surface-container` (#191923) section. This "recessed" look suggests a terminal window embedded in a dashboard.
*   **Ambient Glows:** Traditional black shadows are replaced by "Ambient Glows." For floating elements, use a shadow with a 30px blur, 0% spread, and 6% opacity of the `primary` (#df8eff) color. This mimics the way a neon tube casts light on a dark wall.
*   **The "Ghost Border" Fallback:** If containment is required for accessibility, use the `outline-variant` (#484751) at 15% opacity. It should feel like a suggestion of an edge, not a cage.
*   **Subtle Grids:** Implement a 40px x 40px background grid using `outline-variant` at 5% opacity. This reinforces the "Architect" persona without distracting from the content.

---

## 5. Components

### Buttons
*   **Primary:** Solid `primary` (#df8eff) background with `on-primary` (#4f006d) text. Use `md` (0.375rem) roundedness. Add a subtle `1px` inner-glow (top-down) using `primary-fixed`.
*   **Secondary:** No background. A "Ghost Border" of `secondary` (#00eefc) at 40% opacity. On hover, the background fills to 10% opacity.

### Cards & Lists
*   **The Rule of Flow:** Forbid divider lines. Use the Spacing Scale—specifically `12` (3rem) or `16` (4rem)—to create rhythmic separation between project items.
*   **Interactive Cards:** On hover, a card should shift from `surface-container` to `surface-container-highest` and gain a 2px "Light Leak" top-border of `tertiary` (#ff6e81).

### Input Fields
*   **The Terminal Input:** Dark backgrounds (`surface-container-lowest`) with `outline-variant` ghost borders. The cursor or focus state should use a `secondary` (#00eefc) glow.

### Additional Signature Components
*   **The "Status Pulse":** A small, circular chip using `secondary` with a CSS "ping" animation to indicate "Available for Work" or "System Online."
*   **Code Blocks:** Use `surface-container-highest` with a `1.5` (0.375rem) padding and `body-sm` typography. No borders; use a soft `tertiary` glow on the left edge.

---

## 6. Do's and Don'ts

### Do:
*   **Use Asymmetry:** Place your `display-lg` headline off-center to create a modern, editorial layout.
*   **Embrace Negative Space:** Let the `background` (#0d0d15) breathe. High-end design is defined by what you omit.
*   **Color-Coded Logic:** Use `secondary` (Cyan) for technical/logical actions and `primary` (Purple/Magenta) for creative/human actions.

### Don't:
*   **Don't Use Pure White:** Never use `#ffffff` for text. Use `on-surface` (#f2effb) to prevent harsh eye strain and maintain the atmospheric mood.
*   **Don't Over-Glow:** Glows should be felt, not seen. If a user can see the "edge" of a glow effect, it is too opaque.
*   **Don't Use Heavy Borders:** Avoid the "Bootstrap" look. If you find yourself reaching for a border, try a 4px background color shift instead.