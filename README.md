Jasper Report
=============
This is an ILIAS wrapper for the [JasperReport library](http://community.jaspersoft.com/project/jasperreports-library). It allows ILIAS to print pretty PDF reports.

Dependencies:
- Java 8 or higher must be installed. It must be accessible by the webservice user under /usr/bin/java. You can change this in the class.JasperReport.php file on Line 189.
- For older Java versions or for non Composer version, use the follow commit: https://github.com/studer-raimann/JasperReport/commit/dc9476fcad0f8e4955ea9300581d9f6dc04ab6d6
- Make sure that the webservice user has assigned a shell (e.g. at your own risk: $ sudo chsh -s '/bin/sh' www-data). Otherwise all PDF generation responses will come back empty.

### Usage

#### Composer
First add the following to your `composer.json` file:
```json
"require": {
  "srag/jasperreport": ">=0.1.0"
},
```

And run a `composer install`.

If you deliver your plugin, the plugin has it's own copy of this library and the user doesn't need to install the library.

You can now remove the global installed `Customizing/global/libraries/JasperReport` on productive usages

Tip: Because of multiple autoloaders of plugins, it could be, that different versions of this library exists and suddenly your plugin use an older or a newer version of an other plugin!

So I recommand to use [srag/librariesnamespacechanger](https://packagist.org/packages/srag/librariesnamespacechanger)

### Generate Jasper xml template files
[iReport Designer](https://community.jaspersoft.com/project/ireport-designer)

### Dependencies
* PHP >=5.6
* [composer](https://getcomposer.org)
* [rdpascua/jasperstarter](https://packagist.org/packages/rdpascua/jasperstarter)
* [setasign/fpdi](https://packagist.org/packages/setasign/fpdi)
* [srag/dic](https://packagist.org/packages/srag/dic)

Please use it for further development!

### Adjustment suggestions
* Adjustment suggestions by pull requests
* Adjustment suggestions which are not yet worked out in detail by Jira tasks under https://jira.studer-raimann.ch/projects/LJASPER
* Bug reports under https://jira.studer-raimann.ch/projects/LJASPER
* For external users you can report it at https://plugins.studer-raimann.ch/goto.php?target=uihk_srsu_LJASPER
