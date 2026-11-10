const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../android/app/src/main/assets/pdf-downloads.js'), 'utf8');

function environment(native = true) {
    const messages = [], downloads = [], alerts = [];
    let serial = 0;
    class Anchor { click() { downloads.push(this.href); } }
    class Reader {
        readAsDataURL(blob) {
            blob.arrayBuffer().then(bytes => {
                this.result = 'data:application/pdf;base64,' + Buffer.from(bytes).toString('base64');
                this.onload();
            });
        }
    }
    const window = {};
    window.top = window;
    const bridge = {postMessage(message) {
        messages.push(JSON.parse(message));
        queueMicrotask(() => bridge.onmessage({data: 'ok'}));
    }};
    if (native) window.PlackoPdf = bridge;
    const URL = {createObjectURL() { return 'blob:pdf-' + (++serial); }, revokeObjectURL() {}};
    const context = {window, PlackoPdf: bridge, HTMLAnchorElement: Anchor, URL, FileReader: Reader,
        setTimeout, clearTimeout, alert: text => alerts.push(text)};
    vm.runInNewContext(source, context);
    return {Anchor, URL, messages, downloads, alerts};
}

test('PDF stays available after Livewire immediately revokes the blob URL', async () => {
    const env = environment();
    const bytes = Buffer.concat([Buffer.from('%PDF-1.4\n'), Buffer.alloc(150000, 65)]);
    const blob = new Blob([bytes], {type: 'application/pdf'});
    const anchor = new env.Anchor();
    anchor.href = env.URL.createObjectURL(blob);
    anchor.download = 'Racun_1.pdf';
    anchor.click();
    env.URL.revokeObjectURL(anchor.href);
    for (let i = 0; i < 100 && !env.messages.some(m => m.type === 'end'); i++) {
        await new Promise(resolve => setImmediate(resolve));
    }
    assert.equal(env.messages[0].name, 'Racun_1.pdf');
    assert.equal(env.messages.at(-1).type, 'end');
    const received = Buffer.concat(env.messages.filter(m => m.type === 'chunk').map(m => Buffer.from(m.data, 'base64')));
    assert.deepEqual(received, bytes);
    assert.equal(env.downloads.length, 0);
    assert.deepEqual(env.alerts, []);
});

test('non-PDF exports keep their normal browser download', () => {
    const env = environment();
    const anchor = new env.Anchor();
    anchor.href = env.URL.createObjectURL(new Blob(['test'], {type: 'text/csv'}));
    anchor.download = 'export.csv';
    anchor.click();
    assert.deepEqual(env.downloads, [anchor.href]);
    assert.equal(env.messages.length, 0);
});

test('ordinary browsers without the native bridge are unchanged', () => {
    const env = environment(false);
    const anchor = new env.Anchor();
    anchor.href = 'blob:web-pdf';
    anchor.download = 'invoice.pdf';
    anchor.click();
    assert.deepEqual(env.downloads, [anchor.href]);
    assert.equal(env.messages.length, 0);
});
