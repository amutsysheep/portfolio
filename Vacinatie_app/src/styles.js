// Alle opmaak van de app staat in dit bestand.
// React Native gebruikt geen echte CSS, maar StyleSheet: dezelfde soort regels,
// alleen in camelCase (backgroundColor i.p.v. background-color) en zonder 'px'.

import { StyleSheet } from 'react-native';

export const kleuren = {
  achtergrond: '#F1F5F9',
  kaart: '#FFFFFF',
  tekst: '#0F172A',
  tekstZacht: '#64748B',
  rand: '#E2E8F0',
  invoer: '#F8FAFC',

  primair: '#2563EB',
  primairDonker: '#1E3A8A',
  primairLicht: '#EFF6FF',
  kopTekstZacht: '#BFDBFE',

  groen: '#16A34A',
  groenLicht: '#DCFCE7',
  rood: '#DC2626',
  roodLicht: '#FEE2E2',
  oranje: '#D97706',
  oranjeLicht: '#FEF3C7',
  oranjeDonker: '#92400E',
  grijs: '#94A3B8',
  grijsLicht: '#F1F5F9',
};

const schaduw = '0 4px 16px rgba(15, 23, 42, 0.06)';

// Kleur en tekst van het statuslabel per vaccinatie
export const statusStijl = {
  ok:        { tekst: 'Gehad',         kleur: kleuren.groen,      achtergrond: kleuren.groenLicht },
  nodig:     { tekst: 'Nog nodig',     kleur: kleuren.rood,       achtergrond: kleuren.roodLicht },
  onbekend:  { tekst: 'Invullen',      kleur: kleuren.oranje,     achtergrond: kleuren.oranjeLicht },
  datum:     { tekst: 'Datum invullen', kleur: kleuren.oranje,    achtergrond: kleuren.oranjeLicht },
  vervallen: { tekst: 'Vervallen',     kleur: kleuren.tekstZacht, achtergrond: kleuren.grijsLicht },
  later:     { tekst: 'Nog niet',      kleur: kleuren.tekstZacht, achtergrond: kleuren.grijsLicht },
};

