// Vult het RIVM "Vaccinatiestatus en -opdrachtformulier nieuwkomers" (februari 2025).
// De veldnamen hieronder zijn de echte namen van de invulvelden in die PDF.

import { intervalTekst, korteDatum, leesDatum } from './datum';

// Rij in de tabel "Vaccinatiestatus" -> antwoord-id's uit de app
const STATUS_RIJEN = [
  ['DKTP-Hib-HepB ', ['dktp1', 'dktp2', 'dktp3']],
  ['Pneu', ['pneu1', 'pneu2', 'pneu3']],
  ['Rota', ['rota1', 'rota2']],
  ['BMR', ['bmr1', 'bmr2']],
  ['MenACWY', ['menacwy1']],
];

// Regels X1 t/m X7 in de tabel "Vaccinatieplan"
const PLAN_REGELS = [
  { datum: '6.0 datum x2', vaccins: ['6.1 Vaccin x10', '6.2 Vaccin x1-1', '6.3 Vaccin x1-2'] },
  { datum: '6.4 plandatum', vaccins: ['6.4 Vaccin x20', '6.5 Vaccin x2-1', '6.6 Vaccin x2-2'] },
  { datum: '6.7 plandatum', vaccins: ['6.7 Vaccin x30', '6.8 Vaccin x3-1', '6.9 Vaccin x3-2'] },
  { datum: '6.9 plandatum', vaccins: ['6.9.1 Vaccin x40', '6.9.2 Vaccin x4-1', '6.9.3 Vaccin x4-2'] },
  {
    code: '6.9.4 Vaccinatiemoment Basisimmunisatie 2',
    datum: '6.9.5 plandatum 0',
    vaccins: ['6.9.6 Vaccin0', '6.9.7 Vaccin-1', '6.9.8 Vaccin-2'],
  },
  {
    code: '6.9.9 Vaccinatiemoment Basisimmunisatie 23',
    datum: '6.9.9.1 plandatum -1',
    vaccins: ['6.9.9.2 Vaccin-3', '6.9.9.3 Vaccin-4', '6.9.9.4 Vaccin-5'],
  },
  {
    code: '6.9.9.5 Vaccinatiemoment Basisimmunisatie 4',
    datum: '6.9.9.6 plandatum -2',
    vaccins: ['6.9.9.7 Vaccin-6', '6.9.9.8 Vaccin-7', '6.9.9.8 Vaccin-8'],
  },
];
export const MAX_MOMENTEN = PLAN_REGELS.length;

const VERVOLG_VELDEN = {
  bmr2: '7.0 BMR 2 (3 jaar) 2',
  dkt: '7.1 DKT-booster  2',
  hpv: '7.2 HPV 1+2 (10 jaar) 2',
  menacwy: '7.3 MenACWY (14 jaar) 2',
  dtp: '7.4 DTP-booster (14 jaar) 2',
};

// Het standaardlettertype van PDF-formulieren kent alleen West-Europese tekens.
// Accenten blijven staan (é, ü, ç), andere letters worden zonder accent geschreven
// (ş -> s, ı -> i). Tekens uit andere schriften (Arabisch, Cyrillisch) kunnen niet.
const LOS = { ı: 'i', İ: 'I', ł: 'l', Ł: 'L', đ: 'd', Đ: 'D', ə: 'e', Ə: 'E', ħ: 'h', Ħ: 'H', ŀ: 'l', Ŀ: 'L' };
const WEST_EUROPEES = /^[\x20-\x7E\xA0-\xFF]+$/;

function omzetten(teken) {
  if (WEST_EUROPEES.test(teken)) return teken;
  if (LOS[teken]) return LOS[teken];
  const zonderAccent = teken.normalize('NFD').replace(/[̀-ͯ]/g, '');
  return WEST_EUROPEES.test(zonderAccent) ? zonderAccent : null;
}

export function pastOpFormulier(tekst) {
  return [...String(tekst).normalize('NFC')].every((teken) => omzetten(teken) !== null);
}

function pdfTekst(tekst) {
  return [...String(tekst).normalize('NFC')].map((teken) => omzetten(teken) ?? '?').join('');
}

export function vervolgTekst(item) {
  if (item.datum) return korteDatum(item.datum);
  if (item.datums) return item.datums.map(([naam, datum]) => `${naam}: ${korteDatum(datum)}`).join('   ');
  return item.tekst;
}

// pdf: PDFDocument (pdf-lib) van het lege formulier
export function vulFormulier(pdf, kind, antwoorden, plan, opgesteldOp) {
  const form = pdf.getForm();
  const zet = (veld, tekst) => form.getTextField(veld).setText(pdfTekst(tekst));

  // Pagina 1: datum en persoonsgegevens
  zet('1.0 datum opstellen vaccinatieplan:', korteDatum(opgesteldOp));
  zet('2.4 Voornaam', kind.voornaam);
  zet('2.5 Achternaam', kind.achternaam);
  zet('2.6 Geboortedatum', korteDatum(kind.geboortedatum));
  if (kind.betreft) {
    form.getRadioGroup('2.1').select(kind.betreft === 'asielzoeker' ? '1 - Asielzoeker' : '2 - Andere nieuwkomer');
  }

  // Pagina 2: ontvangen vaccins (maximaal 5 datums per rij)
  for (const [rij, ids] of STATUS_RIJEN) {
    const datums = ids
      .map((id) => antwoorden[id])
      .filter((a) => a?.gehad)
      .map((a) => leesDatum(a.datum))
      .filter((d) => d && d >= kind.geboortedatum && d <= opgesteldOp)
      .sort((a, b) => a - b)
      .slice(0, 5);
    datums.forEach((datum, i) => zet(`datum ${rij}.${i + 1}`, korteDatum(datum)));
  }

  // Vaccinatieplan basisimmunisatie. Op het formulier staat al "X1 +", "X2 +" enz.,
  // dus bij X2-X4 alleen het interval; bij X5-X7 de hele notatie.
  plan.momenten.slice(0, MAX_MOMENTEN).forEach((moment, i) => {
    const regel = PLAN_REGELS[i];
    if (regel.code) zet(regel.code, moment.code);
    const interval = moment.vorige ? intervalTekst(moment.vorige, moment.datum) : null;
    if (i === 0) zet(regel.datum, korteDatum(moment.datum));
    else if (regel.code) zet(regel.datum, `${moment.vorigeCode} + ${interval}`);
    else zet(regel.datum, interval);
    moment.vaccins.slice(0, 3).forEach((vaccin, j) => zet(regel.vaccins[j], vaccin));
  });

  // Vervolg RVP
  for (const item of plan.vervolg) {
    if (VERVOLG_VELDEN[item.id]) zet(VERVOLG_VELDEN[item.id], vervolgTekst(item));
  }
}
