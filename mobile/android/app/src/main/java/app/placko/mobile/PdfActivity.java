package app.placko.mobile;

import android.app.Activity;
import android.content.ClipData;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.pdf.PdfRenderer;
import android.net.Uri;
import android.os.Bundle;
import android.os.CancellationSignal;
import android.os.ParcelFileDescriptor;
import android.print.*;
import android.view.*;
import android.widget.*;
import androidx.core.content.FileProvider;
import java.io.*;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

/** Private PDF preview. Saving and sharing happen only after a user action. */
public class PdfActivity extends Activity {
    private static final int SAVE = 71;
    private static final ExecutorService FILES = Executors.newSingleThreadExecutor();
    private File file;
    private String name;
    private PdfRenderer renderer;
    private int index;
    private TextView pageNumber;
    private Button previous, next;
    private PdfView preview;

    static void open(Activity activity, File file, String name) {
        activity.startActivity(new Intent(activity, PdfActivity.class)
            .putExtra("file", file.getName()).putExtra("name", PdfTransfer.safeName(name)));
    }

    @Override public void onCreate(Bundle state) {
        super.onCreate(state);
        try {
            String basename = getIntent().getStringExtra("file");
            File directory = new File(getCacheDir(), "pdf-preview");
            if (basename == null || !basename.equals(new File(basename).getName())) throw new IOException();
            file = new File(directory, basename);
            if (!file.getCanonicalFile().getParentFile().equals(directory.getCanonicalFile())) throw new IOException();
            name = PdfTransfer.safeName(getIntent().getStringExtra("name"));
            renderer = new PdfRenderer(ParcelFileDescriptor.open(file, ParcelFileDescriptor.MODE_READ_ONLY));
            if (renderer.getPageCount() < 1) throw new IOException();
            index = state == null ? 0 : Math.min(state.getInt("page"), renderer.getPageCount() - 1);
        } catch (Exception e) {
            toast("PDF nije moguće otvoriti. Pokušajte ponovno.");
            finish();
            return;
        }
        LinearLayout root = new LinearLayout(this);
        root.setOrientation(LinearLayout.VERTICAL);
        root.setBackgroundColor(Color.rgb(247, 250, 250));
        root.setOnApplyWindowInsetsListener((view, insets) -> {
            view.setPadding(insets.getSystemWindowInsetLeft(), insets.getSystemWindowInsetTop(),
                insets.getSystemWindowInsetRight(), insets.getSystemWindowInsetBottom());
            return insets;
        });
        LinearLayout heading = new LinearLayout(this);
        heading.setGravity(Gravity.CENTER_VERTICAL);
        Button back = button("Natrag", v -> finish());
        heading.addView(back);
        TextView title = new TextView(this);
        title.setText(name);
        title.setTextColor(Color.rgb(22, 90, 82));
        title.setTextSize(16);
        title.setMaxLines(2);
        heading.addView(title, new LinearLayout.LayoutParams(0, -2, 1));
        root.addView(heading);
        LinearLayout actions = new LinearLayout(this);
        actions.addView(button("Spremi", v -> save()), new LinearLayout.LayoutParams(0, -2, 1));
        actions.addView(button("Podijeli", v -> share()), new LinearLayout.LayoutParams(0, -2, 1));
        actions.addView(button("Ispiši", v -> print()), new LinearLayout.LayoutParams(0, -2, 1));
        root.addView(actions);
        preview = new PdfView();
        root.addView(preview, new LinearLayout.LayoutParams(-1, 0, 1));
        LinearLayout paging = new LinearLayout(this);
        paging.setGravity(Gravity.CENTER_VERTICAL);
        previous = button("Prethodna", v -> { index--; render(); });
        next = button("Sljedeća", v -> { index++; render(); });
        pageNumber = new TextView(this);
        pageNumber.setGravity(Gravity.CENTER);
        paging.addView(previous);
        paging.addView(pageNumber, new LinearLayout.LayoutParams(0, -2, 1));
        paging.addView(next);
        root.addView(paging);
        setContentView(root);
        preview.post(this::render);
    }

