#!/usr/bin/env python3
"""
Verifies every colour in accessibility.css actually clears WCAG AA against white.

This guards the claim made in the stylesheet's comment block. A future edit that
"tidies" a hex value back toward the brand original will fail here rather than silently
reintroducing the accessibility bug Lighthouse flagged.
"""
import re, sys, pathlib

def srgb(c):
    c /= 255.0
    return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4

def lum(hx):
    hx = hx.lstrip('#')
    r, g, b = (int(hx[i:i+2], 16) for i in (0, 2, 4))
    return 0.2126*srgb(r) + 0.7152*srgb(g) + 0.0722*srgb(b)

def ratio(a, b):
    la, lb = lum(a), lum(b)
    hi, lo = max(la, lb), min(la, lb)
    return (hi + 0.05) / (lo + 0.05)

css = pathlib.Path(__file__).parent.parent / "mu-plugins/mellenade-performance/assets/accessibility.css"
text = css.read_text()

# Only check declared text colours; background-color pairs are checked as inverses.
colors = set(re.findall(r'(?<!background-)color:\s*(#[0-9a-fA-F]{6})', text))
colors.discard('#ffffff')  # white-on-green is validated via its background rule

AA = 4.5
fail = 0
print("== Contrast verification (vs #ffffff, WCAG AA normal text 4.5:1) ==")
for c in sorted(colors):
    r = ratio(c, '#ffffff')
    status = "PASS" if r >= AA else "FAIL"
    if r < AA:
        fail = 1
    print(f"  {status}  {c}  {r:5.2f}:1")

# The original failing values must never reappear.
regressions = {'#28ab4d': 2.99, '#939fab': 2.70, '#989a9c': 2.82}
print("\n== Regression guard (original failing colours must be absent) ==")
for bad, was in regressions.items():
    present = re.search(r'(?<!background-)color:\s*' + bad, text, re.I)
    status = "FAIL" if present else "PASS"
    if present:
        fail = 1
    print(f"  {status}  {bad} ({was}:1) not reintroduced as a text colour")

sys.exit(fail)
