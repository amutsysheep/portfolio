import { Text, View } from 'react-native';
import { intervalTekst, toonDatum } from '../datum';
import { styles } from '../styles';

// Toont het berekende vaccinatieplan: momenten X1, X2, ... en het vervolg-RVP.
export default function PlanKaart({ plan }) {
  if (plan.buitenRvp) {
    return (
      <View style={styles.kaart}>
        <Text style={styles.kaartTitel}>Vaccinatieplan</Text>
        <Text style={styles.uitleg}>Het kind is 18 jaar of ouder en valt buiten het Rijksvaccinatieprogramma.</Text>
      </View>
    );
  }

  return (
    <View style={styles.kaart}>
      <Text style={styles.kaartTitel}>Vaccinatieplan</Text>
      <Text style={[styles.uitleg, styles.planIntro]}>
        Berekend met de Beslisboom inhaalschema's RVP 2026, vanaf vandaag. Vaccinaties die op "Nee" staan of nog
        niet zijn ingevuld, zijn als niet gegeven meegenomen.
      </Text>

      <Text style={styles.planKop}>Basisimmunisatie</Text>
      {plan.momenten.length === 0 && (
        <Text style={styles.uitleg}>Geen inhaalvaccinaties nodig voor de basisimmunisatie.</Text>
      )}
      {plan.momenten.map((moment) => (
        <View key={moment.code} style={styles.moment}>
          <View style={styles.momentCode}>
            <Text style={styles.momentCodeTekst}>{moment.code}</Text>
          </View>
          <View style={styles.momentInhoud}>
            <Text style={styles.momentDatum}>{toonDatum(moment.datum)}</Text>
            <Text style={styles.momentInterval}>
              {moment.vorige
                ? `${intervalTekst(moment.vorige, moment.datum)} na ${moment.vorigeCode}`
                : 'Eerste moment'}
            </Text>
            <View style={styles.vaccinChips}>
              {moment.vaccins.map((vaccin) => (
                <View key={vaccin} style={styles.vaccinChip}>
                  <Text style={styles.vaccinChipTekst}>{vaccin}</Text>
                </View>
              ))}
            </View>
          </View>
        </View>
      ))}

      <Text style={[styles.planKop, styles.planKopVervolg]}>Vervolg RVP</Text>
      {plan.vervolg.map((item) => (
        <View key={item.id} style={styles.vervolgRegel}>
          <View style={styles.vervolgNaamBlok}>
            <Text style={styles.vervolgNaam}>{item.naam}</Text>
            <Text style={styles.vervolgUitleg}>{item.uitleg}</Text>
          </View>
          <View style={styles.vervolgWaarde}>
            {item.datum && <Text style={styles.vervolgDatum}>{toonDatum(item.datum)}</Text>}
            {item.datums?.map(([naam, datum]) => (
              <Text key={naam} style={styles.vervolgDatum}>
                {naam}: {toonDatum(datum)}
              </Text>
            ))}
            {item.tekst && <Text style={styles.vervolgStatus}>{item.tekst}</Text>}
          </View>
        </View>
      ))}
    </View>
  );
}