    private Button button(String label, View.OnClickListener listener) {
        Button button = new Button(this);
        button.setText(label);
        button.setAllCaps(false);
        button.setOnClickListener(listener);
        return button;
    }

    private void render() {
        if (renderer == null || isFinishing()) return;
        try (PdfRenderer.Page page = renderer.openPage(index)) {
            // One page at a time, capped to keep long reports within a fixed memory budget.
            float ratio = Math.min(2f, 2048f / Math.max(page.getWidth(), page.getHeight()));
            Bitmap bitmap = Bitmap.createBitmap(Math.max(1, (int) (page.getWidth() * ratio)),
                Math.max(1, (int) (page.getHeight() * ratio)), Bitmap.Config.ARGB_8888);
            bitmap.eraseColor(Color.WHITE);
            page.render(bitmap, null, null, PdfRenderer.Page.RENDER_MODE_FOR_DISPLAY);
            preview.show(bitmap);
            pageNumber.setText((index + 1) + " / " + renderer.getPageCount());
            previous.setEnabled(index > 0);
            next.setEnabled(index + 1 < renderer.getPageCount());
        } catch (Exception e) { toast("Ovu stranicu nije moguće prikazati."); }
    }

    private void save() {
        Intent intent = new Intent(Intent.ACTION_CREATE_DOCUMENT).setType("application/pdf")
            .addCategory(Intent.CATEGORY_OPENABLE).putExtra(Intent.EXTRA_TITLE, name);
        try { startActivityForResult(intent, SAVE); }
        catch (android.content.ActivityNotFoundException e) { toast("Nema aplikacije za spremanje datoteke."); }
    }

    @Override protected void onActivityResult(int request, int result, Intent data) {
        super.onActivityResult(request, result, data);
        if (request != SAVE || result != RESULT_OK || data == null || data.getData() == null) return;
        Uri destination = data.getData();
        FILES.execute(() -> {
            try (InputStream input = new FileInputStream(file);
                 OutputStream output = getContentResolver().openOutputStream(destination, "wt")) {
                if (output == null) throw new IOException();
                copy(input, output);
                runOnUiThread(() -> toast("PDF je spremljen."));
            } catch (Exception e) { runOnUiThread(() -> toast("Spremanje nije uspjelo. Pokušajte ponovno.")); }
        });
    }

