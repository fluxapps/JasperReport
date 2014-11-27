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
###Contact
studer + raimann ag  
Waldeggstrasse 72  
3097 Liebefeld  
Switzerland  

info@studer-raimann.ch  
www.studer-raimann.ch
