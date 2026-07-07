/**
 * Warmvast isolatiescan — multi-step form, live ISDE calculator, Formspree AJAX.
 *
 * Tariffs come from PHP via wp_localize_script (window.WARMVAST_SCAN.rates), so
 * the calculator can never drift from the numbers shown on the service pages.
 * Self-guards on #warmvast-scan, so it is safe to load globally.
 */
(function () {
	"use strict";

	var root = document.getElementById("warmvast-scan");
	if (!root) return;

	var CFG = window.WARMVAST_SCAN || {};
	// Fallback tariff table if localization is missing (keeps the scan functional).
	var rates = CFG.rates || {
		spouw: { label: "Spouwmuurisolatie", baseRate: 5.25, minM2: 10, maxM2: 170, field: "m2_spouw" },
		vloer: { label: "Vloerisolatie", baseRate: 5.5, minM2: 20, maxM2: 130, field: "m2_vloer" },
		glas: { label: "HR++ glas", baseRate: 25.0, minM2: 3, maxM2: 45, field: "m2_glas" },
		dak: { label: "Dakisolatie", baseRate: 16.25, minM2: 20, maxM2: 200, field: "m2_dak" }
	};

	var form = document.getElementById("wvScanForm");
	var endpoint = root.dataset.endpoint;
	var steps = Array.prototype.slice.call(root.querySelectorAll(".wv-step"));
	var dots = Array.prototype.slice.call(root.querySelectorAll("[data-dot]"));
	var prev = document.getElementById("wvPrev");
	var next = document.getElementById("wvNext");
	var submit = document.getElementById("wvSubmit");
	var errorEl = document.getElementById("wvFormError");
	var success = document.getElementById("wvSuccess");
	var totalEl = document.getElementById("wvTotal");
	var modeEl = document.getElementById("wvMode");
	var badgeEl = document.getElementById("wvBadge");
	var breakdownEl = document.getElementById("wvBreakdown");
	var emptyEl = document.getElementById("wvEmpty");
	var totalInput = document.getElementById("subsidieTotaalInput");
	var doubledInput = document.getElementById("verdubbeldTariefInput");
	var specInput = document.getElementById("subsidieSpecInput");
	var mobileBar = document.getElementById("wvMobileBar");
	var mobileAmount = document.getElementById("wvMobileAmount");
	var mobileCta = document.getElementById("wvMobileCta");

	var TOTAL_STEPS = 4;
	var currentStep = 1;
	var lastTotal = 0;
	var startedTracked = false;
	var seenTracked = false;
	var stepTracked = {};

	/* ---------- analytics ---------- */
	function track(event, data) {
		var payload = data || {};
		payload.event = event;
		// dataLayer (GTM) if present.
		if (window.dataLayer && typeof window.dataLayer.push === "function") {
			window.dataLayer.push(payload);
		}
		// gtag if present.
		if (typeof window.gtag === "function") {
			window.gtag("event", event, payload);
		}
		// Always dispatch a DOM event so any analytics setup can listen.
		document.dispatchEvent(new CustomEvent("warmvast:" + event, { detail: payload }));
	}

	/* ---------- helpers ---------- */
	function euro(value) {
		try {
			return new Intl.NumberFormat("nl-NL", {
				style: "currency",
				currency: "EUR",
				maximumFractionDigits: 0
			}).format(value || 0);
		} catch (e) {
			return "€ " + Math.round(value || 0);
		}
	}

	function selectedMeasures() {
		return Array.prototype.slice
			.call(form.querySelectorAll('input[name="maatregelen"]:checked'))
			.map(function (i) { return i.value; });
	}

	function fieldNumber(name) {
		var field = form.elements[name];
		var raw = field && field.value ? String(field.value).replace(",", ".") : "0";
		var n = parseFloat(raw);
		return isNaN(n) || n < 0 ? 0 : n;
	}

	/* ---------- calculation ---------- */
	function calculate() {
		var selected = selectedMeasures();
		var doubled = selected.length >= 2;

		var breakdown = selected.map(function (key) {
			var rate = rates[key];
			var enteredM2 = fieldNumber(rate.field);
			var subsidyM2 = Math.min(enteredM2, rate.maxM2);
			var appliedRate = doubled ? rate.baseRate * 2 : rate.baseRate;
			return {
				key: key,
				label: rate.label,
				enteredM2: enteredM2,
				subsidyM2: subsidyM2,
				rate: appliedRate,
				subtotal: subsidyM2 * appliedRate,
				belowMinimum: enteredM2 > 0 && enteredM2 < rate.minM2,
				capped: enteredM2 > rate.maxM2
			};
		});

		return {
			selected: selected,
			doubled: doubled,
			breakdown: breakdown,
			total: breakdown.reduce(function (s, i) { return s + i.subtotal; }, 0)
		};
	}

	function bump(el) {
		if (!el) return;
		el.classList.remove("is-bump");
		// force reflow so the animation restarts
		void el.offsetWidth;
		el.classList.add("is-bump");
	}

	// Tween the big total from its previous value to the new one.
	var countRaf = null;
	var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
	function animateTotal(from, to) {
		if (countRaf) cancelAnimationFrame(countRaf);
		if (reduceMotion || from === to) {
			totalEl.textContent = euro(to);
			if (mobileAmount) mobileAmount.textContent = euro(to);
			return;
		}
		var start = performance.now();
		var dur = Math.min(700, 260 + Math.abs(to - from) * 0.12);
		function frame(now) {
			var t = Math.min(1, (now - start) / dur);
			var eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
			var val = from + (to - from) * eased;
			totalEl.textContent = euro(val);
			if (mobileAmount) mobileAmount.textContent = euro(val);
			if (t < 1) { countRaf = requestAnimationFrame(frame); }
			else { totalEl.textContent = euro(to); if (mobileAmount) mobileAmount.textContent = euro(to); }
		}
		countRaf = requestAnimationFrame(frame);
	}

	function renderCalculator() {
		var result = calculate();

		animateTotal(lastTotal, result.total);
		totalInput.value = Math.round(result.total);
		doubledInput.value = result.doubled ? "ja" : "nee";
		specInput.value = JSON.stringify(result.breakdown);

		// bump when the number grows meaningfully (e.g. the verdubbelaar kicks in)
		if (result.total > lastTotal + 1) {
			bump(totalEl);
		}
		lastTotal = result.total;

		badgeEl.classList.toggle("is-on", result.doubled);

		if (emptyEl) emptyEl.hidden = result.selected.length > 0;

		if (!result.selected.length) {
			modeEl.textContent = "Selecteer minimaal één maatregel om uw indicatie te zien.";
			breakdownEl.innerHTML = "";
			return;
		}

		modeEl.textContent = result.doubled
			? "Twee of meer maatregelen: uw ISDE-tarief per m² is verdubbeld."
			: "Eén maatregel: basisbedrag per m². Voeg een tweede toe en het tarief verdubbelt.";

		breakdownEl.innerHTML = result.breakdown
			.map(function (item) {
				var notes = [];
				if (item.belowMinimum) notes.push("let op: onder de minimale m²-eis");
				if (item.capped) notes.push("berekening afgetopt op maximum m²");
				var note = notes.length ? '<span class="wv-note">' + notes.join(" · ") + "</span>" : "";
				return (
					"<li><span>" +
					item.label +
					" · " +
					(Math.round(item.subsidyM2 * 10) / 10) +
					" m² × " +
					euro(item.rate) +
					note +
					"</span><strong>" +
					euro(item.subtotal) +
					"</strong></li>"
				);
			})
			.join("");

		if (!seenTracked && result.total > 0) {
			seenTracked = true;
			track("scan_subsidy_seen", { subsidie_totaal: Math.round(result.total), verdubbeld: result.doubled });
		}
	}

	/* ---------- step 3 area fields follow the chosen measures ---------- */
	function syncAreaFields() {
		var selected = selectedMeasures();
		Array.prototype.slice.call(root.querySelectorAll("[data-area]")).forEach(function (label) {
			var key = label.dataset.area;
			var visible = selected.indexOf(key) !== -1;
			label.hidden = !visible;
			if (!visible) {
				var input = label.querySelector("input");
				if (input) input.value = "";
			}
		});
		renderCalculator();
	}

	/* ---------- step navigation ---------- */
	function showStep(step, focus) {
		currentStep = Math.max(1, Math.min(TOTAL_STEPS, step));
		steps.forEach(function (el) {
			el.hidden = Number(el.dataset.step) !== currentStep;
		});
		dots.forEach(function (dot) {
			var n = Number(dot.dataset.dot);
			dot.classList.toggle("is-active", n < currentStep);
			dot.classList.toggle("is-current", n === currentStep);
		});
		prev.hidden = currentStep === 1;
		next.hidden = currentStep === TOTAL_STEPS;
		submit.hidden = currentStep !== TOTAL_STEPS;
		errorEl.textContent = "";

		if (focus !== false) {
			var active = steps.filter(function (s) { return Number(s.dataset.step) === currentStep; })[0];
			var legend = active && active.querySelector("legend");
			if (legend) {
				legend.setAttribute("tabindex", "-1");
				legend.focus({ preventScroll: false });
			}
		}

		if (currentStep === TOTAL_STEPS && !stepTracked.contact) {
			stepTracked.contact = true;
			track("scan_contact_step");
		}
	}

	function validateStep() {
		var active = steps.filter(function (s) { return Number(s.dataset.step) === currentStep; })[0];
		if (!active) return "";

		if (currentStep === 2 && selectedMeasures().length === 0) {
			return "Selecteer minimaal één isolatiemaatregel om verder te gaan.";
		}

		var required = Array.prototype.slice.call(active.querySelectorAll("[required]"));
		for (var i = 0; i < required.length; i++) {
			if (!required[i].checkValidity()) {
				required[i].reportValidity();
				return "Controleer de gemarkeerde velden voordat u verder gaat.";
			}
		}
		return "";
	}

	/* ---------- events ---------- */
	next.addEventListener("click", function () {
		var msg = validateStep();
		if (msg) { errorEl.textContent = msg; return; }
		if (!startedTracked) { startedTracked = true; track("scan_start"); }
		if (!stepTracked[currentStep]) {
			stepTracked[currentStep] = true;
			track("scan_step_" + currentStep + "_complete");
		}
		showStep(currentStep + 1);
		root.scrollIntoView({ behavior: "smooth", block: "start" });
	});

	prev.addEventListener("click", function () {
		showStep(currentStep - 1);
	});

	form.addEventListener("change", function (e) {
		if (!startedTracked) { startedTracked = true; track("scan_start"); }
		if (e.target && e.target.name === "maatregelen") {
			syncAreaFields();
			bump(totalEl);
		}
		renderCalculator();
	});

	form.addEventListener("input", function (e) {
		if (e.target && e.target.type === "number") renderCalculator();
	});

	form.addEventListener("submit", function (e) {
		e.preventDefault();
		errorEl.textContent = "";

		var msg = validateStep();
		if (msg) { errorEl.textContent = msg; return; }

		// honeypot: if filled, silently pretend success (bot).
		if (form.elements._gotcha && form.elements._gotcha.value) {
			showSuccess();
			return;
		}

		if (!endpoint || endpoint.indexOf("REPLACE_WITH_ID") !== -1) {
			errorEl.textContent =
				"De scan is nog niet gekoppeld aan een Formspree-endpoint. Neem telefonisch contact op: " + (CFG.phone || "");
			return;
		}

		var result = calculate();
		var data = new FormData(form);
		data.set("subsidie_totaal", Math.round(result.total));
		data.set("verdubbeld_tarief", result.doubled ? "ja" : "nee");
		data.set("subsidie_specificatie", JSON.stringify(result.breakdown));
		data.set("aantal_maatregelen", result.selected.length);

		// Descriptive Formspree subject: postcode + indicatie (per blueprint).
		var postcode = (form.elements.postcode && form.elements.postcode.value ? form.elements.postcode.value : "").toUpperCase().trim();
		data.set(
			"_subject",
			"Nieuwe Warmvast isolatiescan" +
				(postcode ? " · " + postcode : "") +
				" · indicatie " + euro(result.total) +
				(result.selected.length ? " · " + result.selected.join("+") : "")
		);

		submit.disabled = true;
		var originalLabel = submit.innerHTML;
		submit.textContent = "Versturen…";

		fetch(endpoint, {
			method: "POST",
			headers: { Accept: "application/json" },
			body: data
		})
			.then(function (response) {
				if (!response.ok) throw new Error("Formspree " + response.status);
				return response.json().catch(function () { return {}; });
			})
			.then(function () {
				track("scan_submit_success", {
					subsidie_totaal: Math.round(result.total),
					aantal_maatregelen: result.selected.length,
					verdubbeld: result.doubled
				});
				showSuccess();
			})
			.catch(function () {
				track("scan_submit_error");
				errorEl.textContent =
					"Verzenden lukt nu niet. Probeer het later opnieuw of bel Warmvast" +
					(CFG.phone ? " op " + CFG.phone : "") + ".";
			})
			.finally(function () {
				submit.disabled = false;
				submit.innerHTML = originalLabel;
			});
	});

	function showSuccess() {
		form.hidden = true;
		var aside = root.querySelector(".wv-result");
		if (aside) aside.hidden = true;
		success.hidden = false;
		if (mobileBar) mobileBar.classList.remove("is-visible");
		success.setAttribute("tabindex", "-1");
		success.scrollIntoView({ behavior: "smooth", block: "center" });
		success.focus({ preventScroll: true });
	}

	/* ---------- sticky mobile bar ---------- */
	if (mobileBar && "IntersectionObserver" in window) {
		var resultPanel = root.querySelector(".wv-result");
		var io = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					// Show the bar on small screens when the result panel scrolls out of view
					// (and the form hasn't been submitted yet).
					var small = window.matchMedia("(max-width: 860px)").matches;
					mobileBar.classList.toggle("is-visible", small && !entry.isIntersecting && success.hidden);
				});
			},
			{ threshold: 0.1 }
		);
		if (resultPanel) io.observe(resultPanel);
		if (mobileCta) {
			mobileCta.addEventListener("click", function () {
				// jump to the next incomplete step / contact
				showStep(currentStep < TOTAL_STEPS ? currentStep : TOTAL_STEPS);
			});
		}
	}

	/* ---------- init ---------- */
	function init() {
		// preselect measure from data-preselect (service pages).
		var pre = root.dataset.preselect;
		if (pre) {
			var box = form.querySelector('input[name="maatregelen"][value="' + pre + '"]');
			if (box) box.checked = true;
		}
		syncAreaFields();
		showStep(1, false);
		renderCalculator();
	}

	init();
})();
