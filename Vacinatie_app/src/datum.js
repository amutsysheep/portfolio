// Hulpfuncties voor datums. Invoer en weergave gaan als "DD-MM-JJJJ".

export const DAG = 24 * 3600 * 1000;

export function laatste(...datums) {
  return new Date(Math.max(...datums.filter(Boolean).map((d) => d.getTime())));
}

export function vandaag() {
  const n = new Date();
  return new Date(n.getFullYear(), n.getMonth(), n.getDate());
}

// Telt een periode { d, w, m, j } (dagen, weken, maanden, jaren) op bij een datum.
export function telOp(datum, { d = 0, w = 0, m = 0, j = 0 }) {
  const r = new Date(datum);
  r.setFullYear(r.getFullYear() + j);
  r.setMonth(r.getMonth() + m);
  r.setDate(r.getDate() + d + w * 7);
  return r;
}

// Zet getypte cijfers om naar "DD-MM-JJJJ" (streepjes komen er vanzelf tussen).
export function formatteerInvoer(tekst) {
  const cijfers = tekst.replace(/\D/g, '').slice(0, 8);
  if (cijfers.length <= 2) return cijfers;
  if (cijfers.length <= 4) return `${cijfers.slice(0, 2)}-${cijfers.slice(2)}`;
  return `${cijfers.slice(0, 2)}-${cijfers.slice(2, 4)}-${cijfers.slice(4)}`;
}

// "DD-MM-JJJJ" -> Date, of null als het geen bestaande datum is.
export function leesDatum(tekst) {
  const match = /^(\d{2})-(\d{2})-(\d{4})$/.exec(tekst || '');
  if (!match) return null;
  const [, d, m, j] = match.map(Number);
  const datum = new Date(j, m - 1, d);
  if (datum.getFullYear() !== j || datum.getMonth() !== m - 1 || datum.getDate() !== d) return null;
  return datum;
}

const MAANDEN = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];

export function toonDatum(datum) {
  return `${datum.getDate()} ${MAANDEN[datum.getMonth()]} ${datum.getFullYear()}`;
}

// Date -> "DD-MM-JJJJ"
export function korteDatum(datum) {
  const tweeCijfers = (n) => String(n).padStart(2, '0');
  return `${tweeCijfers(datum.getDate())}-${tweeCijfers(datum.getMonth() + 1)}-${datum.getFullYear()}`;
}

// Tijd tussen twee datums, bijv. "2 mnd", "1 mnd 2 wk" of "3 wk"
export function intervalTekst(van, tot) {
  let maanden = (tot.getFullYear() - van.getFullYear()) * 12 + (tot.getMonth() - van.getMonth());
  if (tot.getDate() < van.getDate()) maanden--;
  if (maanden <= 0) return Math.max(1, Math.round((tot - van) / (7 * DAG))) + ' wk';
  const weken = Math.round((tot - telOp(van, { m: maanden })) / (7 * DAG));
  return weken > 0 ? `${maanden} mnd ${weken} wk` : `${maanden} mnd`;
}

export function leeftijdTekst(van, tot) {
  let maanden = (tot.getFullYear() - van.getFullYear()) * 12 + (tot.getMonth() - van.getMonth());
  if (tot.getDate() < van.getDate()) maanden--;
  if (maanden < 3) {
    const weken = Math.floor((tot - van) / (7 * 24 * 3600 * 1000));
    return weken + (weken === 1 ? ' week' : ' weken');
  }
  if (maanden < 24) return maanden + ' maanden';
  const jaren = Math.floor(maanden / 12);
  const rest = maanden % 12;
  return jaren + ' jaar' + (rest ? ' en ' + rest + ' mnd' : '');
}
