# Plottracker
Mit dem Plottracker lassen sich über das AdminCP Plots samt Erklärungstext (sämtliche Formatierungsmöglichkeiten erlaubt) und Zeitraum erstellen. Diese werden anschließend unter url.de/plottracker.php aufgelistet. User können beim Erstellen einer neuen Szene angeben, ob die Szene Teil eines der angegebenen Plots ist. In der Plotübersicht werden die Szenen angezeigt, die mit dem Plot verknüpft wurden.

# Datenbankänderungen
Folgende Tabellen werden der Datenbank hinzugefügt:

- plots
- plots_threads

# Neue Templates
Folgende Templates werden mit diesem Plugin hinzugefügt:

- plottracker
- plottracker_nav
- plottracker_nav_bit
- plottracker_newthread
- plottracker_view
- plottracker_view_threads
- plottracker_view_threads_bit

# Template-Änderungen
In folgende Templates

- newthread
- editpost

werden folgende Variablen direkt nach {$prefixselect} gesetzt:

- $newthread_plottracker
- $editpost_plottracker

# Demo
<center><img src="https://snipboard.io/GukcrT.jpg" />

<img src="https://snipboard.io/Kt2U0Z.jpg" />

<img src="https://snipboard.io/0yDP4Z.jpg" />

<img src="https://snipboard.io/3BNWpe.jpg" />

<img src="https://snipboard.io/oq73x1.jpg" />
</center>
