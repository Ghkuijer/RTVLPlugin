#RTVLPlugin

Deze wordpress plugin is ontwikkeld voor radio omroepen.
Het zorgt voor een speciaal menu waar de programmering en programma's van de omroep beheerd kunnen worden. 

Vervolgens kan er via shortcodes de programmering en/of programmalijst afgebeeld worden.

In de plugin zit ook functionaliteit ingebouwd voor RTV Lansingerland, de lokale omroep van Lansingerland. 

Zo zorgt de plugin voor programma specifieke menu's op de programma pagina's. Deze functionaliteit past in de layout van de RTV Lansingerland website en is dus niet altijd geschikt. Ook is er een webcamstream pagina en livestream pagina in deze plugin meegenomen die niet altijd geschikt is.

Voor mensen die deze plugin graag willen overnemen en willen aanpassen om hun doel te dienen is dat toegestaan.

##Shortcodes
**[rtv_programmering day=""]** Voor "day" kan de eerste drie letters van een dag ingevuld worden, bijvoorbeeld "Mon" of "Tue". De eerste letter moet met hoofdletter. Ook kan "day" leeg gelaten worden. Als er een dag is gespecificeerd wordt er een tabel gegenereert en afgebeeld voor die bepaalde dag. Als er geen dag is gespecificeerd wordt de volledige programmering afgebeeld.

**[rtv_programs day=""]** De dag kan op dezelfde manier gespecificeerd worden als bij de bovenstaande shortcode. Als er een dag ingevuld is wordt er een lijst met alle programma's, omschrijvingen, presentatoren, etc. gegenereert, als er geen dag gespecificeerd is worden alle programma's in de database in een lijst afgebeeld met alle eigenschappen.