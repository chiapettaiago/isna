import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { setTimeout as sleep } from 'node:timers/promises';

const baseUrl = 'http://127.0.0.1:8108';
const outputDir = 'images/reports/version-2-1';
const debuggingPort = 9225;

mkdirSync(outputDir, { recursive: true });

const chrome = spawn('google-chrome', [
  '--headless=new',
  '--disable-gpu',
  '--no-sandbox',
  '--disable-crash-reporter',
  '--disable-dev-shm-usage',
  `--remote-debugging-port=${debuggingPort}`,
  '--window-size=1440,1000',
  'about:blank',
], { stdio: ['ignore', 'pipe', 'pipe'] });

async function waitForJsonVersion() {
  const endpoint = `http://127.0.0.1:${debuggingPort}/json/version`;
  for (let attempt = 0; attempt < 60; attempt += 1) {
    try {
      const response = await fetch(endpoint);
      if (response.ok) {
        return await response.json();
      }
    } catch {
      // Chrome is still starting.
    }
    await sleep(250);
  }

  throw new Error('Chrome DevTools endpoint did not become available.');
}

class CdpClient {
  constructor(url) {
    this.nextId = 1;
    this.pending = new Map();
    this.events = new Map();
    this.socket = new WebSocket(url);
  }

  async open() {
    await new Promise((resolve, reject) => {
      this.socket.addEventListener('open', resolve, { once: true });
      this.socket.addEventListener('error', reject, { once: true });
      this.socket.addEventListener('message', (event) => {
        const message = JSON.parse(event.data);
        if (message.id && this.pending.has(message.id)) {
          const { resolve: done, reject: fail } = this.pending.get(message.id);
          this.pending.delete(message.id);
          if (message.error) {
            fail(new Error(message.error.message || 'CDP command failed.'));
          } else {
            done(message.result || {});
          }
          return;
        }

        if (message.method && this.events.has(message.method)) {
          const listeners = this.events.get(message.method);
          this.events.delete(message.method);
          listeners.forEach((listener) => listener(message.params || {}));
        }
      });
    });
  }

  send(method, params = {}) {
    const id = this.nextId;
    this.nextId += 1;
    this.socket.send(JSON.stringify({ id, method, params }));

    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
    });
  }

  once(method) {
    return new Promise((resolve) => {
      const listeners = this.events.get(method) || [];
      listeners.push(resolve);
      this.events.set(method, listeners);
    });
  }

  close() {
    this.socket.close();
  }
}

async function capture(client, path, filename) {
  await client.send('Page.enable');
  await client.send('Network.enable');
  await client.send('Emulation.setDeviceMetricsOverride', {
    width: 1440,
    height: 1000,
    deviceScaleFactor: 1,
    mobile: false,
  });
  await client.send('Network.setCookie', {
    name: 'PHPSESSID',
    value: 'codexreport',
    domain: '127.0.0.1',
    path: '/',
    httpOnly: false,
    secure: false,
  });

  const loaded = client.once('Page.loadEventFired');
  await client.send('Page.navigate', { url: `${baseUrl}${path}` });
  await loaded;
  await sleep(1200);
  await client.send('Runtime.evaluate', { expression: 'window.scrollTo(0, 0)' });
  await sleep(250);

  const { data } = await client.send('Page.captureScreenshot', {
    format: 'png',
    fromSurface: true,
    captureBeyondViewport: false,
  });

  writeFileSync(`${outputDir}/${filename}`, Buffer.from(data, 'base64'));
}

try {
  const version = await waitForJsonVersion();
  const client = new CdpClient(version.webSocketDebuggerUrl);
  await client.open();

  await capture(client, '/login', 'login.png');
  await capture(client, '/titulos-documentos', 'documentos.png');
  await capture(client, '/area-restrita', 'area-restrita.png');
  await capture(client, '/sobre/versao-2-1', 'relatorio-versao.png');

  client.close();
} finally {
  chrome.kill('SIGTERM');
}
