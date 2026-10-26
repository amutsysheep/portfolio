// Vaccinatieplan (inhaalschema) volgens de Beslisboom inhaalschema's RVP 2026
// en hoofdstuk 10 van de RVP-richtlijn.
//
// Werkwijze:
//  1. Per reeks (DKTP, Pneu, Rota, BMR, MenACWY) wordt uitgerekend wanneer de
//     volgende dosis mag, op basis van al gegeven doses en de streefintervallen.
//  2. Doses die rond dezelfde datum vallen worden samengevoegd tot één
//     vaccinatiemoment (X1, X2, ...). Maximaal 2 prikken per moment (Rota is
//     een drankje en telt niet mee), zoals in de schema's van de beslisboom.
//  3. Daarna volgt het vervolg-RVP: BMR2, DKT-booster, HPV, MenACWY, DTP.

import { DAG, laatste, leesDatum, telOp } from './datum';

const MAX_PRIKKEN = 2;
const SAMENVOEG_DAGEN = 14; // doses binnen 14 dagen van elkaar komen op één moment
const AANSLUIT_DAGEN = 31; // Pneu/Rota wachten max. een maand om samen met DKTP te gaan

// Antwoord-id's per reeks
const IDS = {
  dktp: ['dktp1', 'dktp2', 'dktp3'],
  pneu: ['pneu1', 'pneu2', 'pneu3'],
  rota: ['rota1', 'rota2'],
  bmr: ['bmr1', 'bmr2'],
  menacwy: ['menacwy1'],
};

// Geldige, gegeven datums (tussen geboorte en planningsdatum), gesorteerd
function gegevenDatums(antwoorden, ids, geboortedatum, tot) {
  return ids
    .map((id) => antwoorden[id])
    .filter((a) => a?.gehad)
    .map((a) => leesDatum(a.datum))
    .filter((d) => d && d >= geboortedatum && d <= tot)
    .sort((a, b) => a - b);
}

// DKTP-Hib-HepB: basisimmuniteit = 2 primaire doses + revaccinatie min. 6 mnd later
// (standaardschema 3-5-12 / T=0-2-9). Extra buitenlandse doses met een korter
// interval (bijv. 6-10-14 weken) tellen als primaire doses.
export function dktpStatus(doses) {
  let primair = 0;
  for (let i = 0; i < doses.length; i++) {
    if (primair >= 2 && doses[i] >= telOp(doses[i - 1], { m: 6 })) {
      return { primair, compleet: true, eind: doses[i] };
    }
    primair++;
  }
  return { primair, compleet: false, eind: null };
}

