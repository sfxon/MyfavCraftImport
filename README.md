# MyfavCraftImport

Shopware 6 Importer für die CRAFT Sportswear API. Importiert Artikel über die API in den Shopware 6 Shop.

Projekt-Status: **Unter Entwicklung**

Dieses Projekt befindet sich noch in Entwicklung. Einige Features sind bereits fertig, andere müssen noch fertiggestellt oder hinzugefügt werden.

Features, die Bereit zum Testen sind:

* Abrufen von Artikeln über die CRAFT-API und das Hinzufügen derselben als Shop-Artikel.
* Hinzufügen von Vereinen zum System als eigene Datenstruktur.
* Artikel, die von Craft heruntergeladen wurden, können Vereinen zugewiesen werden, und erstellen für diese dann neue Artikel.

Fehlende Features:

* Artikel über einen Shopware Worker aktualisieren (+ CLI-Befehl).

## Installation von Updates

Grundsätzlich sollte immer ein Backup angelegt werden, und das Update zunächst in einer Stage Umgebung getestet werden.

Ich gehe dabei in etwa so vor:

```batch
# 1. Sichern des Original-Ordners für einen schnellen Zugriff
cd shoproot
cd /custom/plugins
mv MyfavCraftImport ./../MyfavCraftImport_org

# 2. Aktuelle Version als Zip-File bereitstellen.

# 3. Zip File entpacken
unzip MyfavCraftImport_main.zip

# 4. Main-Ordner umbennen, falls notwendig
mv MyfavCraftImport_main MyfavCraftImport

# 5. Ggf. Berechtigungen korrekt setzen
sudo chmod -R 700 MyfavCraftImport
sudo chown -R www-data:www-data MyfavCraftImport

# 6. Plugin updaten
cd ./../..
bin/console plugin:update MyfavCraftImport
bin/console cache:clear
bin/build-administration.sh
```

## Information about Craft / Disclaimer

Craft is a sportswear manufacturer. We have no business relationship with Craft or Craft Sportswear. The Craft logo and the Craft brand belong to Craft respectively the rights holder.

This software is free and open source. It is published under the MIT license. Please use it at your own risk. Please extensively test the implementation with your store and/or project.