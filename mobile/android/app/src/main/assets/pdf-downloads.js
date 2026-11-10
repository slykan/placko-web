(() => {
    if (window !== window.top || window.__plackoPdf || !window.PlackoPdf) return;
    window.__plackoPdf = true;
    const blobs = new Map();
    const create = URL.createObjectURL.bind(URL);
    const revoke = URL.revokeObjectURL.bind(URL);
    URL.createObjectURL = blob => {
        const url = create(blob);
        blobs.set(url, blob);
        return url;
    };
    URL.revokeObjectURL = url => { blobs.delete(url); revoke(url); };
    let reply;
    PlackoPdf.onmessage = event => {
        if (reply) { const callback = reply; reply = null; callback(event.data); }
    };
    const send = data => new Promise((resolve, reject) => {
        const timeout = setTimeout(() => { reply = null; reject(new Error('Prijenos je istekao.')); }, 15000);
        reply = result => {
            clearTimeout(timeout);
            result === 'ok' ? resolve() : reject(new Error(result));
        };
        PlackoPdf.postMessage(JSON.stringify(data));
    });
    const base64 = blob => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result.split(',')[1]);
        reader.onerror = () => reject(new Error('PDF nije moguće pročitati.'));
        reader.readAsDataURL(blob);
    });
    let queue = Promise.resolve();
    const click = HTMLAnchorElement.prototype.click;
    HTMLAnchorElement.prototype.click = function () {
        const blob = blobs.get(this.href);
        if (!blob || !(blob.type === 'application/pdf' || /\.pdf$/i.test(this.download))) {
            return click.call(this);
        }
        const name = this.download || 'Dokument.pdf';
        // Keep a Blob reference: Livewire revokes its URL immediately after click().
        queue = queue.then(async () => {
            if (blob.size > 25 * 1024 * 1024) throw new Error('PDF je veći od 25 MB.');
            await send({type: 'start', name, size: blob.size});
            for (let offset = 0; offset < blob.size; offset += 49152) {
                await send({type: 'chunk', data: await base64(blob.slice(offset, offset + 49152))});
            }
            await send({type: 'end'});
        }).catch(error => {
            PlackoPdf.postMessage(JSON.stringify({type: 'error'}));
            alert('PDF nije otvoren. ' + error.message + ' Pokušajte ponovno.');
        });
    };
})();