function maakReeksen(ctx) {
  const { gb, T0, antwoorden, kind } = ctx;
  const leeftijd = (periode) => telOp(gb, periode);
  const eenJaar = leeftijd({ j: 1 });

  const dktp = {
    id: 'dktp',
    prikken: 1,
    naam: (n) => `DKTP-Hib-HepB ${n}`,
    gegeven: gegevenDatums(antwoorden, IDS.dktp, gb, T0),
    volgende() {
      const doses = this.doses();
      const st = dktpStatus(doses);
      if (st.compleet) return null;
      if (!doses.length) return { datum: laatste(T0, leeftijd({ m: 3 })) };
      const vorige = doses[doses.length - 1];
      return { datum: laatste(T0, telOp(vorige, st.primair < 2 ? { m: 2 } : { m: 7 })) };
    },
  };

  // Laat een dosis wachten op de eerstvolgende DKTP als die binnen een maand valt
  const sluitAanBijDktp = (datum, uiterlijk) => {
    const d = dktp.volgende();
    if (d && d.datum > datum && d.datum - datum <= AANSLUIT_DAGEN * DAG && (!uiterlijk || d.datum <= uiterlijk)) {
      return d.datum;
    }
    return datum;
  };

  const pneu = {
    id: 'pneu',
    prikken: 1,
    naam: (n) => `Pneu ${n}`,
    gegeven: gegevenDatums(antwoorden, IDS.pneu, gb, T0),
    volgende() {
      const tweeJaar = leeftijd({ j: 2 });
      const doses = this.doses();
      const voor = doses.filter((d) => d < eenJaar).length;
      const na = doses.length - voor;
      // Compleet: 2 doses na de 1e verjaardag, of 2 primaire doses + revaccinatie (min. 6 mnd later)
      const revaccinatie = doses.some((d, i) => i >= 2 && d >= telOp(doses[i - 1], { m: 6 }));
      if (na >= 2 || (voor >= 2 && revaccinatie)) return null;

      let datum;
      if (!doses.length) {
        datum = laatste(T0, leeftijd({ m: 3 }));
      } else {
        const vorige = doses[doses.length - 1];
        // Na 2 doses vóór de 1e verjaardag: revaccinatie 7 mnd later.
        // Anders 2 mnd interval (2 doses na de 1e verjaardag zijn dan voldoende).
        datum = laatste(T0, telOp(vorige, voor >= 2 ? { m: 7 } : { m: 2 }));
      }
      const uiterlijk = telOp(tweeJaar, { d: -1 }); // Pneu vervalt na de 2e verjaardag
      datum = sluitAanBijDktp(datum, uiterlijk);
      return datum <= uiterlijk ? { datum, uiterlijk } : null;
    },
  };

  const rota = {
    id: 'rota',
    prikken: 0, // drankje
    naam: (n) => `Rota ${n}`,
    gegeven: gegevenDatums(antwoorden, IDS.rota, gb, T0),
    volgende() {
      const uiterlijk = leeftijd({ w: 24, d: -1 }); // max. 23 weken en 6 dagen
      const doses = this.doses();
      if (doses.length >= 2) return null;
      let datum;
      if (!doses.length) {
        datum = laatste(T0, leeftijd({ w: 6 }));
      } else {
        if (doses[0] >= leeftijd({ w: 20 })) return null; // Rota1 na 20 weken: Rota2 vervalt
        datum = sluitAanBijDktp(laatste(T0, telOp(doses[0], { m: 1 })), uiterlijk);
      }
      return datum <= uiterlijk ? { datum, uiterlijk } : null;
    },
  };

  // Alle BMR-doses; een dosis vóór de 1e verjaardag telt niet mee voor BMR1/BMR2
  const bmrGegeven = gegevenDatums(antwoorden, IDS.bmr, gb, T0);

  const bmr0 = {
    id: 'bmr0',
    prikken: 1,
    eindpunt: true,
    naam: () => 'BMR 0',
    gegeven: [],
    volgende() {
      // Alleen voor asielzoekerskinderen: extra BMR op 9 maanden
      if (kind.betreft !== 'asielzoeker' || this.doses().length) return null;
      if (bmrGegeven.some((d) => d >= leeftijd({ m: 6 }))) return null;
      const uiterlijk = telOp(eenJaar, { d: -1 });
      const datum = laatste(T0, leeftijd({ m: 9 }));
      return datum <= uiterlijk ? { datum, uiterlijk } : null;
    },
  };

  const bmr1 = {
    id: 'bmr1',
    prikken: 1,
    eindpunt: true,
    naam: () => 'BMR 1',
    gegeven: bmrGegeven.filter((d) => d >= eenJaar).slice(0, 1),
    volgende() {
      if (this.doses().length) return null;
      return { datum: laatste(T0, leeftijd({ m: 14 })) };
    },
  };

  const menacwy = {
    id: 'menacwy',
    prikken: 1,
    eindpunt: true,
    naam: () => 'MenACWY',
    gegeven: gegevenDatums(antwoorden, IDS.menacwy, gb, T0).filter((d) => d >= eenJaar).slice(0, 1),
    volgende() {
      if (this.doses().length) return null;
      return { datum: laatste(T0, leeftijd({ m: 14 })) };
    },
  };

  // Volgorde = voorrang bij het verdelen over momenten
  const reeksen = [dktp, pneu, rota, bmr0, bmr1, menacwy];
  for (const r of reeksen) {
    r.gepland = [];
    r.doses = function () {
      return [...this.gegeven, ...this.gepland].sort((a, b) => a - b);
    };
  }
  return reeksen;
}

