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
### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Soure Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  

