/**
 * Warmvast woningscan — address-driven building scan.
 *
 * Flow: address -> (PDOK via our REST endpoint) -> result (3D house + aerial +
 * surfaces + energielabel) -> subsidie + besparing -> lead -> Formspree.
 * Self-guards on #warmvast-woningscan.
 */
(function () {
	"use strict";

	var root = document.getElementById("warmvast-woningscan");
	if (!root) return;

	var CFG = window.WARMVAST_SCAN || {};
	var rates = CFG.rates || {};
	var savings = CFG.savings || { spouw: 4.5, vloer: 3.5, dak: 4.0, glas: 6.0 };
	var restUrl = CFG.restUrl || "";
	var endpoint = root.dataset.endpoint || CFG.endpoint || "";
	var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	var LABELS = [
		{ l: "A++++", c: "#006b3c" }, { l: "A+++", c: "#00864a" }, { l: "A++", c: "#14a05a" },
		{ l: "A+", c: "#45b96f" }, { l: "A", c: "#66bd63" }, { l: "B", c: "#8bd06b" }, { l: "C", c: "#c8df7a" },
		{ l: "D", c: "#f6c945" }, { l: "E", c: "#fdae61" }, { l: "F", c: "#f46d43" }, { l: "G", c: "#d73027" }
	];

	var $ = function (id) { return document.getElementById(id); };
	var stages = {};
	Array.prototype.slice.call(root.querySelectorAll("[data-stage]")).forEach(function (el) {
		stages[el.dataset.stage] = el;
	});

	var state = { data: null, surfaces: {}, subsidie: 0, besparing: 0, doubled: false };

	/* ---------- analytics ---------- */
	function track(ev, data) {
		var p = data || {}; p.event = ev;
		if (window.dataLayer && window.dataLayer.push) window.dataLayer.push(p);
		if (typeof window.gtag === "function") window.gtag("event", ev, p);
		document.dispatchEvent(new CustomEvent("warmvast:" + ev, { detail: p }));
	}

	function euro(v) {
		try { return new Intl.NumberFormat("nl-NL", { style: "currency", currency: "EUR", maximumFractionDigits: 0 }).format(v || 0); }
		catch (e) { return "€ " + Math.round(v || 0); }
	}

	function m2(v) {
		var n = Number(v || 0);
		return (Math.round(n * 10) / 10).toLocaleString("nl-NL") + " m²";
	}

	function yesNo(v) {
		return v ? "Ja" : "Nee";
	}

	function measureLabel(key) {
		return rates[key] && rates[key].label ? rates[key].label : key;
	}

	function formatMeasures(keys) {
		if (!keys || !keys.length) return "Geen maatregelen geselecteerd";
		return keys.map(measureLabel).join(", ");
	}

	function buildLeadSummary(data, result, form) {
		var address = data.address || {};
		var energy = data.energielabel || {};
		var energySource = energy.source && energy.source.indexOf("ep-online") === 0
			? "EP-Online geregistreerd"
			: "Bouwjaar indicatie";
		return [
			"Nieuwe woningscan lead",
			"",
			"CONTACT",
			"Naam: " + form.naam.value,
			"E-mail: " + form.email.value,
			"Telefoon: " + form.telefoon.value,
			"",
			"WONING",
			"Adres: " + (address.weergave || "-"),
			"Postcode/plaats: " + ([address.postcode, address.woonplaats].filter(Boolean).join(" ") || "-"),
			"Bouwjaar: " + (data.bouwjaar || "-"),
			"Energielabel: " + (energy.letter || "-") + " (" + energySource + ")",
			"",
			"ADVIES",
			"Maatregelen: " + formatMeasures(result.sel),
			"ISDE-indicatie: " + euro(result.subsidie),
			"Besparing indicatie: " + euro(result.besparing) + " per jaar",
			"Verdubbeld tarief: " + yesNo(result.doubled),
			"",
			"OPPERVLAKTES",
			"Vloer: " + m2(state.surfaces.vloer),
			"Dak: " + m2(state.surfaces.dak),
			"Buitenmuur/spouw: " + m2(state.surfaces.spouw),
			"Glas: " + m2(state.surfaces.glas),
			"",
			"OPMERKING",
			form.opmerking.value || "-"
		].join("\n");
	}

	function showStage(name) {
		Object.keys(stages).forEach(function (k) { stages[k].hidden = k !== name; });
		$("wsLoading").hidden = true;
		root.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "nearest" });
	}

	/* ---------- 3D house from footprint polygon ---------- */
	function renderHouse(poly) {
		var host = $("wsHouse");
		host.innerHTML = "";
		if (!poly || poly.length < 3) return;

		// drop closing duplicate point
		var pts = poly.slice();
		var f = pts[0], l = pts[pts.length - 1];
		if (Math.abs(f[0] - l[0]) < 1e-4 && Math.abs(f[1] - l[1]) < 1e-4) pts.pop();
		var n = pts.length;
		if (n < 3) return;

		var VW = 120, VH = 104, wallH = 20, roofH = 15, yFore = 0.7;
		// normalized coords are in [0,1]; fit into a plot box preserving aspect
		var nxs = pts.map(function (p) { return p[0]; }), nys = pts.map(function (p) { return p[1]; });
		var minx = Math.min.apply(null, nxs), maxx = Math.max.apply(null, nxs);
		var miny = Math.min.apply(null, nys), maxy = Math.max.apply(null, nys);
		var spanx = Math.max(1e-3, maxx - minx), spany = Math.max(1e-3, maxy - miny);
		var plotW = 84, plotH = 50;
		var s = Math.min(plotW / spanx, (plotH / spany) / yFore);
		var drawW = spanx * s, drawH = spany * s * yFore;
		var offX = (VW - drawW) / 2, offY = 22 + (plotH - drawH) / 2;

		// map: screen x = offX + (nx-minx)*s ; screen y = offY + (ny-miny)*s*yFore
		var top = pts.map(function (p) {
			return [offX + (p[0] - minx) * s, offY + (p[1] - miny) * s * yFore];
		});
		var base = top.map(function (p) { return [p[0], p[1] + wallH]; });
		var cx = top.reduce(function (a, p) { return a + p[0]; }, 0) / n;
		var cy = top.reduce(function (a, p) { return a + p[1]; }, 0) / n;
		var apex = [cx, cy - roofH];

		var faces = [];
		for (var i = 0; i < n; i++) {
			var j = (i + 1) % n;
			// wall quad
			faces.push({
				type: "wall",
				pts: [top[i], top[j], base[j], base[i]],
				depth: (top[i][1] + top[j][1]) / 2
			});
			// roof triangle
			faces.push({
				type: "roof",
				pts: [top[i], top[j], apex],
				depth: (top[i][1] + top[j][1]) / 2 - 2
			});
		}
		faces.sort(function (a, b) { return a.depth - b.depth; }); // far (small y) first

		var svgNS = "http://www.w3.org/2000/svg";
		var svg = document.createElementNS(svgNS, "svg");
		svg.setAttribute("viewBox", "0 0 " + VW + " " + VH);
		svg.setAttribute("class", "ws-house__svg");

		// ground shadow
		var shadow = document.createElementNS(svgNS, "polygon");
		shadow.setAttribute("points", base.map(function (p) { return p[0] + "," + p[1]; }).join(" "));
		shadow.setAttribute("class", "ws-house__shadow");
		svg.appendChild(shadow);

		faces.forEach(function (face) {
			var el = document.createElementNS(svgNS, "polygon");
			el.setAttribute("points", face.pts.map(function (p) { return p[0].toFixed(1) + "," + p[1].toFixed(1); }).join(" "));
			el.setAttribute("class", face.type === "roof" ? "ws-house__roof" : "ws-house__wall");
			svg.appendChild(el);
		});

		// top outline (crisp)
		var outline = document.createElementNS(svgNS, "polygon");
		outline.setAttribute("points", top.map(function (p) { return p[0].toFixed(1) + "," + p[1].toFixed(1); }).join(" "));
		outline.setAttribute("class", "ws-house__outline");
		svg.appendChild(outline);

		host.appendChild(svg);
	}

	/* ---------- energy label scale ---------- */
	function renderLabel(label) {
		var host = $("wsLabelScale");
		var hint = $("wsLabelHint");
		var source = $("wsLabelSource");
		label = label || { index: -1, letter: "?", source: "unavailable", basis: "Energielabel niet beschikbaar." };
		host.innerHTML = "";
		host.hidden = label.source === "unavailable";
		LABELS.forEach(function (item, idx) {
			var cell = document.createElement("span");
			cell.className = "ws-label__cell" + (idx === label.index ? " is-active" : "");
			cell.dataset.label = item.l;
			cell.style.setProperty("--c", item.c);
			cell.textContent = item.l;
			if (idx === label.index) {
				var tag = document.createElement("em");
				tag.textContent = label.letter === item.l ? "uw woning" : "";
				cell.appendChild(tag);
			}
			host.appendChild(cell);
		});
		if (hint) {
			if (label.source && label.source.indexOf("ep-online") === 0) {
				hint.textContent = "(geregistreerd label)";
			} else if (label.source === "bouwjaar") {
				hint.textContent = "(indicatie o.b.v. bouwjaar)";
			} else {
				hint.textContent = "(niet opgehaald)";
			}
		}
		if (source) {
			var text = label.basis || "";
			if (label.source && label.source.indexOf("ep-online") === 0) {
				if (label.validUntil) text += " Geldig tot " + label.validUntil + ".";
				else if (label.registeredAt) text += " Geregistreerd op " + label.registeredAt + ".";
			}
			source.textContent = text;
		}
	}

	/* ---------- aerial footprint overlay ---------- */
	function renderAerialFootprint(poly) {
		var img = $("wsAerial");
		var host = img ? img.closest(".ws-aerial") : null;
		if (!host) return;

		var old = host.querySelector(".ws-aerial__overlay");
		if (old) old.remove();
		if (!poly || poly.length < 3) return;

		var pts = poly
			.filter(function (p) { return p && isFinite(p[0]) && isFinite(p[1]); })
			.map(function (p) {
				var x = Math.max(0, Math.min(1, Number(p[0])));
				var y = Math.max(0, Math.min(1, Number(p[1])));
				return [x * 100, y * 100];
			});
		if (pts.length < 3) return;

		var svgNS = "http://www.w3.org/2000/svg";
		var svg = document.createElementNS(svgNS, "svg");
		svg.setAttribute("class", "ws-aerial__overlay");
		svg.setAttribute("viewBox", "0 0 100 100");
		svg.setAttribute("preserveAspectRatio", "none");

		var shape = document.createElementNS(svgNS, "polygon");
		shape.setAttribute("points", pts.map(function (p) { return p[0].toFixed(3) + "," + p[1].toFixed(3); }).join(" "));
		shape.setAttribute("class", "ws-aerial__footprint");
		svg.appendChild(shape);

		host.appendChild(svg);
	}

	/* ---------- populate result ---------- */
	function fillResult(d) {
		state.data = d;
		state.surfaces = { vloer: d.surfaces.vloer, dak: d.surfaces.dak, spouw: d.surfaces.spouw, glas: d.surfaces.glas };

		$("wsResultTitle").textContent = "Resultaat voor " + (d.address.weergave || "uw woning");
		var v = $("wsVerdict");
		if (d.geschikt) {
			v.textContent = "Uw woning is geschikt voor isolatie!";
			v.className = "ws-verdict is-ok";
		} else {
			v.textContent = "Dit lijkt geen reguliere woning. We kijken graag met u mee wat mogelijk is.";
			v.className = "ws-verdict is-warn";
		}

		renderHouse(d.polygon);
		var img = $("wsAerial");
		img.src = d.aerial; img.onerror = function () { img.closest(".ws-aerial").style.display = "none"; };
		renderAerialFootprint(d.aerialPolygon);

		$("wsVloer").value = d.surfaces.vloer;
		$("wsDak").value = d.surfaces.dak;
		$("wsSpouw").value = d.surfaces.spouw;
		$("wsGlas").value = d.surfaces.glas;

		var meta = [];
		if (d.bouwjaar) meta.push("Bouwjaar " + d.bouwjaar);
		if (d.footprint) meta.push("Grondoppervlak ± " + Math.round(d.footprint) + " m²");
		if (d.complex) meta.push("meergezinswoning: m² wordt bij de opname bepaald");
		$("wsMeta").textContent = meta.join(" · ");

		renderLabel(d.energielabel);
	}

	/* ---------- surfaces -> measures ---------- */
	function num(id) { var n = parseFloat($(id).value); return isNaN(n) || n < 0 ? 0 : n; }

	function readSurfaces() {
		state.surfaces = { vloer: num("wsVloer"), dak: num("wsDak"), spouw: num("wsSpouw"), glas: num("wsGlas") };
	}

	function selectedMeasures() {
		return Array.prototype.slice.call(root.querySelectorAll('input[name="ws_measure"]:checked')).map(function (i) { return i.value; });
	}

	function computeSubsidie() {
		var sel = selectedMeasures();
		var doubled = sel.length >= 2;
		var subsidie = 0, besparing = 0;
		sel.forEach(function (key) {
			var rate = rates[key];
			var m2 = state.surfaces[key] || 0;
			if (rate) {
				var capped = Math.min(m2, rate.maxM2);
				subsidie += capped * rate.baseRate * (doubled ? 2 : 1);
			}
			besparing += m2 * (savings[key] || 0);
		});
		state.subsidie = subsidie; state.besparing = besparing; state.doubled = doubled;
		return { subsidie: subsidie, besparing: besparing, doubled: doubled, sel: sel };
	}

	function animateVal(el, to) {
		var from = parseFloat(el.dataset.v || "0");
		el.dataset.v = to;
		if (reduce || Math.abs(to - from) < 1) { el.textContent = euro(to); return; }
		var start = performance.now(), dur = Math.min(650, 250 + Math.abs(to - from) * 0.1);
		(function frame(now) {
			var t = Math.min(1, (now - start) / dur), e = 1 - Math.pow(1 - t, 3);
			el.textContent = euro(from + (to - from) * e);
			if (t < 1) requestAnimationFrame(frame); else el.textContent = euro(to);
		})(start);
	}

	function renderSubsidie() {
		readSurfaces();
		var r = computeSubsidie();
		// update per-measure m² tags
		root.querySelectorAll("[data-m2]").forEach(function (tag) {
			var key = tag.dataset.m2;
			tag.textContent = (state.surfaces[key] || 0) + " m²";
		});
		animateVal($("wsSubsidie"), r.subsidie);
		animateVal($("wsBesparing"), r.besparing);
		$("wsDoubleBadge").classList.toggle("is-on", r.doubled);
	}

	/* ---------- address lookup ---------- */
	$("wsAddressForm").addEventListener("submit", function (e) {
		e.preventDefault();
		var err = $("wsAddressError");
		err.textContent = "";
		var pc = $("wsPostcode").value.trim();
		var nr = $("wsHuisnummer").value.trim();
		var tv = $("wsToevoeging").value.trim();
		if (!/^[0-9]{4}\s?[A-Za-z]{2}$/.test(pc) || !nr) {
			err.textContent = "Vul een geldige postcode (1234 AB) en huisnummer in.";
			return;
		}
		track("scan_start", { type: "woningscan" });
		stages.address.hidden = true;
		$("wsLoading").hidden = false;

		var url = restUrl + "?postcode=" + encodeURIComponent(pc) + "&huisnummer=" + encodeURIComponent(nr) + "&toevoeging=" + encodeURIComponent(tv);
		fetch(url, { headers: { Accept: "application/json" } })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				if (!d || !d.ok) {
					stages.address.hidden = false;
					$("wsLoading").hidden = true;
					err.textContent = (d && d.error) ? d.error : "We konden dit adres niet vinden.";
					return;
				}
				fillResult(d);
				showStage("result");
				track("scan_result_seen", { geschikt: d.geschikt, bouwjaar: d.bouwjaar });
			})
			.catch(function () {
				stages.address.hidden = false;
				$("wsLoading").hidden = true;
				err.textContent = "Er ging iets mis bij het ophalen. Probeer het later opnieuw.";
			});
	});

	// auto-uppercase postcode
	$("wsPostcode").addEventListener("input", function () { this.value = this.value.toUpperCase(); });

	/* ---------- navigation ---------- */
	root.querySelectorAll("[data-ws-back]").forEach(function (b) {
		b.addEventListener("click", function () { showStage(b.dataset.wsBack); });
	});

	$("wsToSubsidie").addEventListener("click", function () {
		renderSubsidie();
		showStage("subsidie");
		track("scan_subsidy_seen", { subsidie: Math.round(state.subsidie) });
	});

	// recompute when surfaces edited (on result stage) or measures toggled
	["wsVloer", "wsDak", "wsSpouw", "wsGlas"].forEach(function (id) {
		$(id).addEventListener("input", function () { readSurfaces(); });
	});
	root.addEventListener("change", function (e) {
		if (e.target && e.target.name === "ws_measure") renderSubsidie();
	});

	$("wsToLead").addEventListener("click", function () {
		var r = computeSubsidie();
		var subsidieError = $("wsSubsidieError");
		if (!r.sel.length) {
			subsidieError.textContent = "Selecteer minimaal één maatregel om verder te gaan.";
			return;
		}
		subsidieError.textContent = "";
		$("wsLeadSummary").textContent =
			"Voor " + (state.data ? state.data.address.weergave : "uw woning") +
			": geschatte subsidie " + euro(r.subsidie) + " en besparing " + euro(r.besparing) + " per jaar.";
		showStage("lead");
		track("scan_contact_step");
	});

	/* ---------- lead submit ---------- */
	$("wsLeadForm").addEventListener("submit", function (e) {
		e.preventDefault();
		var err = $("wsLeadError");
		err.textContent = "";
		var form = e.target;
		if (!form.checkValidity()) { form.reportValidity(); return; }
		if (form.elements._gotcha && form.elements._gotcha.value) { showStage("success"); return; }

		var r = computeSubsidie();
		var d = state.data || { address: {} };
		var address = d.address || {};
		if (!endpoint || endpoint.indexOf("REPLACE_WITH_ID") !== -1) {
			err.textContent = "De aanvraag is nog niet gekoppeld (Formspree). Bel ons gerust" + (CFG.phone ? " op " + CFG.phone : "") + ".";
			return;
		}

		var fd = new FormData();
		fd.set("_subject", "Nieuwe Warmvast lead - " + (address.weergave || "adres onbekend") + " - " + euro(r.subsidie));
		fd.set("_replyto", form.email.value);
		fd.set("01. Samenvatting", buildLeadSummary(d, r, form));
		fd.set("02. Naam klant", form.naam.value);
		fd.set("03. E-mail klant", form.email.value);
		fd.set("04. Telefoon klant", form.telefoon.value);
		fd.set("05. Adres", address.weergave || "");
		fd.set("06. Postcode", address.postcode || "");
		fd.set("07. Woonplaats", address.woonplaats || "");
		fd.set("08. Bouwjaar", d.bouwjaar ? String(d.bouwjaar) : "Onbekend");
		fd.set("09. Gebruiksdoel", d.gebruiksdoel || "Onbekend");
		fd.set("10. Energielabel", d.energielabel ? d.energielabel.letter + " - " + (d.energielabel.basis || "") : "Onbekend");
		fd.set("11. Geselecteerde maatregelen", formatMeasures(r.sel));
		fd.set("12. Vloeroppervlak", m2(state.surfaces.vloer));
		fd.set("13. Dakoppervlak", m2(state.surfaces.dak));
		fd.set("14. Buitenmuur/spouw oppervlak", m2(state.surfaces.spouw));
		fd.set("15. Glasoppervlak", m2(state.surfaces.glas));
		fd.set("16. ISDE-subsidie indicatie", euro(r.subsidie));
		fd.set("17. Besparing indicatie", euro(r.besparing) + " per jaar");
		fd.set("18. Verdubbeld ISDE-tarief", yesNo(r.doubled));
		fd.set("19. Opmerking klant", form.opmerking.value || "-");
		fd.set("20. Privacy akkoord", yesNo(form.privacy_akkoord.checked));
		fd.set("21. Bron", "Warmvast woningscan");

		var btn = $("wsSubmitLead");
		btn.disabled = true; var lbl = btn.innerHTML; btn.textContent = "Versturen…";
		fetch(endpoint, { method: "POST", headers: { Accept: "application/json" }, body: fd })
			.then(function (res) { if (!res.ok) throw new Error("formspree"); return res.json().catch(function () { return {}; }); })
			.then(function () {
				track("scan_submit_success", { subsidie: Math.round(r.subsidie), aantal_maatregelen: r.sel.length });
				showStage("success");
			})
			.catch(function () {
				track("scan_submit_error");
				err.textContent = "Verzenden lukt nu niet. Probeer het later opnieuw of bel Warmvast" + (CFG.phone ? " op " + CFG.phone : "") + ".";
			})
			.finally(function () { btn.disabled = false; btn.innerHTML = lbl; });
	});
})();
