# 🛂 Permesso di Soggiorno Tracker

This PHP script monitors the status of multiple "permesso di soggiorno" applications from the Italian **Polizia di Stato** website and sends updates to one or more Telegram chats.

---

## 🇬🇧 How to Use

### 1. Create a Telegram Bot

- Open Telegram and search for [@BotFather](https://t.me/BotFather)
- Send `/newbot`
- Choose a name and username
- After creation, you'll receive a **token** like:  
  `123456789:ABCdefGhIjkLmNoPQRsTuVwxyZ`

Copy and paste this token into your script:
```php
$botToken = 'YOUR_BOT_TOKEN';
```

---

### 2. Get your Chat ID

- Start a conversation with [@RawDataBot](https://t.me/RawDataBot)
- Send any message
- The bot will reply with a JSON. Look for the line:
  ```json
  "chat": { "id": 123456789, ... }
  ```
- Use that number as your `chat_id` in the script:
```php
$chatIds = ['123456789'];
```

You can add multiple chat IDs:
```php
$chatIds = ['123456789', '987654321'];
```

---

### 3. Add your practice numbers

Replace this line with your actual practice codes:
```php
$pratiche = ['25FE001485', '34AB001236'];
```

---

### 4. Set up a Cron Job (Linux)

Run the script automatically (e.g., every hour):

```bash
crontab -e
```

Add this line (adjust path to your PHP and script):

```bash
0 * * * * /usr/bin/php /path/to/your/script.php
```

This example runs it every hour at minute 0.

---

## 🇮🇹 Come Utilizzarlo

### 1. Crea un bot Telegram

- Apri Telegram e cerca [@BotFather](https://t.me/BotFather)
- Invia il comando `/newbot`
- Dai un nome e uno username al bot
- Alla fine riceverai un **token** simile a:
  `123456789:ABCdefGhIjkLmNoPQRsTuVwxyZ`

Inseriscilo nel file:
```php
$botToken = 'IL_TUO_TOKEN';
```

---

### 2. Trova il tuo Chat ID

- Scrivi qualcosa a [@RawDataBot](https://t.me/RawDataBot)
- Il bot ti risponderà con un JSON
- Cerca il valore:
  `"chat": { "id": 123456789, ... }`
- Usa quel numero così:
```php
$chatIds = ['123456789'];
```

Puoi aggiungere più chat:
```php
$chatIds = ['123456789', '987654321'];
```

---

### 3. Inserisci i tuoi codici pratica

Sostituisci con i tuoi codici reali:
```php
$pratiche = ['25FE001485', '34AB001236'];
```

---

### 4. Imposta un cron job (Linux)

Per eseguire automaticamente lo script:

```bash
crontab -e
```

E aggiungi:
```bash
0 * * * * /usr/bin/php /percorso/del/tuo/script.php
```

Questo lo eseguirà ogni ora.

---

## ✅ Funzionalità

- Controllo automatico dello stato della pratica
- Invio su Telegram solo in caso di cambiamento (OPZIONALE)
- Rilevamento errori o rifiuti automatico
- Supporta più pratiche e più chat

---

## 📄 Licenza

MIT
