const { 
    makeWASocket, 
    useMultiFileAuthState, 
    DisconnectReason, 
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    Browsers,
    downloadMediaMessage
} = require("@whiskeysockets/baileys");
const pino    = require("pino");
const path    = require("path");
const fs      = require("fs");
const express = require('express');
const axios   = require('axios');
const https   = require('https');
const Tesseract = require('tesseract.js');

// Config SSL untuk support self-signed certificate (HTTP/HTTPS)
process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

const axiosInstance = axios.create({
    httpsAgent: new https.Agent({  
        rejectUnauthorized: false
    })
});

// ─── Global Error Handlers ─────────────────────────────────────────────────────
process.on('unhandledRejection', (reason) => console.error('[DEBUG] Unhandled Rejection:', reason));
process.on('uncaughtException',  (err)    => console.error('[DEBUG] Uncaught Exception:', err));

require('dotenv').config({ path: path.resolve(__dirname, '.env') });

const app  = express();
app.use(express.json({ limit: '50mb' }));

const PORT         = process.env.BOT_PORT   || 3000;
const BOT_SECRET   = process.env.BOT_SECRET || 'rozitech-bot-secret-2024';
const LARAVEL_URL  = process.env.APP_URL    || 'http://127.0.0.1:8000';
const SESSIONS_PATH = path.resolve(__dirname, 'sessions');

// ─── Session Resmi (hanya session ini yang boleh terima & kirim pesan pelanggan) ─
const MAIN_SESSION_ID = process.env.MAIN_SESSION_ID || 'main';

if (!fs.existsSync(SESSIONS_PATH)) fs.mkdirSync(SESSIONS_PATH, { recursive: true });

const sessions      = new Map(); // id -> sock
const sessionStates = new Map(); // id -> { id, status, user, qr, ... }
const processedMessages = new Set(); // Cache for message IDs to prevent loops

// ─── Middleware ────────────────────────────────────────────────────────────────
const requireSecret = (req, res, next) => {
    const secret = req.headers['x-bot-secret'] || req.body?.secret || req.query?.secret;
    if (secret !== BOT_SECRET) return res.status(403).json({ error: 'Unauthorized' });
    next();
};

// ─── Helpers ───────────────────────────────────────────────────────────────────
function deleteSessionDir(sessionDir) {
    try {
        if (fs.existsSync(sessionDir)) {
            fs.rmSync(sessionDir, { recursive: true, force: true });
            console.log(`[LOG] Folder sesi dihapus: ${path.basename(sessionDir)}`);
        }
    } catch (e) {
        console.error('[WARN] Gagal hapus sesi:', e.message);
    }
}

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

function closeSock(cleanId) {
    if (sessions.has(cleanId)) {
        const old = sessions.get(cleanId);
        try { old.ev.removeAllListeners(); old.end?.(); } catch(_) {}
        sessions.delete(cleanId);
    }
}

