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
		{ l: "A", c: "#1a9850" }, { l: "B", c: "#66bd63" }, { l: "C", c: "#a6d96a" },
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
		host.innerHTML = "";
		LABELS.forEach(function (item, idx) {
			var cell = document.createElement("span");
			cell.className = "ws-label__cell" + (idx === label.index ? " is-active" : "");
			cell.style.setProperty("--c", item.c);
			cell.textContent = item.l;
			if (idx === label.index) {
				var tag = document.createElement("em");
				tag.textContent = label.letter === item.l ? "uw woning" : "";
				cell.appendChild(tag);
			}
			host.appendChild(cell);
		});
	}

	/* ---------- populate result ---------- */
	function fillResult(d) {
		state.data = d;
		state.surfaces = { vloer: d.surfaces.vloer, dak: d.surfaces.dak, spouw: d.surfaces.spouw };

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

		$("wsVloer").value = d.surfaces.vloer;
		$("wsDak").value = d.surfaces.dak;
		$("wsSpouw").value = d.surfaces.spouw;

		var meta = [];
		if (d.bouwjaar) meta.push("Bouwjaar " + d.bouwjaar);
		if (d.footprint) meta.push("Grondoppervlak ± " + Math.round(d.footprint) + " m²");
		if (d.complex) meta.push("meergezinswoning: m² wordt bij de opname bepaald");
		$("wsMeta").textContent = meta.join(" · ");

		renderLabel(d.energielabel || { index: 4, letter: "?" });
	}

	/* ---------- surfaces -> measures ---------- */
	function num(id) { var n = parseFloat($(id).value); return isNaN(n) || n < 0 ? 0 : n; }

	function readSurfaces() {
		state.surfaces = { vloer: num("wsVloer"), dak: num("wsDak"), spouw: num("wsSpouw") };
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
	["wsVloer", "wsDak", "wsSpouw"].forEach(function (id) {
		$(id).addEventListener("input", function () { readSurfaces(); });
	});
	root.addEventListener("change", function (e) {
		if (e.target && e.target.name === "ws_measure") renderSubsidie();
	});

	$("wsToLead").addEventListener("click", function () {
		var r = computeSubsidie();
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
		if (!endpoint || endpoint.indexOf("REPLACE_WITH_ID") !== -1) {
			err.textContent = "De aanvraag is nog niet gekoppeld (Formspree). Bel ons gerust" + (CFG.phone ? " op " + CFG.phone : "") + ".";
			return;
		}

		var fd = new FormData();
		fd.set("bron", "Warmvast woningscan");
		fd.set("adres", d.address.weergave || "");
		fd.set("postcode", d.address.postcode || "");
		fd.set("woonplaats", d.address.woonplaats || "");
		fd.set("bouwjaar", d.bouwjaar || "");
		fd.set("gebruiksdoel", d.gebruiksdoel || "");
		fd.set("m2_vloer", state.surfaces.vloer || 0);
		fd.set("m2_dak", state.surfaces.dak || 0);
		fd.set("m2_spouw", state.surfaces.spouw || 0);
		fd.set("maatregelen", r.sel.join(", "));
		fd.set("subsidie_indicatie", Math.round(r.subsidie));
		fd.set("besparing_indicatie", Math.round(r.besparing));
		fd.set("verdubbeld_tarief", r.doubled ? "ja" : "nee");
		fd.set("energielabel_indicatie", d.energielabel ? d.energielabel.letter : "");
		fd.set("naam", form.naam.value);
		fd.set("email", form.email.value);
		fd.set("telefoon", form.telefoon.value);
		fd.set("opmerking", form.opmerking.value);
		fd.set("privacy_akkoord", form.privacy_akkoord.checked ? "ja" : "nee");
		fd.set("_subject", "Nieuwe Warmvast woningscan · " + (d.address.weergave || "") + " · subsidie " + euro(r.subsidie));

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
