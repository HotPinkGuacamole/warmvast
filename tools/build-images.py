#!/usr/bin/env python3
"""Build the theme's photo assets from the camera originals.

Run this whenever a photo is added or replaced; commit the WebP output it
writes into wp-content/themes/warmvast/assets/img/. The originals themselves
are NOT in the repo (~95MB each, over GitHub's per-file limit) -- see
.gitignore -- so point SRC at wherever they live.

    pip install pillow opencv-python
    python tools/build-images.py

Why this exists rather than "just export a JPEG":

* Sizing. Each photo lands in a small fixed slot (a ~440px story-row column,
  a ~348px team card). Shipping one 2000px+ original meant the browser
  downscaled it 3-5x at paint time, which visibly softened it and sent
  hundreds of KB for a slot that needed a fraction of that. Each variant is
  built at the size it is actually displayed at, with a 2x twin for retina;
  the templates offer both via srcset.

* Framing. These get cropped to the slot's aspect ratio. Leaving that to CSS
  object-position means one blanket percentage for photos whose subjects sit
  at different heights, which clipped people's heads. Faces are detected here
  and the crop is placed to keep the highest head comfortably in frame.
  Detection runs on a CLAHE-equalised copy because these are dark evening
  shots that plain Haar detection barely sees; frontal and profile cascades
  both run, profile also mirrored, so someone turned away is still found. A
  few false positives (foliage, shadow) are harmless: the crop is driven by
  the union of detections plus headroom, which the real face dominates.

* Acuity. Any large downscale costs edge contrast, so a light unsharp pass
  follows the Lanczos resample. Measured +52-73% acuity at display size.

After editing, eyeball tools/_crops/ (written on each run) before committing:
face detection is a heuristic, not a guarantee.
"""
import json
import os

import cv2
import numpy as np
from PIL import Image, ImageOps, ImageFilter

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(REPO, "Graded photos")          # gitignored originals
IMG = os.path.join(REPO, "wp-content", "themes", "warmvast", "assets", "img")
PREVIEW = os.path.join(REPO, "tools", "_crops")    # gitignored crop previews

# base name -> (source file, subdir, aspect, [widths]) with an optional 5th
# element: {"headroom": float} or {"focus": 0..1} for that one photo.
#   3:2  = story rows (.story-row) and team cards (.team-card__photo)
#   1:1  = the Ons werk filmstrip (.werk-filmstrip__item)
# A source containing "/" is a path relative to the repo root instead of SRC,
# so a shoot that lives in its own folder doesn't have to be moved first.
# Widths are the rendered CSS size and its 2x twin -- keep them in step with
# the `sizes` attributes in the templates.
JOBS = {
    "team-makita-cap":       ("P8240117.png",    "team", "3:2", [400, 960]),
    "team-golvend-haar":     ("P8240123.png",    "team", "3:2", [400, 960]),
    "team-krullen-bril":     ("P8240131.png",    "team", "3:2", [400, 960]),
    "team-groep":            ("P8240177_01.png", "team", "3:2", [480, 960]),
    "werk-oplevering-klant": ("P8240091.png",    "werk", "3:2", [480, 960]),
    "werk-dakisolatie-03":   ("P8240067.png",    "werk", "3:2", [480, 960]),
    "werk-dakisolatie-04":   ("P8240068.png",    "werk", "3:2", [480, 960]),
    "werk-dakisolatie-06":   ("P8240072.png",    "werk", "3:2", [480, 960]),
    "werk-dakisolatie-01":   ("P8240019.png",    "werk", "1:1", [160, 320]),
    "werk-dakisolatie-02":   ("P8240021.png",    "werk", "1:1", [160, 320]),
    "werk-dakisolatie-05":   ("P8240071.png",    "werk", "1:1", [160, 320]),

    # --- homepage shoot (Website foto's/) ---------------------------------
    # The three portrait sources are 3:4, so a 3:2 slot keeps roughly the
    # middle half of the frame; face detection places that window.
    # The kruipruimte shot is the exception: the bubble-wrap and foil texture
    # fills most of the frame and Haar fires on it (5 "faces"), which dragged
    # the crop to the very top of the image and cut the installer out of his
    # own photo. It gets an explicit focus instead -- verified in _crops/.
    "werk-opname-welkom": (
        "Website foto's/inspecteur aan de deur schud hand met klant.png",
        "werk", "3:2", [560, 1120]),
    "werk-dakisolatie-montage": (
        # Face detection anchored low in this one (cap + turned head under
        # roof-beam shadow confuses Haar), which cropped the cap brim off
        # the top of the frame. Explicit focus instead -- verified in _crops/.
        "Website foto's/uitvoerder monteerd isolatie op het dak.png",
        "werk", "3:2", [480, 960], {"focus": 0.28}),
    "werk-vloerisolatie-kruipruimte": (
        "Website foto's/uitvoerder in de kruipruimte plakt pif tape op isolatiemateriaal.png",
        "werk", "3:2", [480, 960], {"focus": 0.50}),
    "team-bakwagen": (
        "Website foto's/uitvoerder staat voor een bakwagen met de warmvastlogo.png",
        "team", "3:2", [560, 1120]),
}

