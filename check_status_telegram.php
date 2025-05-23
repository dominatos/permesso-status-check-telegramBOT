<?php
/**
 * ===============================
 * MONITORAGGIO PRATICHE QUESTURA
 * ===============================
 * 
 * Questo script controlla lo stato di più pratiche per il permesso di soggiorno
 * e invia notifiche su Telegram.
 * 
 * In caso di rifiuto, errore o stato incerto, il messaggio viene inviato comunque.
 */

$botToken = 'IL_TUO_TOKEN_DEL_BOT';
$chatIds = [
    'CHAT_ID_1',
    'CHAT_ID_2',
    'CHAT_ID_3'
];

$pratiche = [
    'XXXXXXXXXX',
    'XXXXXXXXXX',
    'XXXXXXXXXX'
];

// === FUNZIONE: Recupera lo stato di una pratica dal sito ===
function ottieniStatoPratica($url)
{
    $html = file_get_contents($url);
    if ($html === false) {
        return ['stato' => '❌ Errore nel caricamento della pagina', 'successo' => false];
    }

    if (strpos($html, 'documento di soggiorno è pronto per la consegna') !== false) {
        return ['stato' => '✅ Pronto per il ritiro', 'successo' => true];
    }

    if (preg_match('/Stato del suo permesso di soggiorno: (.*?)\./', $html, $matches)) {
        return ['stato' => trim($matches[1]), 'successo' => true];
    }

    $htmlLower = strtolower($html);
    if (
        strpos($htmlLower, 'archiviata') !== false ||
        strpos($htmlLower, 'respinta') !== false ||
        strpos($htmlLower, 'non esiste') !== false ||
        strpos($htmlLower, 'non è presente') !== false ||
        strpos($htmlLower, 'Numero di caratteri non validi') !== false ||
        strpos($htmlLower, 'errore') !== false
    ) {
        return ['stato' => '❗ Pratica non trovata o rifiutata', 'successo' => true];
    }

    return ['stato' => '⚠️ Stato non determinato', 'successo' => false];
}

// === PROCESSO PRINCIPALE ===
foreach ($pratiche as $codicePratica) {
    $url = "https://questure.poliziadistato.it/stranieri?lang=italian&mime=&pratica=$codicePratica";
    $filePrecedente = __DIR__ . "/stato_$codicePratica.txt";

    $risultato = ottieniStatoPratica($url);
    $statoCorrente = $risultato['stato'];

    $statoPrecedente = file_exists($filePrecedente) ? trim(file_get_contents($filePrecedente)) : '';

    $link = "[Apri la pagina]($url)";

    $forzaInvio = in_array($statoCorrente, [
        '❗ Pratica non trovata o rifiutata',
        '⚠️ Stato non determinato',
        '❌ Errore nel caricamento della pagina'
    ]);

    if ($statoCorrente !== $statoPrecedente || $forzaInvio) {
        $messaggio = "🔔 *Stato aggiornato!*\n\n*Pratica:* `$codicePratica`\n📄 Nuovo stato: *$statoCorrente*\n\n$link";
        file_put_contents($filePrecedente, $statoCorrente);
    } else {
        continue; // Non inviare nulla se lo stato è lo stesso e non è critico
    }

    foreach ($chatIds as $chatId) {
        $urlTelegram = "https://api.telegram.org/bot$botToken/sendMessage";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlTelegram);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'chat_id' => $chatId,
            'text' => $messaggio,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $risposta = curl_exec($ch);
        if ($risposta === false) {
            echo "❌ Errore invio pratica $codicePratica a $chatId: " . curl_error($ch) . "\n";
        } else {
            echo "✅ [$codicePratica] Messaggio inviato a $chatId.\n";
        }
        curl_close($ch);
    }
}
