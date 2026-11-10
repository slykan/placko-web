package app.placko.mobile;

import android.app.Activity;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.Gravity;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

public class MainActivity extends Activity {
    private WebView web;
    private LinearLayout error;
    private ProgressBar progress;
    private String retryUrl = NavigationPolicy.HOME;
    private boolean failed;
    private boolean splashReady;
    private final Handler launchHandler = new Handler(Looper.getMainLooper());
    private final Runnable releaseSplash = () -> splashReady = true;
    // Logos and attachments use the system file picker.
    private static final int REQUEST_FILE = 45;
    private android.webkit.ValueCallback<Uri[]> fileCallback;

    @Override public void onCreate(Bundle state) {
        setTheme(R.style.AppTheme);
        super.onCreate(state);
        LinearLayout root = new LinearLayout(this);
        root.setOrientation(LinearLayout.VERTICAL);
        root.setBackgroundColor(Color.WHITE);
        if (android.os.Build.VERSION.SDK_INT >= 30) {
            root.setOnApplyWindowInsetsListener((view, insets) -> {
                android.graphics.Insets bars = insets.getInsets(
                    android.view.WindowInsets.Type.systemBars() | android.view.WindowInsets.Type.ime());
                view.setPadding(bars.left, bars.top, bars.right, bars.bottom);
                return insets;
            });
        } else {
            root.setOnApplyWindowInsetsListener((view, insets) -> {
                view.setPadding(insets.getSystemWindowInsetLeft(), insets.getSystemWindowInsetTop(),
                    insets.getSystemWindowInsetRight(), insets.getSystemWindowInsetBottom());
                return insets;
            });
        }
        progress = new ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal);
        progress.setIndeterminate(true);
        root.addView(progress, new LinearLayout.LayoutParams(-1, 5));

        error = new LinearLayout(this);
        error.setOrientation(LinearLayout.VERTICAL);
        error.setGravity(Gravity.CENTER);
        error.setPadding(32, 32, 32, 32);
        TextView message = new TextView(this);
        message.setText("Plačko trenutno nije dostupan.\nProvjerite internetsku vezu i pokušajte ponovno.\n\nUnos bez interneta još nije dostupan.");
        message.setGravity(Gravity.CENTER);
        error.addView(message);
        Button retry = new Button(this);
        retry.setText("Pokušaj ponovno");
        retry.setOnClickListener(v -> reload());
        error.addView(retry);
        error.setVisibility(View.GONE);
        root.addView(error, new LinearLayout.LayoutParams(-1, 0, 1));

