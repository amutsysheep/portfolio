import { StatusBar } from 'expo-status-bar';
import { useEffect, useState } from 'react';
import { BackHandler, KeyboardAvoidingView, ScrollView } from 'react-native';
import { SafeAreaProvider, SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import KindScherm from './src/screens/KindScherm';
import VaccinatieScherm from './src/screens/VaccinatieScherm';
import { styles } from './src/styles';

export default function App() {
  return (
    <SafeAreaProvider>
      <StatusBar style="light" />
      <Hoofd />
    </SafeAreaProvider>
  );
}

function Hoofd() {
  const insets = useSafeAreaInsets();
  const [scherm, setScherm] = useState('kind'); // 'kind' | 'vaccins'
  const [kind, setKind] = useState(null);
  const [antwoorden, setAntwoorden] = useState({}); // id -> { gehad, datum }

  // Android-terugknop: van de vaccinatielijst terug naar de gegevens
  useEffect(() => {
    const sub = BackHandler.addEventListener('hardwareBackPress', () => {
      if (scherm === 'vaccins') {
        setScherm('kind');
        return true;
      }
      return false;
    });
    return () => sub.remove();
  }, [scherm]);

  const nieuwKind = () => {
    setKind(null);
    setAntwoorden({});
    setScherm('kind');
  };

  return (
    <SafeAreaView edges={['top']} style={[styles.scherm, styles.bovenkant]}>
      <KeyboardAvoidingView behavior="padding" style={styles.scherm}>
        <ScrollView
          key={scherm}
          style={styles.scherm}
          contentContainerStyle={{ paddingBottom: insets.bottom + 32 }}
          keyboardShouldPersistTaps="handled"
        >
          {scherm === 'kind' ? (
            <KindScherm
              key={kind ? 'bestaand' : 'nieuw'}
              kind={kind}
              onVerder={(gegevens) => {
                setKind(gegevens);
                setScherm('vaccins');
              }}
            />
          ) : (
            <VaccinatieScherm
              kind={kind}
              antwoorden={antwoorden}
              onAntwoord={(id, antwoord) => setAntwoorden((a) => ({ ...a, [id]: antwoord }))}
              onWijzig={() => setScherm('kind')}
              onNieuw={nieuwKind}
            />
          )}
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}
