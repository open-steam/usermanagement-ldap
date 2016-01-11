<?php
	/*
	 * Hier wird die Konfiguration der Benutzerverwaltung vorgenommen.
	 */

	// Hostname des LDAP-Servers.
	$host = 'localhost';
	//$host = 'localhost';

	// Portnummer des LDAP-Servers. Hinweis:
	// Standard TCP-Port ohne SSL-Verschlüsselung: 389
	// Standard TCP-Port mit SSL-Verschlüsselung: 636
	$port = 389;
	//$port = 23123;

	// Aktivierung oder Deaktivierung der SSL-Verschlüsselten Verbindung zwischen Client und LDAP-Server.
	// false: SSL deaktiviert
	// true: SSL aktiviert
	// Wichtig: Die Erstellung einer SSL-Verschlüsselten Verbindung ist in dieser Prototyp-Version noch nicht enthalten!
	$enableSSL = false;

	// Das Root-Verzeichnis des LDAP-Servers.
	// Beispiel: 'o=bidowl,dc=upb,dc=de'
	$root = 'ou=bidowl,dc=hnf,dc=de';

	// Loginname des benötigten Zugriffs auf den LDAP-Server für die Programmlogik der Benutzerverwaltung.
	$systemLogin = 'cn=admin,dc=hnf,dc=de';

	// Entsprechendes Passwort
	$systemPassword = 'Hier das Passwort einsetzen';

	// Benutzes LDAP-Attribut zur Realisierunug der Wiederherstellungsfunktion
	$membership = 'seeAlso';

/*define("CONF_CUSTOM_HEAD", <<<END
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://statistik.bid-owl.de/piwik/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 3]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
    g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();

</script>
<noscript><p><img src="http://statistik.bid-owl.de/piwik/piwik.php?idsite=3" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->
END
);*/
