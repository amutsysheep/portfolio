import { Text, View } from 'react-native';
import { telOp, toonDatum } from '../datum';
import { controleerDatum } from '../schema';
import { kleuren, statusStijl, styles } from '../styles';
import DatumInvoer from './DatumInvoer';
import Keuze from './Keuze';

const JA_NEE = [
  { waarde: true, label: 'Ja', kleur: kleuren.groen },
  { waarde: false, label: 'Nee', kleur: kleuren.rood },
];

export default function VaccinKaart({ vaccin, antwoord, status, geboortedatum, onWijzig }) {
  const stijl = statusStijl[status];
  const verwacht = telOp(geboortedatum, vaccin.leeftijd);
  const controle = controleerDatum(vaccin, antwoord, geboortedatum);

  const kies = (gehad) => onWijzig({ gehad, datum: gehad ? antwoord?.datum || '' : '' });

  return (
    <View style={[styles.vaccin, { borderLeftColor: stijl.kleur }]}>
      <View style={styles.vaccinBoven}>
        <View style={styles.vaccinNaamBlok}>
          <Text style={styles.vaccinNaam}>{vaccin.naam}</Text>
          <Text style={styles.vaccinSchema}>
            {vaccin.label} · ± {toonDatum(verwacht)}
          </Text>
        </View>
        <View style={[styles.statusLabel, { backgroundColor: stijl.achtergrond }]}>
          <Text style={[styles.statusTekst, { color: stijl.kleur }]}>{stijl.tekst}</Text>
        </View>
      </View>

      <Keuze opties={JA_NEE} waarde={antwoord?.gehad} onKies={kies} />

      {antwoord?.gehad === true && (
        <View>
          <Text style={styles.label}>Datum van vaccinatie</Text>
          <DatumInvoer
            waarde={antwoord.datum}
            onWijzig={(datum) => onWijzig({ gehad: true, datum })}
            fout={!!controle?.waarschuwing && !controle?.info}
            autoFocus={!antwoord.datum}
          />
        </View>
      )}

      {controle?.info && <Text style={styles.infoTekst}>{controle.info}</Text>}
      {controle?.waarschuwing && (
        <View style={styles.waarschuwing}>
          <Text style={styles.waarschuwingTekst}>⚠ {controle.waarschuwing}</Text>
        </View>
      )}
    </View>
  );
}