export const styles = StyleSheet.create({
  // ---------- Algemeen ----------
  scherm: {
    flex: 1,
    backgroundColor: kleuren.achtergrond,
  },
  bovenkant: {
    backgroundColor: kleuren.primairDonker,
  },
  inhoud: {
    paddingHorizontal: 16,
    marginTop: -28,
    gap: 16,
  },

  // ---------- Kop (blauwe balk bovenaan) ----------
  kop: {
    backgroundColor: kleuren.primairDonker,
    paddingHorizontal: 20,
    paddingTop: 12,
    paddingBottom: 48,
    borderBottomLeftRadius: 28,
    borderBottomRightRadius: 28,
  },
  kopIcoon: {
    width: 48,
    height: 48,
    borderRadius: 14,
    backgroundColor: 'rgba(255, 255, 255, 0.14)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
  },
  kopIcoonTekst: {
    fontSize: 24,
  },
  kopTitel: {
    color: '#FFFFFF',
    fontSize: 28,
    fontWeight: '800',
    letterSpacing: -0.5,
  },
  kopSubtitel: {
    color: kleuren.kopTekstZacht,
    fontSize: 15,
    marginTop: 4,
    lineHeight: 21,
  },
  terugKnop: {
    alignSelf: 'flex-start',
    paddingVertical: 6,
    paddingRight: 12,
    marginBottom: 10,
  },
  terugTekst: {
    color: kleuren.kopTekstZacht,
    fontSize: 16,
    fontWeight: '600',
  },
  chips: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 12,
  },
  chip: {
    backgroundColor: 'rgba(255, 255, 255, 0.14)',
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  chipTekst: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '600',
  },

  // ---------- Kaarten ----------
  kaart: {
    backgroundColor: kleuren.kaart,
    borderRadius: 20,
    padding: 18,
    boxShadow: schaduw,
  },
  kaartTitel: {
    fontSize: 18,
    fontWeight: '700',
    color: kleuren.tekst,
    marginBottom: 16,
  },
  uitleg: {
    fontSize: 14,
    color: kleuren.tekstZacht,
    lineHeight: 20,
  },
  vet: {
    fontWeight: '700',
  },

  // ---------- Formuliervelden ----------
  veld: {
    marginBottom: 18,
  },
  veldLaatste: {
    marginBottom: 24,
  },  label: {
    fontSize: 14,
    fontWeight: '600',
    color: kleuren.tekst,
    marginBottom: 8,
  },
  invoer: {
    height: 52,
    borderRadius: 14,
    borderWidth: 1.5,
    borderColor: kleuren.rand,
    backgroundColor: kleuren.invoer,
    paddingHorizontal: 14,
    fontSize: 17,
    color: kleuren.tekst,
  },
  invoerFocus: {
    borderColor: kleuren.primair,
    backgroundColor: '#FFFFFF',
  },
  invoerFout: {
    borderColor: kleuren.rood,
  },
  foutTekst: {
    color: kleuren.rood,
    fontSize: 13,
    marginTop: 6,
  },

  // ---------- Keuzeknoppen (Jongen/Meisje, Ja/Nee) ----------
  keuze: {
    flexDirection: 'row',
    backgroundColor: kleuren.grijsLicht,
    borderRadius: 14,
    padding: 4,
    gap: 4,
  },
  keuzeOptie: {
    flex: 1,
    height: 44,
    borderRadius: 11,
    alignItems: 'center',
    justifyContent: 'center',
  },
  keuzeOptieIngedrukt: {
    backgroundColor: kleuren.rand,
  },
  keuzeTekst: {
    fontSize: 16,
    fontWeight: '600',
    color: kleuren.tekstZacht,
  },
  keuzeTekstActief: {
    color: '#FFFFFF',
  },

  // ---------- Knoppen ----------
  knop: {
    height: 56,
    borderRadius: 16,
    backgroundColor: kleuren.primair,
    alignItems: 'center',
    justifyContent: 'center',
    boxShadow: '0 6px 16px rgba(37, 99, 235, 0.3)',
  },
  knopTekst: {
    color: '#FFFFFF',
    fontSize: 17,
    fontWeight: '700',
  },
  knopSecundair: {
    height: 52,
    borderRadius: 16,
    backgroundColor: kleuren.primairLicht,
    alignItems: 'center',
    justifyContent: 'center',
  },
  knopSecundairTekst: {
    color: kleuren.primair,
    fontSize: 16,
    fontWeight: '700',
  },
  ingedrukt: {
    opacity: 0.85,
    transform: [{ scale: 0.98 }],
  },

  // ---------- Tellers bovenaan het vaccinatiescherm ----------
  tellers: {
    flexDirection: 'row',
    gap: 10,
    padding: 10,
  },
  teller: {
    flex: 1,
    borderRadius: 16,
    paddingVertical: 12,
    paddingHorizontal: 10,
    alignItems: 'center',
  },
  tellerGetal: {
    fontSize: 26,
    fontWeight: '800',
  },
  tellerLabel: {
    fontSize: 12,
    fontWeight: '600',
    marginTop: 2,
    textAlign: 'center',
  },

  // ---------- Groepen en vaccinatiekaarten ----------
  groep: {
    gap: 10,
  },
  groepKop: {
    marginTop: 8,
    marginLeft: 4,
  },
  groepTitel: {
    fontSize: 13,
    fontWeight: '800',
    color: kleuren.primairDonker,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  groepOndertitel: {
    fontSize: 13,
    color: kleuren.tekstZacht,
    marginTop: 2,
  },
  vaccin: {
    backgroundColor: kleuren.kaart,
    borderRadius: 18,
    padding: 16,
    gap: 14,
    borderLeftWidth: 5,
    boxShadow: schaduw,
  },
  vaccinBoven: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
  },
  vaccinNaamBlok: {
    flex: 1,
  },
  vaccinNaam: {
    fontSize: 17,
    fontWeight: '700',
    color: kleuren.tekst,
  },
  vaccinSchema: {
    fontSize: 13,
    color: kleuren.tekstZacht,
    marginTop: 3,
  },
  statusLabel: {
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  statusTekst: {
    fontSize: 12,
    fontWeight: '700',
  },
  infoTekst: {
    fontSize: 13,
    color: kleuren.tekstZacht,
    marginTop: -6,
  },
  waarschuwing: {
    backgroundColor: kleuren.oranjeLicht,
    borderRadius: 12,
    padding: 10,
  },
  waarschuwingTekst: {
    color: kleuren.oranjeDonker,
    fontSize: 13,
    fontWeight: '600',
    lineHeight: 18,
  },

  // ---------- Vaccinatieplan ----------
  planIntro: {
    marginTop: -8,
    marginBottom: 16,
  },
  planKop: {
    fontSize: 13,
    fontWeight: '800',
    color: kleuren.primairDonker,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
    marginBottom: 10,
  },
  planKopVervolg: {
    marginTop: 18,
  },
  moment: {
    flexDirection: 'row',
    gap: 12,
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: kleuren.rand,
  },
  momentCode: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: kleuren.primair,
    alignItems: 'center',
    justifyContent: 'center',
  },
  momentCodeTekst: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '800',
  },
  momentInhoud: {
    flex: 1,
  },
  momentDatum: {
    fontSize: 16,
    fontWeight: '700',
    color: kleuren.tekst,
  },
  momentInterval: {
    fontSize: 13,
    color: kleuren.tekstZacht,
    marginTop: 2,
  },
  vaccinChips: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    marginTop: 8,
  },
  vaccinChip: {
    backgroundColor: kleuren.primairLicht,
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  vaccinChipTekst: {
    color: kleuren.primair,
    fontSize: 13,
    fontWeight: '700',
  },
  vervolgRegel: {
    flexDirection: 'row',
    gap: 12,
    paddingVertical: 10,
    borderTopWidth: 1,
    borderTopColor: kleuren.rand,
  },
  vervolgNaamBlok: {
    flex: 1,
  },
  vervolgNaam: {
    fontSize: 15,
    fontWeight: '700',
    color: kleuren.tekst,
  },
  vervolgUitleg: {
    fontSize: 12,
    color: kleuren.tekstZacht,
    marginTop: 2,
  },
  vervolgWaarde: {
    alignItems: 'flex-end',
    maxWidth: '50%',
  },
  vervolgDatum: {
    fontSize: 14,
    fontWeight: '600',
    color: kleuren.tekst,
  },
  vervolgStatus: {
    fontSize: 13,
    color: kleuren.tekstZacht,
    textAlign: 'right',
  },

  // ---------- Exporteren ----------
  exportKaart: {
    gap: 12,
  },
  exportTitel: {
    marginBottom: 0,
  },
  knopUit: {
    opacity: 0.45,
    boxShadow: 'none',
  },
  knopInhoud: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },

  // ---------- Overzicht onderaan ----------
  blok: {
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
  },
  blokTitel: {
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 6,
  },
  blokRegel: {
    fontSize: 15,
    color: kleuren.tekst,
    lineHeight: 24,
  },
  link: {
    color: kleuren.primair,
    fontWeight: '600',
    textDecorationLine: 'underline',
  },
});
