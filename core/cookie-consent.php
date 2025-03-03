<?php
if (!isset($_COOKIE['cookie_consent'])):
    foreach ($_COOKIE as $key => $value) {
        setcookie($key, '', time() - 3600, '/', '', true, true);
    }
    ?>
    <div id="cookie-banner" style="z-index: 10; position: fixed; padding: 10px 0; border-top: 1px solid #ddd; bottom: 0; left: 0; width: 100%; background: #222; color: white; text-align: center;">
        <p>This website uses cookies to enhance your experience. Do you accept cookies? <a style="color: orange" target="_blank" href="https://www.google.com/search?q=cookies%20law">More Information</a></p>
        <button style="background-color: orange; border: 0; cursor: pointer" onclick="setCookieConsent('accepted')">Accept</button>
        <button style="background-color: orange; border: 0; cursor: pointer" onclick="hideBanner()">Reject</button>
    </div>

    <script>
        function setCookieConsent(choice) {
            document.cookie = "cookie_consent=" + choice + "; path=/; max-age=" + (365 * 24 * 60 * 60);
            document.getElementById("cookie-banner").style.display = "none";
        }

        function hideBanner() {
            document.getElementById("cookie-banner").style.display = "none";
        }
    </script>
<?php endif; ?>
