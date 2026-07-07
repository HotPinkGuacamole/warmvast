/**
 * Warmvast — site interactions.
 * Mobile nav, dropdown accessibility, sticky-header state, CTA tracking.
 */
(function () {
	"use strict";

	var doc = document;

	var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	/* ---------- sticky header shadow + scroll progress + parallax ---------- */
	var header = doc.querySelector("[data-header]");
	var progress = doc.getElementById("scrollProgress");
	var heroArt = doc.querySelector(".hero__art");
	var orb1 = doc.querySelector(".hero__orb--1");
	var orb2 = doc.querySelector(".hero__orb--2");
	var ticking = false;
	function onScroll() {
		var y = window.scrollY || window.pageYOffset;
		if (header) header.classList.toggle("is-scrolled", y > 8);
		if (progress) {
			var docH = doc.documentElement.scrollHeight - window.innerHeight;
			progress.style.transform = "scaleX(" + (docH > 0 ? Math.min(1, y / docH) : 0) + ")";
		}
		if (!reduce && y < 1100) {
			if (heroArt) heroArt.style.transform = "translateY(" + (y * 0.15) + "px)";
			// orbs drift apart on scroll for parallax depth (opposite directions)
			if (orb1) orb1.style.transform = "translate3d(0," + (y * -0.06) + "px,0)";
			if (orb2) orb2.style.transform = "translate3d(0," + (y * 0.1) + "px,0)";
		}
		ticking = false;
	}
	function requestScroll() { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }
	onScroll();
	window.addEventListener("scroll", requestScroll, { passive: true });

	/* ---------- mobile nav ---------- */
	var toggle = doc.querySelector("[data-nav-toggle]");
	var nav = doc.querySelector("[data-nav]");
	if (toggle && nav) {
		var setOpen = function (open) {
			toggle.setAttribute("aria-expanded", String(open));
			nav.classList.toggle("is-open", open);
			doc.body.classList.toggle("nav-open", open);
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