    private void share() {
        Uri uri = FileProvider.getUriForFile(this, BuildConfig.APPLICATION_ID + ".pdf", file, name);
        Intent intent = new Intent(Intent.ACTION_SEND).setType("application/pdf")
            .putExtra(Intent.EXTRA_STREAM, uri)
            .addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION);
        intent.setClipData(ClipData.newRawUri(name, uri));
        try { startActivity(Intent.createChooser(intent, "Podijeli PDF")); }
        catch (android.content.ActivityNotFoundException e) { toast("Nema aplikacije za dijeljenje PDF-a."); }
    }

    private void print() {
        PrintManager manager = (PrintManager) getSystemService(PRINT_SERVICE);
        int pageCount = renderer.getPageCount();
        try {
            manager.print(name, new PrintDocumentAdapter() {
                @Override public void onLayout(PrintAttributes oldAttrs, PrintAttributes newAttrs,
                        CancellationSignal cancel, LayoutResultCallback callback, Bundle extras) {
                    if (cancel.isCanceled()) { callback.onLayoutCancelled(); return; }
                    callback.onLayoutFinished(new PrintDocumentInfo.Builder(name)
                        .setContentType(PrintDocumentInfo.CONTENT_TYPE_DOCUMENT).setPageCount(pageCount).build(), true);
                }
                @Override public void onWrite(PageRange[] pages, ParcelFileDescriptor destination,
                        CancellationSignal cancel, WriteResultCallback callback) {
                    FILES.execute(() -> {
                        try (InputStream input = new FileInputStream(file);
                             OutputStream output = new ParcelFileDescriptor.AutoCloseOutputStream(destination)) {
                            byte[] buffer = new byte[16384];
                            for (int count; (count = input.read(buffer)) != -1;) {
                                if (cancel.isCanceled()) { runOnUiThread(callback::onWriteCancelled); return; }
                                output.write(buffer, 0, count);
                            }
                            output.flush();
                            runOnUiThread(() -> callback.onWriteFinished(new PageRange[]{PageRange.ALL_PAGES}));
                        } catch (Exception e) { runOnUiThread(() -> callback.onWriteFailed("Ispis nije uspio.")); }
                    });
                }
            }, new PrintAttributes.Builder().build());
        } catch (RuntimeException e) { toast("Ispis nije uspio. Pokušajte ponovno."); }
    }

    static void copy(InputStream input, OutputStream output) throws IOException {
        byte[] buffer = new byte[16384];
        for (int n; (n = input.read(buffer)) != -1;) output.write(buffer, 0, n);
    }

    private void toast(String text) { if (!isDestroyed()) Toast.makeText(this, text, Toast.LENGTH_LONG).show(); }
    @Override protected void onSaveInstanceState(Bundle state) { state.putInt("page", index); super.onSaveInstanceState(state); }
    @Override protected void onDestroy() {
        if (preview != null) preview.show(null);
        if (renderer != null) renderer.close();
        super.onDestroy();
    }

    /** Pinch to zoom and drag; fit resets when changing pages. */
    private final class PdfView extends View {
        private Bitmap bitmap;
        private final android.graphics.RectF bounds = new android.graphics.RectF();
        private final Paint bitmapPaint = new Paint(Paint.FILTER_BITMAP_FLAG);
        private float zoom = 1, offsetX, offsetY, lastX, lastY;
        private final ScaleGestureDetector scale;
        PdfView() {
            super(PdfActivity.this);
            setContentDescription("Pregled PDF-a. Povećajte s dva prsta i povucite za pomicanje.");
            setLayerType(View.LAYER_TYPE_SOFTWARE, null);
            scale = new ScaleGestureDetector(PdfActivity.this, new ScaleGestureDetector.SimpleOnScaleGestureListener() {
                @Override public boolean onScale(ScaleGestureDetector detector) {
                    float old = zoom;
                    zoom = Math.max(1, Math.min(4, zoom * detector.getScaleFactor()));
                    offsetX = detector.getFocusX() - (detector.getFocusX() - offsetX) * zoom / old;
                    offsetY = detector.getFocusY() - (detector.getFocusY() - offsetY) * zoom / old;
                    invalidate();
                    return true;
                }
            });
        }
        void show(Bitmap value) {
            if (bitmap != null) bitmap.recycle();
            bitmap = value;
            zoom = 1; offsetX = offsetY = 0;
            invalidate();
        }
        @Override protected void onDraw(Canvas canvas) {
            canvas.drawColor(Color.rgb(223, 230, 229));
            if (bitmap == null) return;
            float fit = Math.min((float) getWidth() / bitmap.getWidth(), (float) getHeight() / bitmap.getHeight());
            float width = bitmap.getWidth() * fit, height = bitmap.getHeight() * fit;
            float left = (getWidth() - width) / 2, top = (getHeight() - height) / 2;
            offsetX = constrain(offsetX, left, width, getWidth());
            offsetY = constrain(offsetY, top, height, getHeight());
            canvas.save();
            canvas.translate(offsetX, offsetY);
            canvas.scale(zoom, zoom);
            bounds.set(left, top, left + width, top + height);
            canvas.drawBitmap(bitmap, null, bounds, bitmapPaint);
            canvas.restore();
        }
        private float constrain(float offset, float start, float length, float viewport) {
            if (length * zoom <= viewport) return (viewport - length * zoom) / 2 - start * zoom;
            return Math.max(viewport - (start + length) * zoom, Math.min(-start * zoom, offset));
        }
        @Override public boolean onTouchEvent(MotionEvent event) {
            scale.onTouchEvent(event);
            if (event.getActionMasked() == MotionEvent.ACTION_MOVE && !scale.isInProgress() && zoom > 1) {
                offsetX += event.getX() - lastX; offsetY += event.getY() - lastY; invalidate();
            }
            lastX = event.getX(); lastY = event.getY();
            if (event.getActionMasked() == MotionEvent.ACTION_UP) performClick();
            return true;
        }
        @Override public boolean performClick() { super.performClick(); return true; }
    }
}
