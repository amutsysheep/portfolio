import { leesDatum, leeftijdTekst, telOp, vandaag } from './datum';

export const RICHTLIJN_URL =
  'https://rijksvaccinatieprogramma.nl/professionals/richtlijnen/uitvoering/7-tijdstip-van-vaccinaties';

// ---------------------------------------------------------------------------
// Vaccinatieschema RVP (richtlijn hoofdstuk 7), standaardschema 3-5-12 maanden.
// Alleen de vaccinaties tot en met 4 jaar.
// leeftijd:    standaardtijdstip { d, w, m, j }
// minLeeftijd: vroegst toegestane leeftijd (waarschuwing als eerder gegeven)
// maxLeeftijd: daarna mag de vaccinatie niet meer gegeven worden
// ---------------------------------------------------------------------------
export const SCHEMA = [
  {
    titel: 'Rotavirus',
    vaccins: [
      { id: 'rota1', naam: 'Rotavirus 1', leeftijd: { w: 6 }, label: '6 weken', minLeeftijd: { w: 6 }, maxLeeftijd: { w: 24 } },
      { id: 'rota2', naam: 'Rotavirus 2', leeftijd: { m: 3 }, label: '3 maanden', minLeeftijd: { w: 10 }, maxLeeftijd: { w: 24 } },
    ],
  },
  {
    titel: 'DKTP-Hib-HepB',
    ondertitel: 'Difterie, kinkhoest, tetanus, polio, Hib, hepatitis B',
    vaccins: [
      { id: 'dktp1', naam: 'DKTP-Hib-HepB 1', leeftijd: { m: 3 }, label: '3 maanden', minLeeftijd: { w: 6 } },
      { id: 'dktp2', naam: 'DKTP-Hib-HepB 2', leeftijd: { m: 5 }, label: '5 maanden' },
      { id: 'dktp3', naam: 'DKTP-Hib-HepB 3', leeftijd: { m: 12 }, label: '12 maanden' },
    ],
  },
  {
    titel: 'Pneumokokken',
    vaccins: [
      { id: 'pneu1', naam: 'Pneumokokken 1', leeftijd: { m: 3 }, label: '3 maanden', minLeeftijd: { w: 6 } },
      { id: 'pneu2', naam: 'Pneumokokken 2', leeftijd: { m: 5 }, label: '5 maanden' },
      { id: 'pneu3', naam: 'Pneumokokken 3', leeftijd: { m: 12 }, label: '12 maanden' },
    ],
  },
  {
    titel: 'BMR en MenACWY',
    ondertitel: 'Bof, mazelen, rodehond en meningokokken',
    vaccins: [
      { id: 'bmr1', naam: 'BMR 1', leeftijd: { m: 14 }, label: '14 maanden', minLeeftijd: { m: 12 } },
      { id: 'menacwy1', naam: 'MenACWY', leeftijd: { m: 14 }, label: '14 maanden', minLeeftijd: { m: 12 } },
      { id: 'bmr2', naam: 'BMR 2', leeftijd: { j: 2, m: 6 }, label: '2,5 – 3,5 jaar' },
    ],
  },
];

// Status van één vaccinatie:
// ok | datum (ja, maar geen geldige datum) | nodig | onbekend | vervallen | later
export function bepaalStatus(vaccin, antwoord, geboortedatum) {
  if (antwoord?.gehad === true) return leesDatum(antwoord.datum) ? 'ok' : 'datum';
  if (telOp(geboortedatum, vaccin.leeftijd) <= vandaag()) {
    // rotavirus kan na 24 weken niet meer worden ingehaald
    if (vaccin.maxLeeftijd && telOp(geboortedatum, vaccin.maxLeeftijd) <= vandaag()) return 'vervallen';
    return antwoord?.gehad === false ? 'nodig' : 'onbekend';
  }
  return 'later';
}

// Uitleg bij de ingevulde datum: leeftijd op dat moment plus eventuele waarschuwing.
export function controleerDatum(vaccin, antwoord, geboortedatum) {
  if (antwoord?.gehad !== true) return null;
  const gegeven = leesDatum(antwoord.datum);
  if (!gegeven) {
    return antwoord.datum?.length === 10 ? { waarschuwing: 'Deze datum bestaat niet.' } : null;
  }
  if (gegeven < geboortedatum) return { waarschuwing: 'Datum ligt vóór de geboortedatum.' };
  if (gegeven > vandaag()) return { waarschuwing: 'Datum ligt in de toekomst.' };

  const info = 'Gegeven op leeftijd ' + leeftijdTekst(geboortedatum, gegeven);
  if (vaccin.minLeeftijd && gegeven < telOp(geboortedatum, vaccin.minLeeftijd)) {
    return { info, waarschuwing: 'Eerder gegeven dan toegestaan – controleer of deze opnieuw moet.' };
  }
  if (vaccin.maxLeeftijd && gegeven >= telOp(geboortedatum, vaccin.maxLeeftijd)) {
    return { info, waarschuwing: 'Na de maximale leeftijd gegeven.' };
  }
  return { info };
}
