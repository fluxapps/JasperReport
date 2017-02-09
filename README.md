Jasper Report
=============
This is an ILIAS wrapper for the [JasperReport library](http://community.jaspersoft.com/project/jasperreports-library). It allows ILIAS to print pretty PDF reports.

Dependencies:
- Java 1.6 or higher must be installed. It must be accessible by the webservice user under /usr/bin/java. You can change this in the class.JasperReport.php file on Line 109.
- Make sure that the webservice user has assigned a shell (e.g. at your own risk: $ sudo chsh -s '/bin/sh' www-data). Otherwise all PDF generation responses will come back empty.

###Installation
Start in your ILIAS root directory
```bash
cd Customizing/global/plugins/
mkdir Libraries
cd Libraries
git clone https://github.com/studer-raimann/JasperReport.git
``` 
### Hinweis Plugin-Patenschaft
Grundsätzlich veröffentlichen wir unsere Plugins (Extensions, Add-Ons), weil wir sie für alle Community-Mitglieder zugänglich machen möchten. Auch diese Extension wird der ILIAS Community durch die studer + raimann ag als open source zur Verfügung gestellt. Diese Plugin hat noch keinen Plugin-Paten. Das bedeutet, dass die studer + raimann ag etwaige Fehlerbehebungen, Supportanfragen oder die Release-Pflege lediglich für Kunden mit entsprechendem Hosting-/Wartungsvertrag leistet. Falls Sie nicht zu unseren Hosting-Kunden gehören, bitten wir Sie um Verständnis, dass wir leider weder kostenlosen Support noch Release-Pflege für Sie garantieren können.

Sind Sie interessiert an einer Plugin-Patenschaft (https://studer-raimann.ch/produkte/ilias-plugins/plugin-patenschaften/ ) Rufen Sie uns an oder senden Sie uns eine E-Mail.

###Contact
studer + raimann ag  
Waldeggstrasse 72  
3097 Liebefeld  
Switzerland  

info@studer-raimann.ch  
www.studer-raimann.ch