// ─── Core: Start Session ───────────────────────────────────────────────────────
async function startSession(id, opts = {}) {
    const cleanId    = id.replace(/[^a-zA-Z0-9_]/g, '');
    const sessionDir = path.join(SESSIONS_PATH, cleanId);

    if (sessions.has(cleanId)) {
        console.log(`[LOG] Sesi ${cleanId} sudah aktif, digunakan kembali.`);
        return sessions.get(cleanId);
    }

    console.log(`[LOG] Memulai Sesi: ${cleanId}`);

    let { version, isLatest } = await fetchLatestBaileysVersion().catch(() => ({ version: [2, 3000, 1015901307], isLatest: false }));
    console.log(`[LOG] WA Web v${version.join('.')} (Latest: ${isLatest})`);

    const { state, saveCreds } = await useMultiFileAuthState(sessionDir);

    const sock = makeWASocket({
        version,
        logger: pino({ level: 'silent' }),
        printQRInTerminal: opts.printQR ?? false,
        auth: {
            creds: state.creds,
            keys: makeCacheableSignalKeyStore(state.keys, pino({ level: 'silent' }))
        },
        browser: Browsers.ubuntu('Chrome'), 
        connectTimeoutMs:     60000,
        defaultQueryTimeoutMs: 60000,
        keepAliveIntervalMs:  30000,
        retryRequestDelayMs:  250,
        maxMsgRetryCount:     5,
        shouldSyncHistoryMessage: () => false,
        syncFullHistory:  false,
        markOnlineOnConnect: true, 
        generateHighQualityLinkPreview: false,
    });

    sessions.set(cleanId, sock);
    sessionStates.set(cleanId, { id: cleanId, status: 'connecting' });

    sock.ev.on('creds.update', saveCreds);

    // Helper: Lapor status ke Laravel
    const notifyLaravel = async (status) => {
        try {
            await axiosInstance.post(`${LARAVEL_URL}/whatsapp/status`, {
                sessionId: cleanId,
                status: status,
                user: sock.user || null
            }, { 
                headers: { 'X-Bot-Secret': BOT_SECRET },
                timeout: 5000 
            });
        } catch (e) {
            console.error(`[STATUS] Gagal lapor status ke Laravel (${cleanId}): ${e.message}`);
        }
    };

    // ── Connection Update ──
    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            sessionStates.set(cleanId, { ...sessionStates.get(cleanId), qr, status: 'qr' });
            console.log(`[QR] Sesi ${cleanId} menampilkan QR...`);
            notifyLaravel('qr');
        }

        if (connection === 'close') {
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const errMsg     = lastDisconnect?.error?.message || '';
            const fullError  = lastDisconnect?.error;
            console.error(`[ERR] Sesi ${cleanId} terputus — Code: ${statusCode}, Msg: ${errMsg}`);
            
            if (fullError && statusCode !== 408) {
                console.log(`[DEBUG] Full Error:`, JSON.stringify(fullError, null, 2));
            }
            
            sessions.delete(cleanId);
            notifyLaravel('closed');

            const fatal = 
                statusCode === DisconnectReason.loggedOut ||
                statusCode === 401 ||
                statusCode === 500 ||
                errMsg.includes('Invalid account signature') ||
                errMsg.includes('conflict');

            if (fatal) {
                console.log(`[LOG] Sesi ${cleanId} dihapus (fatal error)`);
                sessionStates.delete(cleanId);
                deleteSessionDir(sessionDir);
            } else {
                console.log(`[LOG] Reconnect sesi ${cleanId} dalam 5 detik...`);
                sessionStates.set(cleanId, { ...sessionStates.get(cleanId), status: 'reconnecting' });
                setTimeout(() => startSession(cleanId), 5000);
            }
        } else if (connection === 'open') {
            console.log(`[OK] ✅ Sesi ${cleanId} TERHUBUNG! User: ${sock.user?.id}`);
            sessionStates.set(cleanId, { 
                id: cleanId, status: 'open',
                user: sock.user,
                connectedAt: new Date().toISOString()
            });
            notifyLaravel('open');
        }
    });

    // ── Incoming Messages → Forward to Laravel ──
    sock.ev.on('messages.upsert', async ({ messages: msgs, type }) => {
        if (type !== 'notify') return;

        // 🔒 SECURITY: Hanya proses pesan dari session resmi
        if (cleanId !== MAIN_SESSION_ID) {
            console.log(`[SECURITY] Pesan diabaikan dari sesi tidak resmi: ${cleanId} (sesi resmi: ${MAIN_SESSION_ID})`);
            return;
        }

        for (const msg of msgs) {
            const msgId = msg.key.id;
            const fromMe = msg.key.fromMe;
            
            // IGNORE if: no message body, from self, or already processed
            if (!msg.message || fromMe || processedMessages.has(msgId)) {
                continue;
            }

            // IGNORE protocol messages (often the cause of 'message cannot be loaded' loops)
            const msgType = Object.keys(msg.message || {})[0];
            if (['protocolMessage', 'senderKeyDistributionMessage', 'reactionMessage', 'readReceiptMessage'].includes(msgType)) {
                continue;
            }

            // Mark as processed
            processedMessages.add(msgId);
            setTimeout(() => processedMessages.delete(msgId), 30000); // Cleanup after 30s

            const remoteJid = msg.key.remoteJid;
            const sender    = msg.key.participant || remoteJid;
            const pushName  = msg.pushName || '';

            console.log(`[MSG] Dari: ${pushName} (${sender}) | Tipe: ${msgType}`);

            let textContent = '';
            let mediaBase64 = null;
            let ocrText     = '';

            console.log(`[MSG] Dari: ${pushName} (${sender}) | Tipe: ${msgType}`);

            if (msgType === 'conversation') {
                textContent = msg.message.conversation;
            } else if (msgType === 'extendedTextMessage') {
                textContent = msg.message.extendedTextMessage?.text || '';
            } else if (msgType === 'buttonsResponseMessage') {
                textContent = msg.message.buttonsResponseMessage?.selectedButtonId || '';
            } else if (msgType === 'templateButtonReplyMessage') {
                textContent = msg.message.templateButtonReplyMessage?.selectedId || '';
            } else if (msgType === 'listResponseMessage') {
                textContent = msg.message.listResponseMessage?.singleSelectReply?.selectedRowId || '';
            } else if (msgType === 'imageMessage') {
                textContent = msg.message.imageMessage?.caption || '';
                try {
                    const buffer = await downloadMediaMessage(msg, 'buffer', {});
                    mediaBase64 = buffer.toString('base64');
                    
                    console.log(`[OCR] Memproses gambar dari ${pushName}...`);
                    const { data: { text } } = await Tesseract.recognize(buffer, 'ind+eng');
                    ocrText = text;
                    console.log(`[OCR] Selesai. Terdeteksi ${ocrText.length} karakter.`);
                } catch(e) {
                    console.error('[MEDIA/OCR] Gagal proses:', e.message);
                }
            }

            const payload = {
                sessionId: cleanId,
                remoteJid,
                sender,
                pushName,
                message: textContent,
                type: msgType,
                isGroup: remoteJid.endsWith('@g.us'),
                mentionedJid: msg.message?.extendedTextMessage?.contextInfo?.mentionedJid || [],
                timestamp: msg.messageTimestamp,
                media: mediaBase64,
                ocrText,
            };

            try {
                const resp = await axiosInstance.post(`${LARAVEL_URL}/whatsapp/webhook`, payload, {
                    headers: { 'X-Bot-Secret': BOT_SECRET },
                    timeout: 30000
                });

                const data = resp.data;
                console.log(`[WEBHOOK] Respon dari Laravel:`, JSON.stringify(data));
                if (!data) return;

                // Simulasi Jeda & Mengetik agar lebih "Manusiawi" (Anti-Ban)
                await sock.sendPresenceUpdate('composing', remoteJid);
                const delay = Math.floor(Math.random() * 1500) + 1500; // 1.5 - 3 detik
                await sleep(delay);
                await sock.sendPresenceUpdate('paused', remoteJid);

                // Case 1: Simple reply wrapped in { reply: "..." }
                if (data.reply && typeof data.reply === 'string') {
                    await sock.sendMessage(remoteJid, { text: data.reply }, { quoted: msg });
                } 
                // Case 2: Direct message object (e.g., from showMenu)
                else if (data.text || data.buttons || data.sections || data.image || data.document) {
                    await sock.sendMessage(remoteJid, data, { quoted: msg });
                }
            } catch (e) {
                console.error(`[WEBHOOK] Gagal kirim ke Laravel: ${e.message}`);
                if (e.response) console.error(`[WEBHOOK] Response Data:`, e.response.data);
            }
        }
    });

    return sock;
}

