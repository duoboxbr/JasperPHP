![alt text](https://jasperphp.com/wp-content/uploads/2020/01/cropped-ms-icon-150x150-2.png) 

# JasperPHP
Project to generate reports created with the JasperSoft Studio application<br>
Pure PHP library, without a java server or Jasper Server

Consider donating funds to support us
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EE7CD4UZEL3A4&source=url)

See more blog, forum and coming soon report template store in https://jasperphp.com 

# Export formats
 PDF <br>
 XLS

# Supported tags/components
<table>
    <tr>
        <td>TAG/component</td>
        <td>Status</td>
        <td>TAG/component</td>
        <td>Status</td>
    </tr>
    <tr>
        <td colspan="4">Basic elements</td>
    </tr>
    <tr>
        <td>Text Field</td>
        <td>OK</td>
        <td>Static Text</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Image</td>
        <td>OK</td>
        <td>Break</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Rectangle</td>
        <td>OK</td>
        <td>Line</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>SubReport*</td>
        <td>OK</td>
        <td>Barcode</td>
        <td>OK</td>
    </tr>
    <tr>
        <td colspan="4">Composite elements</td>
    </tr>
    <tr>
        <td>Page Number</td>
        <td>OK</td>
        <td>Total Pages</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Current Date</td>
        <td>OK</td>
        <td>Page X of Y</td>
        <td>OK</td>
    </tr>
    <tr>
        <td colspan="4">Bands</td>
    </tr>
    <tr>
        <td>Title</td>
        <td>OK</td>
        <td>Page Header</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Group</td>
        <td>OK</td>
        <td>Detail</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Column Header</td>
        <td>OK</td>
        <td>Column Footer</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Page Footer</td>
        <td>OK</td>
        <td>Sumary</td>
        <td>OK</td>
    </tr>
    <tr>
        <td>Background</td>
        <td>OK</td>
    </tr>
</table>
* Subreports are supported recursively and unlimited

# Other features
<lu>
    <li>sum, average,minimum, max of variables</li>
    <li>read and calculate subreport variables</li>
    <li>array of objects as input data</li>
    <li>textfield with html render with data replacement</li>
    <li>active record</li>
</lu>
<br>

# Generic sample
```php
<?php
use JasperPHP\Report;
use JasperPHP\Report2XLS;
use JasperPHP\ado\TTransaction;
use JasperPHP\ado\TLoggerHTML;

//use PHPexcel as PHPexcel;
/**
* classe TJasper
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.net>
* @version  2015.03.11
* @access   restrict
* 
* 2015.03.11 -- create
**/
class TJasper
{
    private $report;
    private $type;

    /**
    * method __construct()
    * 
    * @param $jrxml = a source xmlr filename
    * @param $param = a array whith params
    */
    public function __construct($jrxml,array $param)
    {
        $xmlFile=  $jrxml;
        $this->type = (array_key_exists('type',$param))?$param['type']:'pdf';
        error_reporting(0);
        switch ($this->type)
        {
            case 'pdf': 
                $this->report =new JasperPHP\Report($xmlFile,$param);
                JasperPHP\Pdf::prepare($this->report);
                break;
            case 'xls':
                JasperPHP\Excel::prepare();
                $this->report =new JasperPHP\Report2XLS($xmlFile,$param);
                
                break;
        }
    }
    
    /**
    * method outpage()
    * 
    * @param $type = a type of output. ALERT: xls is experimental
    */

    public function outpage($type='pdf'){
        $this->report->generate();
        $this->report->out();
        switch ($this->type)
        {
            case 'pdf':
                $pdf  = JasperPHP\Pdf::get();
                $pdf->Output('Relatorio.pdf',"I");
                break;
            case 'xls':
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="01simple.xls"');
                header('Cache-Control: max-age=0');
                // If you're serving to IE 9, then the following may be needed
                header('Cache-Control: max-age=1');
                // If you're serving to IE over SSL, then the following may be needed
                header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header ('Pragma: public'); // HTTP/1.0
                $objWriter = PHPExcel_IOFactory::createWriter($this->report->wb, 'Excel5');
                $objWriter->save('php://output');
            break;
        }
        
    }
    /**
    * method setVariable()
     * insert variable into report after output
    * 
    * @param $name = name of variable
    * @param $value = value of variable
    */
    public function setVariable($name,$value){
        $this->report->arrayVariable[$name]['initialValue'] = $value ;
    }
}
require('autoloader.php') ;
require('../../tecnickcom/tcpdf/tcpdf.php'); // point to tcpdf class previosly instaled , 
                                            // on composer instalation is not necessaty 

TTransaction::open('dev');
$jasper = new TJasper('template.jrxml',$_GET);
$jasper->outpage();
?>

```

# Requirements
* PHP 5.2+
* "tecnickcom/tcpdf":"6.2.*"
* "PHPOffice/PHPExcel" only of XLS export

# How to use this sample
Define database conections params into file config\dev.ini<br>
View file src\ado\TConection.php to define database type<br>
Sample URL:<br>
http://localhost/vendor/quilhasoft/JasperPHP/TJasper.class.php?param1=foo&param2=bar<br>
URL params passed into URL are the params defined into xmlr file.<br>
# Using composer
Add "quilhasoft/jasperphp":"dev-master" into your composer config file and update/install

# Live samples<br>
* A basic test: <a href='http://quilhasoft.net/Jasper/vendor/quilhasoft/JasperPHP/TJasper.class.php'>Here</a><br>
* A burn test, 201 pages, hosted in a default hostgator server: <a href='http://quilhasoft.net/Jasper/vendor/quilhasoft/JasperPHP/TJasper.class.php?report=cities/region.jrxml'>Here</a><br>
* Brasilian payment method "boleto" in "carne":<a href='http://quilhasoft.net/Jasper/vendor/quilhasoft/JasperPHP-OpenBoleto/itauJasper.php'>Here</a><br>
* Brasilian payment method "boleto" in A4 :<a href='http://quilhasoft.net/Jasper/vendor/quilhasoft/JasperPHP-OpenBoleto/itauJasperA4.php'>Here</a><br>
 ** Brasilian boleto project disponible in QuilhaSoft/JasperPHP-OpenBoleto.

## License

* MIT License
