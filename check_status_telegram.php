<?php
/**
 * ===================================
 * MONITORAGGIO PRATICHE QUESTURA
 * ===================================
 *
 * Questo script controlla lo stato di più pratiche per il permesso di soggiorno
 * e invia notifiche su Telegram.
 *
 * Con l'opzione `$notificaSempre`, è possibile scegliere se ricevere notifiche
 * solo in caso di cambiamento dello stato oppure sempre.
 */

$botToken = 'IL_TUO_TOKEN_DEL_BOT';
$chatIds = [
    'CHAT_ID_1',
    'CHAT_ID_2',
    'CHAT_ID_3'
];

// Se impostato su `true`, invierà notifiche sempre, anche se lo stato non cambia
$notificaSempre = true;

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

    // Проверка на готовность документа к выдаче
    if (strpos($html, 'documento di soggiorno è pronto per la consegna') !== false) {
        return ['stato' => '✅ Documento pronto per il ritiro', 'successo' => true];
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
        strpos($htmlLower, 'numero di caratteri non validi') !== false ||
        strpos($htmlLower, 'errore') !== false
    ) {
        return ['stato' => '❗ Pratica non trovata o rifiutata', 'successo' => true];
    }

    return ['stato' => '⚠️ Stato non determinato', 'successo' => false];
}

// === PROCESSO PRINCIPALE ===
foreach ($pratiche as $codicePratica) {
    $url = "https://questure.poliziadistato.it/stranieri?lang=italian&mime=4&pratica=$codicePratica";
    $url2 = "https://questure.poliziadistato.it/stranieri?lang=italian&mime=&pratica=$codicePratica";
    $filePrecedente = __DIR__ . "/stato_$codicePratica.txt";

    // Recupera lo stato della pratica dal sito
    $risultato = ottieniStatoPratica($url);
    $statoCorrente = $risultato['stato'];

    // Legge il precedente stato salvato su file (se esiste)
    $statoPrecedente = file_exists($filePrecedente) ? trim(file_get_contents($filePrecedente)) : '';

    $link = "[Apri la pagina]($url2)";

    // Se lo stato è critico, forza l'invio del messaggio
    $forzaInvio = in_array($statoCorrente, [
        '❗ Pratica non trovata o rifiutata',
        '⚠️ Stato non determinato',
        '❌ Errore nel caricamento della pagina'
    ]);

    // Controlla se è necessario inviare la notifica
    if ($notificaSempre || $statoCorrente !== $statoPrecedente || $forzaInvio) {
        $messaggio = "🔔 *Stato aggiornato!*\n\n*Pratica:* `$codicePratica`\n📄 Stato attuale: *$statoCorrente*\n\n$link";
        file_put_contents($filePrecedente, $statoCorrente);
    } else {
        continue; // Salta l'invio se lo stato non è cambiato e non è critico
    }

    // Invia la notifica Telegram a tutte le chat ID
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