/** Auto-load sessions from folder */
async function loadExistingSessions() {
    if (!fs.existsSync(SESSIONS_PATH)) return;
    const folders = fs.readdirSync(SESSIONS_PATH);
    for (const folder of folders) {
        const fullPath = path.join(SESSIONS_PATH, folder);
        if (fs.statSync(fullPath).isDirectory()) {
            console.log(`[INIT] Memuat sesi tersimpan: ${folder}...`);
            await startSession(folder).catch(e => console.error(`[INIT] Gagal memuat ${folder}:`, e.message));
        }
    }
}

// ─── REST API ─────────────────────────────────────────────────────────────────

/** GET /sessions — daftar semua sesi */
app.get('/sessions', (req, res) => {
    res.json(Array.from(sessionStates.values()));
});

/** GET /session/:id — status satu sesi */
app.get('/session/:id', (req, res) => {
    const cleanId = req.params.id.replace(/[^a-zA-Z0-9_]/g, '');
    const state   = sessionStates.get(cleanId);
    if (!state) return res.status(404).json({ error: 'Session not found' });
    res.json(state);
});

/**
 * POST /session/start — mulai sesi (QR mode)
 * Body: { id }
 */
app.post('/session/start', requireSecret, async (req, res) => {
    const cleanId    = (req.body.id || 'main').replace(/[^a-zA-Z0-9_]/g, '');
    const sessionDir = path.join(SESSIONS_PATH, cleanId);

    try {
        closeSock(cleanId);
        await new Promise(r => setTimeout(r, 500));
        await startSession(cleanId, { printQR: true });
        res.json({ success: true, sessionId: cleanId, status: 'connecting' });
    } catch (e) {
        res.status(500).json({ error: e.message });
    }
});