        web = new WebView(this);
        root.addView(web, new LinearLayout.LayoutParams(-1, 0, 1));
        setContentView(root);
        // Keep the system launch screen until the first web screen is ready.
        // A bounded wait also releases it on slow connections and restored activities.
        root.getViewTreeObserver().addOnPreDrawListener(new android.view.ViewTreeObserver.OnPreDrawListener() {
            @Override public boolean onPreDraw() {
                if (!splashReady) return false;
                root.getViewTreeObserver().removeOnPreDrawListener(this);
                return true;
            }
        });
        launchHandler.postDelayed(releaseSplash, 8000);
        WebSettings settings = web.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setAllowFileAccess(false);
        settings.setAllowContentAccess(false);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
        settings.setGeolocationEnabled(false);
        settings.setUserAgentString(settings.getUserAgentString() + " PlackoNative/" + BuildConfig.VERSION_NAME + " Android");
        CookieManager.getInstance().setAcceptCookie(true);
        CookieManager.getInstance().setAcceptThirdPartyCookies(web, false);
        WebView.setWebContentsDebuggingEnabled(BuildConfig.DEBUG);
        web.setWebChromeClient(new android.webkit.WebChromeClient() {
            @Override public boolean onShowFileChooser(WebView view, android.webkit.ValueCallback<Uri[]> callback, FileChooserParams params) {
                if (!NavigationPolicy.isInternal(view.getUrl())) { callback.onReceiveValue(null); return true; }
                if (fileCallback != null) fileCallback.onReceiveValue(null);
                fileCallback = callback;
                try {
                    startActivityForResult(params.createIntent(), REQUEST_FILE);
                } catch (ActivityNotFoundException e) {
                    fileCallback = null;
                    callback.onReceiveValue(null);
                    toast("Nema aplikacije za odabir datoteke.");
                }
                return true;
            }

        });
        web.setWebViewClient(new WebViewClient() {
            @Override public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                Uri uri = request.getUrl();
                if (!request.isForMainFrame()) return false; // Allow Turnstile HTTPS frames; top-level navigation stays restricted.
                if (NavigationPolicy.isPrintRequest(uri.toString())) {
                    printPage();
                    return true;
                }
                if (NavigationPolicy.isInternal(uri.toString())) {
                    if ("/auth/google".equals(uri.getPath())) {
                        web.loadUrl(NavigationPolicy.ORIGIN + "/auth/mobile/google");
                        return true;
                    }
                    if ("/auth/mobile/google/launch".equals(uri.getPath())) {
                        try {
                            startActivity(new Intent(Intent.ACTION_VIEW, uri).addCategory(Intent.CATEGORY_BROWSABLE));
                        } catch (ActivityNotFoundException e) {
                            toast("Za Google prijavu potreban je preglednik.");
                        }
                        return true;
                    }
                    return false;
                }
                if (NavigationPolicy.isAllowedExternal(uri.toString())) {
                    try { startActivity(new Intent(Intent.ACTION_VIEW, uri).addCategory(Intent.CATEGORY_BROWSABLE)); }
                    catch (ActivityNotFoundException e) {
                        String scheme = uri.getScheme() == null ? "" : uri.getScheme();
                        toast("viber".equals(scheme) ? "Viber nije instaliran na ovom mobitelu."
                            : "whatsapp".equals(scheme) ? "WhatsApp nije instaliran na ovom mobitelu."
                            : "Nema aplikacije za otvaranje poveznice.");
                    }
                }
                return true;
            }
            @Override public void onPageStarted(WebView view, String url, android.graphics.Bitmap icon) {
                failed = false;
                if (NavigationPolicy.isInternal(url)) retryUrl = url;
                progress.setVisibility(View.VISIBLE);
                error.setVisibility(View.GONE);
                web.setVisibility(View.VISIBLE);
            }
            @Override public void onPageFinished(WebView view, String url) {
                finishSplash();
                progress.setVisibility(View.GONE);
                CookieManager.getInstance().flush();
                if (failed) showError();
            }
            @Override public void onPageCommitVisible(WebView view, String url) {
                finishSplash();
            }
            @Override public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError reason) {
                if (request.isForMainFrame()) showError();
            }
            @Override public void onReceivedHttpError(WebView view, WebResourceRequest request, WebResourceResponse response) {
                if (request.isForMainFrame() && response.getStatusCode() >= 500) showError();
            }
        });
        // izvještaji (PDF, CSV, XLSX): preuzme se uz prijavu, spremi u Preuzimanja/Placko i odmah otvori
        Preuzimanje preuzimanje = new Preuzimanje(this);
        web.setDownloadListener((url, userAgent, disposition, mime, length) ->
            preuzimanje.preuzmi(url, userAgent, disposition, mime));
        if (android.os.Build.VERSION.SDK_INT >= 33) {
            getOnBackInvokedDispatcher().registerOnBackInvokedCallback(0, this::goBack);
        }
        if (!handleGoogleReturn(getIntent())) {
            if (state == null || web.restoreState(state) == null) web.loadUrl(NavigationPolicy.HOME);
        }
    }

    private void toast(String message) { Toast.makeText(this, message, Toast.LENGTH_LONG).show(); }
    @Override protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleGoogleReturn(intent);
    }

    private boolean handleGoogleReturn(Intent intent) {
        Uri uri = intent.getData();
        if (uri == null || !"placko".equals(uri.getScheme()) || !"auth".equals(uri.getHost())
                || !"/google".equals(uri.getPath())) return false;
        intent.setData(null);
        String code = uri.getQueryParameter("code"), state = uri.getQueryParameter("state");
        if (code == null || state == null || !code.matches("[a-f0-9]{64}") || !state.matches("[a-f0-9]{64}")) {
            toast("Google prijava nije dovršena. Pokušajte ponovno.");
            web.loadUrl(NavigationPolicy.ORIGIN + "/admin/login");
            return true;
        }
        // The server binds the one-time code to the WebView session that started login.
        web.loadUrl(NavigationPolicy.ORIGIN + "/auth/mobile/google/return#code=" + code + "&state=" + state);
        return true;
    }
    @Override protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode != REQUEST_FILE || fileCallback == null) return;
        fileCallback.onReceiveValue(android.webkit.WebChromeClient.FileChooserParams.parseResult(resultCode, data));
        fileCallback = null;
    }
    /** Reserved print route; the website does not yet invoke it. */
    private void printPage() {
        android.print.PrintManager manager = (android.print.PrintManager) getSystemService(PRINT_SERVICE);
        String naslov = web.getTitle() == null || web.getTitle().isEmpty() ? "Placko" : web.getTitle();
        android.print.PrintAttributes atributi = new android.print.PrintAttributes.Builder()
            .setMediaSize(android.print.PrintAttributes.MediaSize.ISO_A4).build();
        try { manager.print(naslov, web.createPrintDocumentAdapter(naslov), atributi); }
        catch (RuntimeException e) { toast("Ispis nije uspio. Pokušajte ponovno."); }
    }
    private void showError() {
        finishSplash();
        failed = true;
        progress.setVisibility(View.GONE);
        web.setVisibility(View.GONE);
        error.setVisibility(View.VISIBLE);
    }
    private void finishSplash() {
        splashReady = true;
        launchHandler.removeCallbacks(releaseSplash);
    }
    private void reload() { web.loadUrl(retryUrl); }
    private void goBack() { if (web.canGoBack()) web.goBack(); else finish(); }
    // Legacy fallback for API 29–32. API 33+ registers OnBackInvokedCallback above.
    @android.annotation.SuppressLint("GestureBackNavigation")
    @Override public void onBackPressed() { goBack(); }
    @Override protected void onSaveInstanceState(Bundle state) { super.onSaveInstanceState(state); web.saveState(state); }
    @Override protected void onPause() { CookieManager.getInstance().flush(); super.onPause(); }
    @Override protected void onDestroy() {
        launchHandler.removeCallbacks(releaseSplash);
        if (fileCallback != null) { fileCallback.onReceiveValue(null); fileCallback = null; }
        web.destroy();
        super.onDestroy();
    }
}
