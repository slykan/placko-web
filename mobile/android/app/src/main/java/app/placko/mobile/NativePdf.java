package app.placko.mobile;

import android.app.Activity;
import android.webkit.WebView;
import androidx.webkit.WebViewCompat;
import androidx.webkit.WebViewFeature;
import java.io.File;
import java.nio.charset.StandardCharsets;
import java.util.Collections;
import org.json.JSONObject;

final class NativePdf {
    private final Activity activity;
    private final PdfTransfer transfer = new PdfTransfer();
    private final android.os.Handler handler = new android.os.Handler(android.os.Looper.getMainLooper());
    private final Runnable timeout = this::reset;
    private String name;
    private String script;
    private boolean documentStart;

    NativePdf(Activity activity, WebView web) {
        this.activity = activity;
        File directory = new File(activity.getCacheDir(), "pdf-preview");
        File[] old = directory.listFiles();
        if (old != null) for (File file : old) {
            if (file.lastModified() < System.currentTimeMillis() - 86400000L) file.delete();
        }
        if (!WebViewFeature.isFeatureSupported(WebViewFeature.WEB_MESSAGE_LISTENER)) return;
        try (java.io.InputStream input = activity.getAssets().open("pdf-downloads.js")) {
            java.io.ByteArrayOutputStream bytes = new java.io.ByteArrayOutputStream();
            PdfActivity.copy(input, bytes);
            script = new String(bytes.toByteArray(), StandardCharsets.UTF_8);
        } catch (Exception e) { return; }
        WebViewCompat.addWebMessageListener(web, "PlackoPdf", Collections.singleton(NavigationPolicy.ORIGIN),
            (view, message, origin, mainFrame, reply) -> {
                if (!mainFrame || !NavigationPolicy.isInternal(origin.toString())
                        || !NavigationPolicy.isInternal(view.getUrl())) return;
                try {
                    String data = message.getData();
                    if (data == null || data.length() > 70000) throw new IllegalArgumentException();
                    JSONObject payload = new JSONObject(data);
                    switch (payload.getString("type")) {
                        case "start":
                            name = PdfTransfer.safeName(payload.optString("name", "Dokument.pdf"));
                            transfer.start(directory, payload.getLong("size"));
                            break;
                        case "chunk": transfer.chunk(payload.getString("data")); break;
                        case "end":
                            File pdf = transfer.finish();
                            PdfActivity.open(activity, pdf, name);
                            break;
                        case "error": reset(); return;
                        default: throw new IllegalArgumentException();
                    }
                    handler.removeCallbacks(timeout);
                    if (!"end".equals(payload.getString("type"))) handler.postDelayed(timeout, 30000);
                    reply.postMessage("ok");
                } catch (Exception e) {
                    reset();
                    reply.postMessage("Prijenos PDF-a nije uspio.");
                }
            });
        if (WebViewFeature.isFeatureSupported(WebViewFeature.DOCUMENT_START_SCRIPT)) {
            documentStart = true;
            WebViewCompat.addDocumentStartJavaScript(web, script, Collections.singleton(NavigationPolicy.ORIGIN));
        }
    }

    void pageFinished(WebView web) {
        if (script != null && !documentStart && NavigationPolicy.isInternal(web.getUrl())) web.evaluateJavascript(script, null);
    }

    void reset() {
        handler.removeCallbacks(timeout);
        try { transfer.close(); } catch (Exception ignored) { }
    }
}
