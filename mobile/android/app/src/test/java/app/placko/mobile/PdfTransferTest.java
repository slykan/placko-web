package app.placko.mobile;

import org.junit.*;
import org.junit.rules.TemporaryFolder;
import java.io.*;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.util.Base64;
import static org.junit.Assert.*;

public class PdfTransferTest {
    @Rule public TemporaryFolder temporary = new TemporaryFolder();
    private String encode(String text) { return Base64.getEncoder().encodeToString(text.getBytes(StandardCharsets.US_ASCII)); }

    @Test public void chunksPreserveBytesAndCompletedFileSurvivesReset() throws Exception {
        File directory = temporary.newFolder();
        PdfTransfer transfer = new PdfTransfer();
        transfer.start(directory, 13);
        transfer.chunk(encode("%PDF-1.4\n"));
        transfer.chunk(encode("test"));
        File file = transfer.finish();
        transfer.close();
        assertEquals("%PDF-1.4\ntest", new String(Files.readAllBytes(file.toPath()), StandardCharsets.US_ASCII));
    }

    @Test public void partialTransferIsDeletedAndCannotBeFinished() throws Exception {
        File directory = temporary.newFolder();
        try (PdfTransfer transfer = new PdfTransfer()) {
            transfer.start(directory, 20);
            transfer.chunk(encode("%PDF-"));
            assertThrows(IOException.class, transfer::finish);
        }
        assertEquals(0, directory.list().length);
    }

    @Test public void sizeLimitsAndNonPdfAreRejected() throws Exception {
        File directory = temporary.newFolder();
        try (PdfTransfer transfer = new PdfTransfer()) {
            assertThrows(IOException.class, () -> transfer.start(directory, PdfTransfer.LIMIT + 1));
            transfer.start(directory, 5);
            assertThrows(IOException.class, () -> transfer.chunk(encode("%PDF-too-large")));
            transfer.chunk(encode("<html"));
            assertThrows(IOException.class, transfer::finish);
        }
        assertEquals(0, directory.list().length);
    }

    @Test public void restartDeletesPreviousPartialFileAndRejectsMalformedBase64() throws Exception {
        File directory = temporary.newFolder();
        try (PdfTransfer transfer = new PdfTransfer()) {
            transfer.start(directory, 5);
            File first = directory.listFiles()[0];
            transfer.start(directory, 5);
            assertFalse(first.exists());
            assertThrows(IOException.class, () -> transfer.chunk("%%%"));
        }
        assertEquals(0, directory.list().length);
    }

    @Test public void displayNamesCannotContainPathsOrControlCharacters() {
        assertEquals("Racun_1_1.pdf", PdfTransfer.safeName("Racun/1\\1.pdf"));
        assertEquals("Dokument.pdf", PdfTransfer.safeName(null));
        assertFalse(PdfTransfer.safeName("a\nb").contains("\n"));
    }
}