/**
 * POST /session/pairing — pairing code mode
 * Body: { id, phone }
 */
app.post('/session/pairing', requireSecret, async (req, res) => {
    const { id, phone } = req.body;
    if (!id || !phone) return res.status(400).json({ error: 'id dan phone wajib diisi' });

    const cleanId    = id.replace(/[^a-zA-Z0-9_]/g, '');
    const sessionDir = path.join(SESSIONS_PATH, cleanId);

    try {
        console.log(`[PAIR] Request Pairing untuk ${phone} (sesi: ${cleanId})`);

        // Matikan & bersihkan sesi lama
        closeSock(cleanId);
        deleteSessionDir(sessionDir);
        await new Promise(r => setTimeout(r, 1500));

        const sock       = await startSession(cleanId, { printQR: true });
        const cleanPhone = phone.replace(/[^0-9]/g, '');

        console.log(`[PAIR] Menunggu socket siap (8 detik)...`);
        await new Promise(r => setTimeout(r, 8000));

        if (sock.authState.creds.registered) {
            console.log(`[PAIR] Sesi ${cleanId} sudah terdaftar, membatalkan request pairing.`);
            return res.json({ success: true, message: 'Sudah terhubung', sessionId: cleanId });
        }

        console.log(`[PAIR] Meminta kode pairing untuk ${cleanPhone}...`);
        const code          = await sock.requestPairingCode(cleanPhone);
        const formattedCode = code?.match(/.{1,4}/g)?.join('-') || code;
        console.log(`[PAIR] ✅ Kode pairing: ${formattedCode}`);

        return res.json({ 
            success: true,
            pairingCode: formattedCode,
            sessionId: cleanId,
            phone: cleanPhone
        });

    } catch (e) {
        console.error(`[PAIR] Error:`, e.message);
        closeSock(cleanId);
        sessionStates.delete(cleanId);
        deleteSessionDir(sessionDir);
        return res.status(500).json({ error: e.message });
    }
});

/**
 * POST /session/stop — logout & hapus sesi
 * Body: { id }
 */
app.post('/session/stop', requireSecret, async (req, res) => {
    const cleanId    = (req.body.id || '').replace(/[^a-zA-Z0-9_]/g, '');
    const sessionDir = path.join(SESSIONS_PATH, cleanId);
    closeSock(cleanId);
    sessionStates.delete(cleanId);
    deleteSessionDir(sessionDir);
    res.json({ success: true, message: `Sesi ${cleanId} dihapus` });
});