function prikkenIn(moment) {
  return moment.vaccins.reduce((n, v) => n + v.prikken, 0);
}

// Basisimmunisatie: verdeelt alle ontbrekende doses over vaccinatiemomenten
function planBasis(reeksen) {
  const momenten = [];

  for (let ronde = 0; ronde < 40; ronde++) {
    const kandidaten = [];
    for (const r of reeksen) {
      const v = r.volgende();
      if (!v) continue;
      let datum = v.datum;
      if (r.nietVoor && r.nietVoor > datum) datum = r.nietVoor;
      if (v.uiterlijk && datum > v.uiterlijk) continue;
      kandidaten.push({ r, datum, uiterlijk: v.uiterlijk });
    }
    if (!kandidaten.length) break;

    // Moment = laatste datum binnen het samenvoegvenster, maar niet na een harde grens
    const eerste = Math.min(...kandidaten.map((k) => k.datum));
    const groep = kandidaten.filter((k) => k.datum - eerste <= SAMENVOEG_DAGEN * DAG);
    let datum = new Date(Math.max(...groep.map((k) => k.datum)));
    const grens = Math.min(...groep.filter((k) => k.uiterlijk).map((k) => k.uiterlijk));
    if (datum > grens) datum = new Date(grens);
    const leden = groep.filter((k) => k.datum <= datum);

    const moment = { datum, vaccins: [] };
    for (const { r, uiterlijk } of leden) {
      if (r.prikken && prikkenIn(moment) + r.prikken > MAX_PRIKKEN) {
        r.nietVoor = telOp(datum, { m: 1 }); // past niet meer: een maand later
        continue;
      }
      r.gepland.push(datum);
      r.nietVoor = null;
      moment.vaccins.push({ reeks: r, prikken: r.prikken, uiterlijk, naam: r.naam(r.doses().length) });
    }
    momenten.push(moment);
  }

  // Een moment met alleen losse vaccins (BMR, MenACWY) mag een maand opschuiven
  // om mee te gaan met het volgende moment, als daar nog ruimte is.
  for (let i = 0; i < momenten.length - 1; i++) {
    const m = momenten[i];
    const volgend = momenten[i + 1];
    const kanSchuiven =
      m.vaccins.every((v) => v.reeks.eindpunt && (!v.uiterlijk || volgend.datum <= v.uiterlijk)) &&
      volgend.datum - m.datum <= AANSLUIT_DAGEN * DAG &&
      prikkenIn(m) + prikkenIn(volgend) <= MAX_PRIKKEN;
    if (kanSchuiven) {
      for (const v of m.vaccins) {
        const r = v.reeks;
        r.gepland = r.gepland.map((d) => (d === m.datum ? volgend.datum : d));
      }
      volgend.vaccins.push(...m.vaccins);
      momenten.splice(i, 1);
      i--;
    }
  }

  const volgorde = reeksen.map((r) => r.id);
  return momenten.map((m, i) => ({
    code: 'X' + (i + 1),
    datum: m.datum,
    vorige: i > 0 ? momenten[i - 1].datum : null,
    vorigeCode: i > 0 ? 'X' + i : null,
    vaccins: m.vaccins
      .sort((a, b) => volgorde.indexOf(a.reeks.id) - volgorde.indexOf(b.reeks.id))
      .map((v) => v.naam),
  }));
}

