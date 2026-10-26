import { useState } from 'react';
import { TextInput } from 'react-native';
import { formatteerInvoer } from '../datum';
import { kleuren, styles } from '../styles';

// Datumveld waarin je alleen cijfers typt; de streepjes komen er vanzelf tussen.
// Sneller dan een kalender als je datums van een vaccinatieboekje overneemt.
export default function DatumInvoer({ waarde, onWijzig, fout, autoFocus }) {
  const [focus, setFocus] = useState(false);

  return (
    <TextInput
      value={waarde}
      onChangeText={(tekst) => onWijzig(formatteerInvoer(tekst))}
      placeholder="DD-MM-JJJJ"
      placeholderTextColor={kleuren.grijs}
      keyboardType="number-pad"
      maxLength={10}
      autoFocus={autoFocus}
      onFocus={() => setFocus(true)}
      onBlur={() => setFocus(false)}
      style={[styles.invoer, focus && styles.invoerFocus, fout && styles.invoerFout]}
    />
  );
}
