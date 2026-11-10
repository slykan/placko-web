package app.placko.mobile;

import org.junit.Test;
import static org.junit.Assert.*;

public class NavigationPolicyTest {
    @Test public void authenticatedNavigationUsesOnlyPlackoHttpsOrigin() {
        assertTrue(NavigationPolicy.isInternal("https://placko.app/admin"));
        assertTrue(NavigationPolicy.isInternal("https://placko.app:443/admin/login"));
        for (String value : new String[]{null, "", "http://placko.app/admin",
                "https://placko.app.evil.test/admin", "https://evil.test@placko.app/admin",
                "https://placko.app:8443/admin", "https://agro-log.app/dashboard",
                "file:///etc/passwd", "javascript:alert(1)"}) {
            assertFalse(value, NavigationPolicy.isInternal(value));
        }
    }

    @Test public void externalActionsExcludeLocalFilesAndArbitraryIntents() {
        assertTrue(NavigationPolicy.isAllowedExternal("https://example.com"));
        assertTrue(NavigationPolicy.isAllowedExternal("mailto:info@example.com"));
        assertTrue(NavigationPolicy.isAllowedExternal("tel:+385123456"));
        for (String value : new String[]{"intent://example", "file:///sdcard/test",
                "content://example", "data:text/html,test", "javascript:alert(1)"}) {
            assertFalse(value, NavigationPolicy.isAllowedExternal(value));
        }
    }

    @Test public void printCommandRequiresExactOriginAndPath() {
        assertTrue(NavigationPolicy.isPrintRequest("https://placko.app/__aplikacija/ispis"));
        assertFalse(NavigationPolicy.isPrintRequest("https://evil.test/__aplikacija/ispis"));
        assertFalse(NavigationPolicy.isPrintRequest("https://placko.app/__aplikacija/ispis/extra"));
    }
}
