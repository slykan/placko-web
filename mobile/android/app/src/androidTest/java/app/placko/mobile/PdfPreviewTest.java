package app.placko.mobile;

import android.app.Activity;
import android.app.Instrumentation;
import android.graphics.pdf.PdfDocument;
import android.graphics.Paint;
import androidx.test.platform.app.InstrumentationRegistry;
import org.junit.Test;
import static org.junit.Assert.*;
import android.webkit.WebView;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import java.io.ByteArrayOutputStream;

/** Exercises the real WebView bridge, chunk protocol and Android PdfRenderer together. */
public class PdfPreviewTest {

    private WebView findWeb(View view) {
        if (view instanceof WebView) return (WebView) view;
        if (view instanceof ViewGroup) for (int i = 0; i < ((ViewGroup) view).getChildCount(); i++) {
            WebView result = findWeb(((ViewGroup) view).getChildAt(i));
            if (result != null) return result;
        }
        return null;
    }

    private View text(View view, String label) {
        if (view instanceof TextView && label.contentEquals(((TextView) view).getText())) return view;
        if (view instanceof ViewGroup) for (int i = 0; i < ((ViewGroup) view).getChildCount(); i++) {
            View match = text(((ViewGroup) view).getChildAt(i), label);
            if (match != null) return match;
        }
        return null;
    }

    @Test public void testLivewireBlobOpensPrivatePdfPreview() throws Exception {
        Instrumentation instrumentation = InstrumentationRegistry.getInstrumentation();
        MainActivity main = (MainActivity) instrumentation.startActivitySync(
            new android.content.Intent(instrumentation.getTargetContext(), MainActivity.class)
                .addFlags(android.content.Intent.FLAG_ACTIVITY_NEW_TASK));
        PdfDocument document = new PdfDocument();
        for (int i = 0; i < 2; i++) {
            PdfDocument.Page page = document.startPage(new PdfDocument.PageInfo.Builder(595, 842, i + 1).create());
            Paint paint = new Paint(); paint.setTextSize(24);
            page.getCanvas().drawText("Placko - PDF pregled " + (i + 1), 48, 72, paint);
            document.finishPage(page);
        }
        ByteArrayOutputStream bytes = new ByteArrayOutputStream();
        document.writeTo(bytes); document.close();
        String data = android.util.Base64.encodeToString(bytes.toByteArray(), android.util.Base64.NO_WRAP);
        Instrumentation.ActivityMonitor monitor = instrumentation.addMonitor(PdfActivity.class.getName(), null, false);
        final WebView[] web = new WebView[1];
        instrumentation.runOnMainSync(() -> {
            web[0] = findWeb(main.findViewById(android.R.id.content));
            web[0].stopLoading();
            web[0].loadDataWithBaseURL(NavigationPolicy.ORIGIN + "/test-pdf", "<html><body>PDF test</body></html>", "text/html", "UTF-8", NavigationPolicy.ORIGIN + "/test-pdf");
        });
        // Poll readiness without depending on a network request or a real account.
        java.util.concurrent.CountDownLatch ready = new java.util.concurrent.CountDownLatch(1);
        android.os.Handler handler = new android.os.Handler(android.os.Looper.getMainLooper());
        Runnable poll = new Runnable() {
            int attempts;
            @Override public void run() {
                web[0].evaluateJavascript("!!window.PlackoPdf && document.readyState === 'complete'", value -> {
                    if ("true".equals(value) || ++attempts > 50) ready.countDown();
                    else handler.postDelayed(this, 100);
                });
            }
        };
        handler.post(poll);
        assertTrue(ready.await(10, java.util.concurrent.TimeUnit.SECONDS));
        instrumentation.runOnMainSync(() -> {
            try (java.io.InputStream input = main.getAssets().open("pdf-downloads.js")) {
                ByteArrayOutputStream js = new ByteArrayOutputStream(); PdfActivity.copy(input, js);
                web[0].evaluateJavascript(new String(js.toByteArray(), java.nio.charset.StandardCharsets.UTF_8), null);
            } catch (Exception e) { throw new RuntimeException(e); }
            web[0].evaluateJavascript("(()=>{const b=atob('" + data + "');const a=Uint8Array.from(b,c=>c.charCodeAt(0));"
                + "const u=URL.createObjectURL(new Blob([a],{type:'application/pdf'}));"
                + "const link=document.createElement('a');link.href=u;link.download='Testni_racun.pdf';"
                + "link.click();URL.revokeObjectURL(u);})()", null);
        });
        Activity preview = instrumentation.waitForMonitorWithTimeout(monitor, 15000);
        assertNotNull("PDF preview should open without a browser or external PDF app", preview);
        instrumentation.waitForIdleSync();
        assertEquals("Testni_racun.pdf", preview.getIntent().getStringExtra("name"));
        assertTrue(new java.io.File(main.getCacheDir(), "pdf-preview/" + preview.getIntent().getStringExtra("file")).isFile());
        View root = preview.findViewById(android.R.id.content);
        assertNotNull(text(root, "Spremi"));
        assertNotNull(text(root, "Podijeli"));
        assertNotNull(text(root, "Ispiši"));
        assertNotNull(text(root, "1 / 2"));
        instrumentation.runOnMainSync(() -> text(root, "Sljedeća").performClick());
        assertNotNull(text(root, "2 / 2"));
        instrumentation.runOnMainSync(() -> text(root, "Prethodna").performClick());
        assertNotNull(text(root, "1 / 2"));
        instrumentation.waitForIdleSync();
        android.graphics.Bitmap screenshot = instrumentation.getUiAutomation().takeScreenshot();
        try (java.io.FileOutputStream output = new java.io.FileOutputStream(new java.io.File(main.getCacheDir(), "pdf-preview-test.png"))) {
            screenshot.compress(android.graphics.Bitmap.CompressFormat.PNG, 100, output);
        }
        screenshot.recycle();
        // The filter must carry the same category and MIME type as the real intent, or it never matches.
        android.content.IntentFilter saveFilter = new android.content.IntentFilter(android.content.Intent.ACTION_CREATE_DOCUMENT);
        saveFilter.addCategory(android.content.Intent.CATEGORY_OPENABLE);
        saveFilter.addDataType("application/pdf");
        Instrumentation.ActivityMonitor save = instrumentation.addMonitor(
            saveFilter, new Instrumentation.ActivityResult(Activity.RESULT_CANCELED, null), true);
        instrumentation.runOnMainSync(() -> text(root, "Spremi").performClick());
        assertEquals(1, save.getHits());
        instrumentation.removeMonitor(save);
        Instrumentation.ActivityMonitor share = instrumentation.addMonitor(
            new android.content.IntentFilter(android.content.Intent.ACTION_CHOOSER),
            new Instrumentation.ActivityResult(Activity.RESULT_CANCELED, null), true);
        instrumentation.runOnMainSync(() -> text(root, "Podijeli").performClick());
        assertEquals(1, share.getHits());
        instrumentation.removeMonitor(share);
        instrumentation.removeMonitor(monitor);
        // Keep the preview visible for optional screenshot inspection.
    }
}