function planVervolg(ctx, reeksen) {
  const { gb, T0, antwoorden } = ctx;
  const leeftijd = (periode) => telOp(gb, periode);
  const achttien = leeftijd({ j: 18 });
  const reeks = (id) => reeksen.find((r) => r.id === id);
  const jaarWaarin = (jaren) => new Date(gb.getFullYear() + jaren, 0, 1);
  const binnenRvp = (datum) => (datum < achttien ? { datum } : { status: 'nvt', tekst: 'Valt buiten het RVP (18+)' });

  const vervolg = [];

  // BMR 2: tussen 2,5 en 3,5 jaar, op oudere leeftijd zo snel mogelijk; min. 1 mnd na BMR 1
  const bmrGeldig = gegevenDatums(antwoorden, IDS.bmr, gb, T0).filter((d) => d >= leeftijd({ j: 1 }));
  const bmr1 = reeks('bmr1').doses()[0];
  vervolg.push({
    id: 'bmr2',
    naam: 'BMR 2',
    ...(bmrGeldig.length >= 2
      ? { status: 'gehad', tekst: 'Gehad' }
      : binnenRvp(laatste(T0, telOp(bmr1, { m: 1 }), leeftijd({ j: 2, m: 6 })))),
    uitleg: 'Tussen 2,5 en 3,5 jaar, min. 1 mnd na BMR 1',
  });

  // Vaccinaties na 4 jaar worden niet ingevoerd, dus die zijn nog niet gegeven.
  // DKT-booster: min. 5 jaar oud en min. 4 jaar na afronden basisimmuniteit DKTP
  const basisEind = dktpStatus(reeks('dktp').doses()).eind;
  const booster = binnenRvp(laatste(T0, telOp(basisEind, { j: 4 }), leeftijd({ j: 5 })));
  vervolg.push({ id: 'dkt', naam: 'DKT-booster', ...booster, uitleg: 'Min. 5 jaar oud, 4 jaar na laatste DKTP-Hib-HepB' });

  // HPV: 2 doses (T=0-6 mnd) vanaf het jaar waarin het kind 10 wordt (min. 9 jaar)
  const hpv1 = laatste(T0, jaarWaarin(10), leeftijd({ j: 9 }));
  const hpv2 = telOp(hpv1, { m: 6 });
  vervolg.push({
    id: 'hpv',
    naam: 'HPV 1+2',
    ...(hpv2 < achttien ? { datums: [['HPV 1', hpv1], ['HPV 2', hpv2]] } : { status: 'nvt', tekst: 'Valt buiten het RVP (18+)' }),
    uitleg: 'Jaar waarin kind 10 wordt, HPV 2 na 6 mnd',
  });

  // Tiener-MenACWY: in het jaar waarin het kind 14 wordt; niet nodig als er
  // op of na de 11e verjaardag al een MenACWY is gegeven
  const menacwyAlle = [
    ...gegevenDatums(antwoorden, IDS.menacwy, gb, T0),
    ...reeks('menacwy').gepland,
  ];
  vervolg.push({
    id: 'menacwy',
    naam: 'MenACWY (tiener)',
    ...(menacwyAlle.some((d) => d >= leeftijd({ j: 11 }))
      ? { status: 'gehad', tekst: 'Niet nodig: MenACWY na 11e verjaardag' }
      : binnenRvp(laatste(T0, jaarWaarin(14)))),
    uitleg: 'Jaar waarin kind 14 wordt',
  });

  // DTP-booster: min. 5 jaar na DKT-booster, niet eerder dan het jaar waarin het kind 14 wordt
  vervolg.push({
    id: 'dtp',
    naam: 'DTP-booster',
    ...(booster.datum
      ? binnenRvp(laatste(T0, telOp(booster.datum, { j: 5 }), jaarWaarin(14)))
      : { status: 'nvt', tekst: 'Valt buiten het RVP (18+)' }),
    uitleg: 'Jaar waarin kind 14 wordt, min. 5 jaar na DKT-booster',
  });

  return vervolg;
}

// kind: { geboortedatum, betreft }, antwoorden: { id: { gehad, datum } }
export function maakPlan(kind, antwoorden, T0) {
  const gb = kind.geboortedatum;
  if (T0 >= telOp(gb, { j: 18 })) {
    return { momenten: [], vervolg: [], buitenRvp: true };
  }
  const ctx = { gb, T0, antwoorden, kind };
  const reeksen = maakReeksen(ctx);
  const momenten = planBasis(reeksen);
  const vervolg = planVervolg(ctx, reeksen);

  return { momenten, vervolg, buitenRvp: false };
}
