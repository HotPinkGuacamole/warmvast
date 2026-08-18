/**
 * Warmvast — site interactions.
 * Mobile nav, dropdown accessibility, sticky-header state, CTA tracking.
 */
(function () {
	"use strict";

	var doc = document;
	var root = doc.documentElement;

	var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	/* ---------- sticky header shadow + scroll progress + parallax ---------- */
	var header = doc.querySelector("[data-header]");
	var progress = doc.getElementById("scrollProgress");
	var orb1 = doc.querySelector(".hero__orb--1");
	var orb2 = doc.querySelector(".hero__orb--2");
	var ticking = false;

	var amb = { tx: 0, ty: 0, cx: 0, cy: 0 };
	var ambRunning = false;

	function onScroll() {
		var y = window.scrollY || window.pageYOffset;
		if (header) header.classList.toggle("is-scrolled", y > 8);
		if (progress) {
			var docH = doc.documentElement.scrollHeight - window.innerHeight;
			progress.style.transform = "scaleX(" + (docH > 0 ? Math.min(1, y / docH) : 0) + ")";
		}
		ticking = false;
	}
	function requestScroll() { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }
	onScroll();
	window.addEventListener("scroll", requestScroll, { passive: true });

	/* Continuous ambient haze: a slow autonomous Lissajous drift (warm, organic —
	   each element on its own long period so they never move in lockstep) plus a
	   subtle scale "breathing", combined with the smoothed pointer offset. Drives
	   the hero orbs directly and the card / CTA-band glows via CSS custom
	   properties. Pauses when the tab is hidden; skipped for reduced-motion. */
	function ambientLoop() {
		amb.cx += (amb.tx - amb.cx) * 0.075;   // floaty pointer follow
		amb.cy += (amb.ty - amb.cy) * 0.075;
		var s = performance.now() / 1000;
		var y = window.scrollY || window.pageYOffset || 0;
		if (y < 1400) {
			if (orb1) orb1.style.transform =
				"translate3d(" + (amb.cx * 90 + Math.sin(s * 0.24) * 46).toFixed(1) + "px," +
				(y * -0.06 + amb.cy * 62 + Math.cos(s * 0.31 + 1.3) * 34).toFixed(1) + "px,0) scale(" +
				(1 + Math.sin(s * 0.20 + 0.5) * 0.04).toFixed(3) + ")";
			if (orb2) orb2.style.transform =
				"translate3d(" + (amb.cx * -104 + Math.sin(s * 0.19 + 2.1) * 54).toFixed(1) + "px," +
				(y * 0.10 + amb.cy * -54 + Math.cos(s * 0.27 + 3.7) * 38).toFixed(1) + "px,0) scale(" +
				(1 + Math.sin(s * 0.17 + 2.0) * 0.05).toFixed(3) + ")";
		}
		root.style.setProperty("--wv-mx", amb.cx.toFixed(4));
		root.style.setProperty("--wv-my", amb.cy.toFixed(4));
		root.style.setProperty("--wv-fx", Math.sin(s * 0.16 + 0.6).toFixed(4));   // card float
		root.style.setProperty("--wv-fy", Math.cos(s * 0.21 + 2.4).toFixed(4));
		root.style.setProperty("--wv-gx", Math.sin(s * 0.13 + 4.2).toFixed(4));   // cta float
		root.style.setProperty("--wv-gy", Math.cos(s * 0.18 + 1.1).toFixed(4));
		if (!doc.hidden) { requestAnimationFrame(ambientLoop); } else { ambRunning = false; }
	}
	function startAmbient() { if (!ambRunning && !reduce) { ambRunning = true; requestAnimationFrame(ambientLoop); } }

	if (!reduce) {
		startAmbient();
		doc.addEventListener("visibilitychange", function () { if (!doc.hidden) startAmbient(); });
		if (window.matchMedia && window.matchMedia("(pointer: fine)").matches) {
			window.addEventListener("mousemove", function (e) {
				amb.tx = (e.clientX / window.innerWidth - 0.5) * 2;   // -1 .. 1
				amb.ty = (e.clientY / window.innerHeight - 0.5) * 2;
			}, { passive: true });
		}
	}

	/* ---------- hero: reserve exact space for the floating USP bar ----------
	   .usp-bar floats via position:absolute near the hero's bottom edge (see
	   CSS) so it stays visible without scrolling. .hero__inner reserves space
	   for it via padding-bottom, but the bar's real height depends on wrapped
	   text (region name, item labels) that varies by viewport width and
	   content -- a static CSS guess drifts out of sync with the bar's actual
	   size and the bar ends up overlapping tall woningscan states. Measure it
	   and feed the exact number back as a CSS var instead of guessing. Only
	   meaningful >960px, where the bar is absolutely positioned at all -- below
	   that it sits in normal flow (see .usp-bar mobile rule), so the var is
	   cleared and the fallback clamp() in the CSS applies. */
	var uspBar = doc.querySelector(".usp-bar");
	var heroInner = doc.querySelector(".hero__inner");
	if (uspBar && heroInner && "ResizeObserver" in window) {
		var uspDesktopMq = window.matchMedia("(min-width: 961px)");
		var syncUspClearance = function () {
			if (!uspDesktopMq.matches) {
				heroInner.style.removeProperty("--usp-clearance");
				return;
			}
			var offset = parseFloat(getComputedStyle(uspBar).bottom) || 0;
			heroInner.style.setProperty("--usp-clearance", (offset + uspBar.offsetHeight + 56) + "px");
		};
		new ResizeObserver(syncUspClearance).observe(uspBar);
		if (uspDesktopMq.addEventListener) uspDesktopMq.addEventListener("change", syncUspClearance);
		syncUspClearance();
	}

	/* ---------- mobile nav ---------- */
	var toggle = doc.querySelector("[data-nav-toggle]");
	var nav = doc.querySelector("[data-nav]");
	if (toggle && nav) {
		var lockedScrollY = 0;
		var setOpen = function (open) {
			toggle.setAttribute("aria-expanded", String(open));
			nav.classList.toggle("is-open", open);
			doc.body.classList.toggle("nav-open", open);
			// body{overflow:hidden} alone doesn't reliably block touch-drag
			// scrolling on mobile browsers -- the page behind kept scrolling
			// while the menu was open, which also visibly shifted the sticky
			// header (and the fixed nav pinned to it) out of place, showing
			// a gap at the top. Freezing body at its current scroll offset
			// (the standard mobile scroll-lock pattern) blocks it properly;
			// restore the exact position on close so the page doesn't jump.
			if (open) {
				lockedScrollY = window.scrollY || window.pageYOffset || 0;
				doc.body.style.position = "fixed";
				doc.body.style.top = -lockedScrollY + "px";
				doc.body.style.left = "0";
				doc.body.style.right = "0";
			} else {
				doc.body.style.position = "";
				doc.body.style.top = "";
				doc.body.style.left = "";
				doc.body.style.right = "";
				// html has scroll-behavior:smooth for anchor links, which would
				// otherwise turn this restore into a visible glide back to
				// position instead of an instant snap. Force instant just for
				// this jump, then hand scroll-behavior back to the stylesheet.
				var prevScrollBehavior = root.style.scrollBehavior;
				root.style.scrollBehavior = "auto";
				window.scrollTo(0, lockedScrollY);
				root.style.scrollBehavior = prevScrollBehavior;
			}
		};
		toggle.addEventListener("click", function () {
			setOpen(toggle.getAttribute("aria-expanded") !== "true");
		});
		// close on nav link click (mobile)
		nav.addEventListener("click", function (e) {
			var link = e.target.closest("a");
			if (link && window.matchMedia("(max-width: 860px)").matches && !link.closest("[data-dropdown] > a")) {
				setOpen(false);
			}
		});
		// close on escape / outside click
		doc.addEventListener("keydown", function (e) {
			if (e.key === "Escape") setOpen(false);
		});
		doc.addEventListener("click", function (e) {
			if (nav.classList.contains("is-open") && !nav.contains(e.target) && !toggle.contains(e.target)) {
				setOpen(false);
			}
		});
	}

	/* ---------- dropdown: click to toggle on mobile / keyboard ---------- */
	Array.prototype.slice.call(doc.querySelectorAll("[data-dropdown] > a")).forEach(function (trigger) {
		trigger.addEventListener("click", function (e) {
			if (window.matchMedia("(max-width: 860px)").matches) {
				e.preventDefault();
				var parent = trigger.parentElement;
				var dd = parent.querySelector(".dropdown");
				var open = dd.classList.toggle("is-open");
				trigger.setAttribute("aria-expanded", String(open));
			}
		});
	});

	/* ---------- CTA / phone / email tracking ---------- */
	function track(event, data) {
		var payload = data || {};
		payload.event = event;
		if (window.dataLayer && typeof window.dataLayer.push === "function") window.dataLayer.push(payload);
		if (typeof window.gtag === "function") window.gtag("event", event, payload);
		doc.dispatchEvent(new CustomEvent("warmvast:" + event, { detail: payload }));
	}
	doc.addEventListener("click", function (e) {
		var el = e.target.closest("[data-track]");
		if (el) track(el.getAttribute("data-track"), { label: (el.textContent || "").trim().slice(0, 60), href: el.getAttribute("href") || "" });
	});

	/* ---------- count-up numbers ---------- */
	function countUp(el) {
		var target = parseFloat(el.getAttribute("data-countup"));
		if (isNaN(target)) return;
		var dec = parseInt(el.getAttribute("data-decimals") || "0", 10);
		if (reduce) { el.textContent = target.toLocaleString("nl-NL", { minimumFractionDigits: dec, maximumFractionDigits: dec }); return; }
		var start = performance.now(), dur = 1100;
		(function frame(now) {
			var t = Math.min(1, (now - start) / dur), e = 1 - Math.pow(1 - t, 3);
			el.textContent = (target * e).toLocaleString("nl-NL", { minimumFractionDigits: dec, maximumFractionDigits: dec });
			if (t < 1) requestAnimationFrame(frame);
			else el.textContent = target.toLocaleString("nl-NL", { minimumFractionDigits: dec, maximumFractionDigits: dec });
		})(start);
	}

	/* ---------- reveal on scroll + trigger count-ups (respects reduced motion) ---------- */
	if ("IntersectionObserver" in window && !reduce) {
		var reveals = doc.querySelectorAll("[data-reveal], [data-countup]");
		if (reveals.length) {
			var ro = new IntersectionObserver(
				function (entries, obs) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							entry.target.classList.add("is-revealed");
							if (entry.target.hasAttribute("data-countup")) countUp(entry.target);
							obs.unobserve(entry.target);
						}
					});
				},
				{ threshold: 0.06, rootMargin: "0px 0px -12% 0px" }
			);
			Array.prototype.slice.call(reveals).forEach(function (el) { ro.observe(el); });
		}
	} else {
		Array.prototype.slice.call(doc.querySelectorAll("[data-reveal]")).forEach(function (el) { el.classList.add("is-revealed"); });
		Array.prototype.slice.call(doc.querySelectorAll("[data-countup]")).forEach(countUp);
	}
})();