/**
 * DELETE /session/:id — alias untuk stop
 */
app.delete('/session/:id', requireSecret, async (req, res) => {
    const cleanId    = req.params.id.replace(/[^a-zA-Z0-9_]/g, '');
    const sessionDir = path.join(SESSIONS_PATH, cleanId);
    closeSock(cleanId);
    sessionStates.delete(cleanId);
    deleteSessionDir(sessionDir);
    res.json({ success: true, message: `Sesi ${cleanId} dihapus` });
});

/**
 * POST /send-message — kirim pesan teks / file
 * Body: { phone, message, sessionId, media, filename, mimetype, url }
 */
app.post('/send-message', requireSecret, async (req, res) => {
    const { phone, message, sessionId, media, filename, mimetype, url, caption } = req.body;

    if (!phone) return res.status(400).json({ error: 'phone wajib diisi' });

    // 🔒 SECURITY: Selalu kirim via session resmi, abaikan sessionId dari luar
    let targetId = MAIN_SESSION_ID;
    if (!sessions.has(targetId)) {
        // Fallback jika session resmi belum aktif
        return res.status(503).json({ error: `Sesi resmi '${MAIN_SESSION_ID}' belum aktif. Hubungkan WA terlebih dahulu.` });
    }

    const sock  = sessions.get(targetId);
    const state = sessionStates.get(targetId);

    if (!sock)                      return res.status(404).json({ error: `Sesi '${targetId}' tidak ditemukan` });
    if (state?.status !== 'open')   return res.status(400).json({ error: `Sesi '${targetId}' belum terhubung (status: ${state?.status})` });

    const cleanPhone = phone.replace(/[^0-9]/g, '');
    const jid        = `${cleanPhone}@s.whatsapp.net`;

    try {
        // ── Kirim File via URL ──
        if (url && filename) {
            const fileResp = await axiosInstance.get(url, { responseType: 'arraybuffer', timeout: 30000 });
            const buffer   = Buffer.from(fileResp.data);
            const mime     = mimetype || fileResp.headers['content-type'] || 'application/octet-stream';
            await sock.sendMessage(jid, {
                document: buffer,
                mimetype: mime,
                fileName: filename,
                caption: caption || message || ''
            });
            return res.json({ success: true, to: cleanPhone });
        }

        // ── Kirim File via Base64 ──
        if (media && filename) {
            const buffer = Buffer.from(media, 'base64');
            const mime   = mimetype || 'application/octet-stream';
            await sock.sendMessage(jid, {
                document: buffer,
                mimetype: mime,
                fileName: filename,
                caption: caption || message || ''
            });
            return res.json({ success: true, to: cleanPhone });
        }

        // ── Kirim Teks ──
        if (message) {
            await sock.sendMessage(jid, { text: message });
            return res.json({ success: true, to: cleanPhone });
        }

        return res.status(400).json({ error: 'Butuh message, media+filename, atau url+filename' });
    } catch (e) {
        console.error('[SEND] Error:', e.message);
        res.status(500).json({ error: e.message });
    }
});

// ─── Start Server ─────────────────────────────────────────────────────────────
app.listen(PORT, async () => {
    console.log(`✅ WhatsApp Bot Aktif di Port ${PORT}`);
    console.log(`📋 Endpoint:`);
    console.log(`   GET    /sessions`);
    console.log(`   GET    /session/:id`);
    console.log(`   POST   /session/start    { id }`);
    console.log(`   POST   /session/pairing  { id, phone }`);
    console.log(`   POST   /session/stop     { id }`);
    console.log(`   DELETE /session/:id`);
    console.log(`   POST   /send-message     { phone, message|media|url, sessionId }`);
    console.log(`   (Semua butuh X-Bot-Secret header atau ?secret=)`);

    console.log(`\n[INIT] Mencari sesi tersimpan...`);
    await loadExistingSessions();
});
