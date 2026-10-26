import { Asset } from 'expo-asset';
import { File, Paths } from 'expo-file-system';
import * as Sharing from 'expo-sharing';
import { PDFDocument } from 'pdf-lib';
import { vulFormulier } from './formulier';

// Vult het RIVM-formulier in en opent het deelmenu (mail, WhatsApp, opslaan, ...)
export async function deelFormulier(kind, antwoorden, plan, opgesteldOp) {
  const [asset] = await Asset.loadAsync(require('../assets/formulier-nieuwkomers.pdf'));
  const sjabloon = await new File(asset.localUri).bytes();

  const pdf = await PDFDocument.load(sjabloon);
  vulFormulier(pdf, kind, antwoorden, plan, opgesteldOp);
  const bytes = await pdf.save();

  const naam = `Vaccinatieplan ${kind.voornaam} ${kind.achternaam}`.replace(/[\\/:*?"<>|]/g, '').trim();
  const bestand = new File(Paths.cache, `${naam}.pdf`);
  bestand.create({ overwrite: true });
  bestand.write(bytes);

  if (!(await Sharing.isAvailableAsync())) {
    throw new Error('Delen is niet beschikbaar op dit apparaat.');
  }
  await Sharing.shareAsync(bestand.uri, { mimeType: 'application/pdf', UTI: 'com.adobe.pdf', dialogTitle: naam });
}
