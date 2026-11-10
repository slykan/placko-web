package app.placko.mobile;

import java.io.*;
import java.nio.charset.StandardCharsets;
import java.util.Base64;

/** Bounded, sequential transfer to private cache. No caller-controlled file paths. */
final class PdfTransfer implements AutoCloseable {
    static final long LIMIT = 25L * 1024 * 1024;
    private File file;
    private OutputStream out;
    private long expected, written;

    void start(File directory, long size) throws IOException {
        close();
        if (size < 5 || size > LIMIT) throw new IOException("Neispravna veličina PDF-a.");
        if (!directory.isDirectory() && !directory.mkdirs()) throw new IOException("PDF nije moguće pripremiti.");
        file = File.createTempFile("document-", ".pdf", directory);
        out = new FileOutputStream(file);
        expected = size;
        written = 0;
    }

    void chunk(String encoded) throws IOException {
        if (out == null || encoded.length() > 65536) throw new IOException("Neispravan prijenos PDF-a.");
        byte[] bytes;
        try { bytes = Base64.getDecoder().decode(encoded); }
        catch (IllegalArgumentException e) { throw new IOException("Neispravan PDF.", e); }
        if (written + bytes.length > expected) throw new IOException("PDF je prevelik.");
        out.write(bytes);
        written += bytes.length;
    }

    File finish() throws IOException {
        if (out == null || written != expected) throw new IOException("PDF nije prenesen do kraja.");
        out.close();
        out = null;
        try (InputStream input = new FileInputStream(file)) {
            byte[] signature = new byte[5];
            if (input.read(signature) != 5 || !"%PDF-".equals(new String(signature, StandardCharsets.US_ASCII))) {
                throw new IOException("Datoteka nije PDF.");
            }
        }
        File result = file;
        file = null;
        return result;
    }

    static String safeName(String name) {
        String safe = name == null ? "Dokument" : name.replaceAll("[\\\\/\\p{Cntrl}]", "_");
        if (safe.length() > 120) safe = safe.substring(0, 120);
        return safe.toLowerCase(java.util.Locale.ROOT).endsWith(".pdf") ? safe : safe + ".pdf";
    }

    @Override public void close() throws IOException {
        try { if (out != null) out.close(); }
        finally { out = null; if (file != null) file.delete(); file = null; }
    }
}