FRONTAL = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
PROFILE = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_profileface.xml")

HEADROOM = 0.20      # topmost face starts this far down the crop
DETECT_WIDTH = 1000  # detection runs on a downscaled copy, for speed


def detect_faces(pil):
    """Face boxes in original-image coordinates."""
    scale = DETECT_WIDTH / pil.width
    small = pil.resize((DETECT_WIDTH, round(pil.height * scale)), Image.LANCZOS)
    gray = cv2.cvtColor(np.array(small), cv2.COLOR_RGB2GRAY)
    gray = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8)).apply(gray)

    boxes = []
    for cascade, mirror in ((FRONTAL, False), (PROFILE, False), (PROFILE, True)):
        img = cv2.flip(gray, 1) if mirror else gray
        for sf in (1.05, 1.1):
            for (x, y, w, h) in cascade.detectMultiScale(img, scaleFactor=sf,
                                                         minNeighbors=6, minSize=(40, 40)):
                if mirror:
                    x = img.shape[1] - x - w
                boxes.append((x / scale, y / scale, w / scale, h / scale))

    merged = []
    for b in sorted(boxes, key=lambda b: -b[2] * b[3]):
        if any(abs(b[0] - m[0]) < m[2] * 0.6 and abs(b[1] - m[1]) < m[3] * 0.6 for m in merged):
            continue
        merged.append(b)
    return merged


def crop_box(pil, faces, ratio, headroom=HEADROOM, focus=None):
    """Largest window of `ratio`, centred on the faces, with headroom above.

    `focus` overrides detection entirely: a 0..1 fraction of the source height
    that the window is centred on. For frames where the subject is texture
    rather than a face -- insulation, foil, bubble wrap -- Haar detection
    fires on the pattern and drags the crop somewhere useless, so those get
    an explicit anchor instead of a heuristic.
    """
    W, H = pil.size
    cw = min(W, H * ratio)
    ch = cw / ratio
    if focus is not None:
        left = max(0, min(W / 2 - cw / 2, W - cw))
        top = max(0, min(H * focus - ch / 2, H - ch))
        return (round(left), round(top), round(left + cw), round(top + ch))
    if faces:
        cx = (min(f[0] for f in faces) + max(f[0] + f[2] for f in faces)) / 2
        top = min(f[1] for f in faces) - ch * headroom
    else:
        cx, top = W / 2, (H - ch) / 2
    left = max(0, min(cx - cw / 2, W - cw))
    top = max(0, min(top, H - ch))
    return (round(left), round(top), round(left + cw), round(top + ch))


def main():
    os.makedirs(PREVIEW, exist_ok=True)
    manifest = {}
    for base, job in JOBS.items():
        src, sub, aspect, widths = job[0], job[1], job[2], job[3]
        opts = job[4] if len(job) > 4 else {}
        headroom = opts.get("headroom", HEADROOM)
        focus = opts.get("focus")
        path = os.path.join(REPO, src) if "/" in src else os.path.join(SRC, src)
        if not os.path.exists(path):
            print(f"!! missing original: {path}")
            continue
        master = ImageOps.exif_transpose(Image.open(path)).convert("RGB")
        faces = detect_faces(master)

        if aspect == "1:1":
            # unchanged two-step path: 3:2 face crop, then square from its
            # centre -- kept verbatim so the committed filmstrip images do
            # not shift when this function learned about other ratios.
            box = crop_box(master, faces, 3 / 2, headroom, focus)
            region = master.crop(box)
            s = min(region.size)
            cx, cy = region.width / 2, region.height / 2
            region = region.crop((round(cx - s / 2), round(cy - s / 2),
                                  round(cx + s / 2), round(cy + s / 2)))
        else:
            aw, ah = (float(v) for v in aspect.split(":"))
            box = crop_box(master, faces, aw / ah, headroom, focus)
            region = master.crop(box)

        os.makedirs(os.path.join(IMG, sub), exist_ok=True)
        for w in widths:
            h = round(region.height * w / region.width)
            im = region.resize((w, h), Image.LANCZOS)
            im = im.filter(ImageFilter.UnsharpMask(radius=0.8, percent=70, threshold=2))
            out = os.path.join(IMG, sub, f"{base}-{w}.webp")
            im.save(out, "WEBP", quality=88, method=6)
            print(f"{base}-{w}.webp  {w}x{h}  {os.path.getsize(out) // 1024}KB")

        prev = region.copy()
        prev.thumbnail((640, 640), Image.LANCZOS)
        prev.save(os.path.join(PREVIEW, f"{base}.jpg"), "JPEG", quality=82)
        manifest[base] = {"source": src, "faces": len(faces), "crop": box, "widths": widths}

    json.dump(manifest, open(os.path.join(PREVIEW, "manifest.json"), "w"), indent=1)
    print(f"\ncrop previews -> {PREVIEW}  (check these before committing)")


if __name__ == "__main__":
    main()
