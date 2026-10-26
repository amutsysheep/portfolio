import { useState } from 'react';
import { ActivityIndicator, Alert, Linking, Pressable, Text, View } from 'react-native';
import PlanKaart from '../components/PlanKaart';
import VaccinKaart from '../components/VaccinKaart';
import { leeftijdTekst, toonDatum, vandaag } from '../datum';
import { deelFormulier } from '../export';
import { MAX_MOMENTEN } from '../formulier';
import { maakPlan } from '../plan';
import { RICHTLIJN_URL, SCHEMA, bepaalStatus, controleerDatum } from '../schema';
import { kleuren, styles } from '../styles';

// Tweede scherm: alle vaccinaties met Ja/Nee, een overzicht, het vaccinatieplan
// en de export naar het RIVM-formulier.
export default function VaccinatieScherm({ kind, antwoorden, onAntwoord, onWijzig, onNieuw }) {
  const [bezig, setBezig] = useState(false);
  const groepen = SCHEMA;
  const plan = maakPlan(kind, antwoorden, vandaag());

  const nodig = [];
  const invullen = [];
  const ongeldig = [];
  let gehad = 0;
  for (const groep of groepen) {
    for (const v of groep.vaccins) {
      const status = bepaalStatus(v, antwoorden[v.id], kind.geboortedatum);
      if (status === 'ok') gehad++;
      else if (status === 'nodig') nodig.push(v.naam);
      else if (status === 'onbekend' || status === 'datum') invullen.push(v.naam);
      const controle = controleerDatum(v, antwoorden[v.id], kind.geboortedatum);
      if (controle?.waarschuwing && !controle.info) ongeldig.push(v.naam);
    }
  }
  const klaarVoorExport = invullen.length === 0 && ongeldig.length === 0;

  const exporteer = async () => {
    setBezig(true);
    try {
      await deelFormulier(kind, antwoorden, plan, vandaag());
    } catch (fout) {
      Alert.alert('Exporteren mislukt', fout?.message || String(fout));
    } finally {
      setBezig(false);
    }
  };

  return (
    <>
      <View style={styles.kop}>
        <Pressable onPress={onWijzig} hitSlop={10} style={styles.terugKnop}>
          <Text style={styles.terugTekst}>‹ Gegevens wijzigen</Text>
        </Pressable>
        <Text style={styles.kopTitel}>
          {kind.voornaam} {kind.achternaam}
        </Text>
        <View style={styles.chips}>
          <Chip tekst={leeftijdTekst(kind.geboortedatum, vandaag())} />
          <Chip tekst={'Geboren ' + toonDatum(kind.geboortedatum)} />
          <Chip tekst={kind.geslacht === 'jongen' ? 'Jongen' : 'Meisje'} />
          <Chip tekst={kind.betreft === 'asielzoeker' ? 'Asielzoeker' : 'Andere nieuwkomer'} />
        </View>
      </View>

      <View style={styles.inhoud}>
        <View style={[styles.kaart, styles.tellers]}>
          <Teller getal={gehad} label="Gehad" kleur={kleuren.groen} achtergrond={kleuren.groenLicht} />
          <Teller getal={nodig.length} label="Nog nodig" kleur={kleuren.rood} achtergrond={kleuren.roodLicht} />
          <Teller getal={invullen.length} label="Invullen" kleur={kleuren.oranje} achtergrond={kleuren.oranjeLicht} />
        </View>

        <Text style={styles.uitleg}>
          Tik per vaccinatie op <Text style={styles.vet}>Ja</Text> als het kind deze heeft gehad en vul
          de datum in, of op <Text style={styles.vet}>Nee</Text> als deze niet gegeven is.
        </Text>

        {groepen.map((groep) => (
          <View key={groep.titel} style={styles.groep}>
            <View style={styles.groepKop}>
              <Text style={styles.groepTitel}>{groep.titel}</Text>
              {groep.ondertitel && <Text style={styles.groepOndertitel}>{groep.ondertitel}</Text>}
            </View>
            {groep.vaccins.map((v) => (
              <VaccinKaart
                key={v.id}
                vaccin={v}
                antwoord={antwoorden[v.id]}
                status={bepaalStatus(v, antwoorden[v.id], kind.geboortedatum)}
                geboortedatum={kind.geboortedatum}
                onWijzig={(antwoord) => onAntwoord(v.id, antwoord)}
              />
            ))}
          </View>
        ))}

        <View style={styles.kaart}>
          <Text style={styles.kaartTitel}>Overzicht</Text>
          {nodig.length > 0 && (
            <Blok titel={`Nog nodig (${nodig.length})`} regels={nodig} kleur={kleuren.rood} achtergrond={kleuren.roodLicht} />
          )}
          {invullen.length > 0 && (
            <Blok
              titel={`Al aan de beurt, nog niet ingevuld (${invullen.length})`}
              regels={invullen}
              kleur={kleuren.oranjeDonker}
              achtergrond={kleuren.oranjeLicht}
            />
          )}
          {nodig.length === 0 && invullen.length === 0 && (
            <Blok
              titel="Alles in orde"
              regels={['Alle vaccinaties die voor deze leeftijd gegeven moesten zijn, zijn gehad.']}
              kleur={kleuren.groen}
              achtergrond={kleuren.groenLicht}
            />
          )}
          <Text style={styles.uitleg}>
            Dit is een hulpmiddel. Controleer altijd de minimale intervallen en inhaalschema's in de{' '}
            <Text style={styles.link} onPress={() => Linking.openURL(RICHTLIJN_URL)}>
              RVP-richtlijn
            </Text>
            .
          </Text>
        </View>

        <PlanKaart plan={plan} />

        <View style={[styles.kaart, styles.exportKaart]}>
          <Text style={[styles.kaartTitel, styles.exportTitel]}>RIVM-formulier</Text>
          <Text style={styles.uitleg}>
            Vult het "Vaccinatiestatus en -opdrachtformulier nieuwkomers" in met de gegevens van het kind, de
            ontvangen vaccinaties en het vaccinatieplan. De rest (BSN, adres, opmerkingen, ondertekening) vult de
            jeugdarts zelf in.
          </Text>
          {!klaarVoorExport && (
            <View style={styles.waarschuwing}>
              <Text style={styles.waarschuwingTekst}>
                {invullen.length > 0
                  ? `Vul eerst alle vaccinaties in die al aan de beurt waren (${invullen.length} open).`
                  : `Controleer de datum bij: ${ongeldig.join(', ')}.`}
              </Text>
            </View>
          )}
          {plan.momenten.length > MAX_MOMENTEN && (
            <View style={styles.waarschuwing}>
              <Text style={styles.waarschuwingTekst}>
                Het plan heeft {plan.momenten.length} momenten; op het formulier passen er {MAX_MOMENTEN}. Vul de rest
                met de hand aan.
              </Text>
            </View>
          )}
          <Pressable
            onPress={exporteer}
            disabled={!klaarVoorExport || bezig}
            style={({ pressed }) => [styles.knop, (!klaarVoorExport || bezig) && styles.knopUit, pressed && styles.ingedrukt]}
          >
            <View style={styles.knopInhoud}>
              {bezig && <ActivityIndicator color="#FFFFFF" />}
              <Text style={styles.knopTekst}>{bezig ? 'Formulier maken…' : 'Exporteer naar PDF-formulier'}</Text>
            </View>
          </Pressable>
        </View>

        <Pressable onPress={onNieuw} style={({ pressed }) => [styles.knopSecundair, pressed && styles.ingedrukt]}>
          <Text style={styles.knopSecundairTekst}>Nieuw kind invoeren</Text>
        </Pressable>
      </View>
    </>
  );
}

function Chip({ tekst }) {
  return (
    <View style={styles.chip}>
      <Text style={styles.chipTekst}>{tekst}</Text>
    </View>
  );
}

function Teller({ getal, label, kleur, achtergrond }) {
  return (
    <View style={[styles.teller, { backgroundColor: achtergrond }]}>
      <Text style={[styles.tellerGetal, { color: kleur }]}>{getal}</Text>
      <Text style={[styles.tellerLabel, { color: kleur }]}>{label}</Text>
    </View>
  );
}

function Blok({ titel, regels, kleur, achtergrond }) {
  return (
    <View style={[styles.blok, { backgroundColor: achtergrond }]}>
      <Text style={[styles.blokTitel, { color: kleur }]}>{titel}</Text>
      {regels.map((regel) => (
        <Text key={regel} style={styles.blokRegel}>
          • {regel}
        </Text>
      ))}
    </View>
  );
}
