import { Pressable, Text, View } from 'react-native';
import { kleuren, styles } from '../styles';

// Rij knoppen waarvan er één tegelijk gekozen kan zijn (bijv. Ja/Nee).
// opties: [{ waarde, label, kleur? }]
export default function Keuze({ opties, waarde, onKies }) {
  return (
    <View style={styles.keuze}>
      {opties.map((optie) => {
        const actief = optie.waarde === waarde;
        return (
          <Pressable
            key={String(optie.waarde)}
            onPress={() => onKies(optie.waarde)}
            accessibilityRole="button"
            accessibilityState={{ selected: actief }}
            style={({ pressed }) => [
              styles.keuzeOptie,
              pressed && !actief && styles.keuzeOptieIngedrukt,
              actief && { backgroundColor: optie.kleur || kleuren.primair },
            ]}
          >
            <Text style={[styles.keuzeTekst, actief && styles.keuzeTekstActief]}>{optie.label}</Text>
          </Pressable>
        );
      })}
    </View>
  );
}
