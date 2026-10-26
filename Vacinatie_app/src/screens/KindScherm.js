import { useState } from 'react';
import { Pressable, Text, TextInput, View } from 'react-native';
import DatumInvoer from '../components/DatumInvoer';
import Keuze from '../components/Keuze';
import { leesDatum, vandaag } from '../datum';
import { pastOpFormulier } from '../formulier';
import { kleuren, styles } from '../styles';

const GESLACHTEN = [
  { waarde: 'jongen', label: 'Jongen' },
  { waarde: 'meisje', label: 'Meisje' },
];

const BETREFT = [
  { waarde: 'asielzoeker', label: 'Asielzoeker' },
  { waarde: 'nieuwkomer', label: 'Andere nieuwkomer' },
];

function controleerNaam(naam, wat) {
  if (!naam.trim()) return `Vul de ${wat} in.`;
  if (!pastOpFormulier(naam)) return 'Gebruik Latijnse letters (zoals in het paspoort).';
  return null;
}

// Eerste scherm: gegevens van het kind.
export default function KindScherm({ kind, onVerder }) {
  const [voornaam, setVoornaam] = useState(kind?.voornaam || '');
  const [achternaam, setAchternaam] = useState(kind?.achternaam || '');
  const [geboorte, setGeboorte] = useState(kind?.geboorteTekst || '');
  const [geslacht, setGeslacht] = useState(kind?.geslacht || null);
  const [betreft, setBetreft] = useState(kind?.betreft || null);
  const [fouten, setFouten] = useState({});

  const verder = () => {
    const nieuweFouten = {};
    const geboortedatum = leesDatum(geboorte);
    nieuweFouten.voornaam = controleerNaam(voornaam, 'voornaam');
    nieuweFouten.achternaam = controleerNaam(achternaam, 'achternaam');
    if (!geboorte) nieuweFouten.geboorte = 'Vul de geboortedatum in.';
    else if (!geboortedatum) nieuweFouten.geboorte = 'Dit is geen geldige datum (DD-MM-JJJJ).';
    else if (geboortedatum > vandaag()) nieuweFouten.geboorte = 'De geboortedatum ligt in de toekomst.';
    if (!geslacht) nieuweFouten.geslacht = 'Kies het geslacht.';
    if (!betreft) nieuweFouten.betreft = 'Kies wat van toepassing is.';

    setFouten(nieuweFouten);
    if (Object.values(nieuweFouten).some(Boolean)) return;
    onVerder({
      voornaam: voornaam.trim(),
      achternaam: achternaam.trim(),
      geboorteTekst: geboorte,
      geboortedatum,
      geslacht,
      betreft,
    });
  };

  return (
    <>
      <View style={styles.kop}>
        <View style={styles.kopIcoon}>
          <Text style={styles.kopIcoonTekst}>💉</Text>
        </View>
        <Text style={styles.kopTitel}>Vaccinatiecheck</Text>
        <Text style={styles.kopSubtitel}>
          Controleer buitenlandse vaccinaties met het Rijksvaccinatieprogramma
        </Text>
      </View>

      <View style={styles.inhoud}>
        <View style={styles.kaart}>
          <Text style={styles.kaartTitel}>Gegevens van het kind</Text>

          <TekstVeld label="Voornaam" waarde={voornaam} onWijzig={setVoornaam} fout={fouten.voornaam} />
          <TekstVeld label="Achternaam" waarde={achternaam} onWijzig={setAchternaam} fout={fouten.achternaam} />

          <View style={styles.veld}>
            <Text style={styles.label}>Geboortedatum</Text>
            <DatumInvoer waarde={geboorte} onWijzig={setGeboorte} fout={!!fouten.geboorte} />
            {fouten.geboorte && <Text style={styles.foutTekst}>{fouten.geboorte}</Text>}
          </View>

          <View style={styles.veld}>
            <Text style={styles.label}>Geslacht</Text>
            <Keuze opties={GESLACHTEN} waarde={geslacht} onKies={setGeslacht} />
            {fouten.geslacht && <Text style={styles.foutTekst}>{fouten.geslacht}</Text>}
          </View>

          <View style={[styles.veld, styles.veldLaatste]}>
            <Text style={styles.label}>Betreft</Text>
            <Keuze opties={BETREFT} waarde={betreft} onKies={setBetreft} />
            {fouten.betreft && <Text style={styles.foutTekst}>{fouten.betreft}</Text>}
          </View>

          <Pressable onPress={verder} style={({ pressed }) => [styles.knop, pressed && styles.ingedrukt]}>
            <Text style={styles.knopTekst}>Verder naar vaccinaties</Text>
          </Pressable>
        </View>
      </View>
    </>
  );
}

function TekstVeld({ label, waarde, onWijzig, fout }) {
  const [focus, setFocus] = useState(false);
  return (
    <View style={styles.veld}>
      <Text style={styles.label}>{label}</Text>
      <TextInput
        value={waarde}
        onChangeText={onWijzig}
        placeholderTextColor={kleuren.grijs}
        autoCapitalize="words"
        autoCorrect={false}
        onFocus={() => setFocus(true)}
        onBlur={() => setFocus(false)}
        style={[styles.invoer, focus && styles.invoerFocus, fout && styles.invoerFout]}
      />
      {fout && <Text style={styles.foutTekst}>{fout}</Text>}
    </View>
  );
}
