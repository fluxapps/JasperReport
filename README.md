Jasper Report
=============
This is an ILIAS wrapper for the [JasperReport library](http://community.jaspersoft.com/project/jasperreports-library). It allows ILIAS to print pretty PDF reports.

Dependencies:
- Java 8 or higher must be installed. It must be accessible by the webservice user under /usr/bin/java. You can change this in the class.JasperReport.php file on Line 189.
- For older Java versions or for non Composer version, use the follow commit: https://github.com/studer-raimann/JasperReport/commit/dc9476fcad0f8e4955ea9300581d9f6dc04ab6d6
- Make sure that the webservice user has assigned a shell (e.g. at your own risk: $ sudo chsh -s '/bin/sh' www-data). Otherwise all PDF generation responses will come back empty.

### Install
For development you should install this library like follow:

Start at your ILIAS root directory 
```bash
mkdir -p Customizing/global/plugins/Libraries/  
cd Customizing/global/plugins/Libraries/  
git clone git@git.studer-raimann.ch:ILIAS/Plugins/JasperReport.git JasperReport
```
### Usage

#### Composer
First add the follow to your `composer.json` file:
```json
"require": {
  "srag/jasperreport": "^2.0.2"
},
```

And run a `composer install`.

If you deliver your plugin, the plugin has it's own copy of this library and the user doesn't need to install the library.

You can now remove the global installed `Customizing/global/plugins/Libraries/JasperReport` on productive usages

Hint: Because of multiple autoloaders of plugins, it could be, that different versions of this library exists and suddenly your plugin use an old version of an other plugin! So you should keep up to date your plugin with `composer update`.

### Generate Jasper xml template files
[iReport Designer](https://community.jaspersoft.com/project/ireport-designer)

### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Soure Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  

