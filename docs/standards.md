# Code Standards

This document records the foundational decisions the codebase follows.

## PHP

- Target platform: PHP 8.4 and later.
- All manually written files use `declare(strict_types=1);`.
- Typed and `readonly` properties are preferred for dependencies and object state.
- Dynamic properties are not used.
- New code uses explicit parameter types and return types throughout.

## HTML

- Templates are built on semantic HTML5 elements: `main`, `nav`, `footer`.
- Templates use Bootstrap 5.
- Standard HTML controls and native accessibility attributes are preferred for interactive elements.

## JavaScript

- Source code is written in modern JavaScript (ES2024+) without jQuery.
- Target: modern evergreen browsers.
- Current ECMAScript features are used directly; Babel is only needed for minimal compatibility polyfills via `browserslist`.
- New scripts use standard DOM APIs: `fetch`, `FormData`, `classList`, `URL`.
- Charts use `Chart.js` instead of deprecated libraries.

## Practices

- Do not add deprecated libraries unless strictly necessary.
- When updating, follow the current official recommendations of the tool — not old project patterns.
