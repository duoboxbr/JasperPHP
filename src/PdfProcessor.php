<?php

namespace JasperPHP;

use JasperPHP;
use \TCPDF;

/**
 * classe Instruction
 * classe para construção de rótulos de texto
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.11
 * @access   restrict
 *
 * 2015.03.11 -- criação
 * */
class PdfProcessor {

    private $jasperObj;
    private $print_expression_result;

    public function __construct(\JasperPHP\Report $jasperObj) {

        $this->jasperObj = $jasperObj;
    }

    public static function prepare($report) {
        JasperPHP\Instructions::$arrayPageSetting = $report->arrayPageSetting;
        if ($report->arrayPageSetting["orientation"] == "Landscape") {
            JasperPHP\Instructions::$objOutPut = new TCPDF($report->arrayPageSetting["orientation"], 'pt', array(intval($report->arrayPageSetting["pageHeight"]), intval($report->arrayPageSetting["pageWidth"])), true);
        } else {
            JasperPHP\Instructions::$objOutPut = new TCPDF($report->arrayPageSetting["orientation"], 'pt', array(intval($report->arrayPageSetting["pageWidth"]), intval($report->arrayPageSetting["pageHeight"])), true);
        }
        JasperPHP\Instructions::$objOutPut->SetLeftMargin((int) $report->arrayPageSetting["leftMargin"]);
        JasperPHP\Instructions::$objOutPut->SetRightMargin((int) $report->arrayPageSetting["rightMargin"]);
        JasperPHP\Instructions::$objOutPut->SetTopMargin((int) $report->arrayPageSetting["topMargin"]);
        JasperPHP\Instructions::$objOutPut->SetAutoPageBreak(true, (int) $report->arrayPageSetting["bottomMargin"] / 2);
        //self::$pdfOutPut->AliasNumPage();
        JasperPHP\Instructions::$objOutPut->setPrintHeader(false);
        JasperPHP\Instructions::$objOutPut->setPrintFooter(false);
        JasperPHP\Instructions::$objOutPut->AddPage();
        JasperPHP\Instructions::$objOutPut->setPage(1, true);
        JasperPHP\Instructions::$y_axis = (int) $report->arrayPageSetting["topMargin"];

        if (JasperPHP\Instructions::$fontdir == "")
            JasperPHP\Instructions::$fontdir = dirname(__FILE__) . "/tcpdf/fonts";
    }
    public static function PageNo(){
        JasperPHP\Instructions::$objOutPut->PageNo();
    }

    public static function get() {
        JasperPHP\Instructions::$objOutPut;
    }

    public function PreventY_axis($arraydata) {
        //$pdf = \JasperPHP\Pdf;
        $pageHeader = $this->jasperObj->getChildByClassName('PageHeader');
        $preventY_axis = JasperPHP\Instructions ::$y_axis + $arraydata['y_axis'];
        $pageheight = JasperPHP\Instructions::$arrayPageSetting["pageHeight"];
        $pageFooter = $this->jasperObj->getChildByClassName('PageFooter');
        $pageFooterHeigth = ($pageFooter) ? $pageFooter->children[0]->height : 0;
        $topMargin = JasperPHP\Instructions::$arrayPageSetting["topMargin"];
        $bottomMargin = JasperPHP\Instructions::$arrayPageSetting["bottomMargin"];
        $discount = $pageheight - $pageFooterHeigth - $topMargin - $bottomMargin; //dicount heights of page parts;
        // var_dump($pageFooter);
        //exit;


        if ($preventY_axis >= $discount) {
            if ($pageFooter) {
                $pageFooter->generate(array($this->jasperObj, array('counter' => true)));
            }
            JasperPHP\Instructions::addInstruction(array("type" => "resetY_axis"));
            JasperPHP\Instructions::$currrentPage++;
            JasperPHP\Instructions::addInstruction(array("type" => "AddPage"));
            JasperPHP\Instructions::addInstruction(array("type" => "setPage", "value" => JasperPHP\Instructions::$currrentPage, 'resetMargins' => false));
            JasperPHP\Instructions::runInstructions();
            $pageHeader = $this->jasperObj->getChildByClassName('PageHeader');
            if (JasperPHP\Instructions::$print_expression_result == true) {
                if ($pageHeader)
                    $pageHeader->generate($this->jasperObj);
            }
            JasperPHP\Instructions::runInstructions();
        }
    }

    public function resetY_axis($arraydata) {

        JasperPHP\Instructions::$y_axis = (int) JasperPHP\Instructions::$arrayPageSetting["topMargin"];
    }

    public function SetY_axis($arraydata) {
        if ((JasperPHP\Instructions::$y_axis + $arraydata['y_axis']) <= JasperPHP\Instructions::$arrayPageSetting["pageHeight"]) {
            JasperPHP\Instructions::$y_axis = JasperPHP\Instructions::$y_axis + $arraydata['y_axis'];
        }
    }

    public function ChangeCollumn($arraydata) {
        $pdf = JasperPHP\Instructions;
        if (JasperPHP\Instructions::$arrayPageSetting['columnCount'] > (JasperPHP\Instructions::$arrayPageSetting["CollumnNumber"])) {
            JasperPHP\Instructions::$arrayPageSetting["leftMargin"] = JasperPHP\Instructions::$arrayPageSetting["defaultLeftMargin"] + (JasperPHP\Instructions::$arrayPageSetting["columnWidth"] * JasperPHP\Instructions::$arrayPageSetting["CollumnNumber"]);
            JasperPHP\Instructions::$arrayPageSetting["CollumnNumber"] = JasperPHP\Instructions::$arrayPageSetting['CollumnNumber'] + 1;
        } else {
            JasperPHP\Instructions::$arrayPageSetting["CollumnNumber"] = 1;
            JasperPHP\Instructions::$arrayPageSetting["leftMargin"] = JasperPHP\Instructions::$arrayPageSetting["defaultLeftMargin"];
        }
    }

    public function AddPage($arraydata) {
        // $pdf = JasperPHP\Pdf;
        JasperPHP\Instructions::$objOutPut->AddPage();
    }

    public function setPage($arraydata) {
        //$pdf = JasperPHP\Pdf;
        JasperPHP\Instructions::$objOutPut->setPage($arraydata["value"], $arraydata["resetMargins"]);
    }

    public function SetFont($arraydata) {
        $arraydata["font"] = strtolower($arraydata["font"]);

        $fontfile = JasperPHP\Instructions::$fontdir . '/' . $arraydata["font"] . '.php';
        // if(file_exists($fontfile) || $this->jasperObj->bypassnofont==false){

        $fontfile = JasperPHP\Instructions::$fontdir . '/' . $arraydata["font"] . '.php';

        JasperPHP\Instructions::$objOutPut->SetFont($arraydata["font"], $arraydata["fontstyle"], $arraydata["fontsize"], $fontfile);
        /* }
          else{
          $arraydata["font"]="freeserif";
          if($arraydata["fontstyle"]=="")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserif',$arraydata["fontstyle"],$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserif.php');
          elseif($arraydata["fontstyle"]=="B")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifb',$arraydata["fontstyle"],$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifb.php');
          elseif($arraydata["fontstyle"]=="I")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifi',$arraydata["fontstyle"],$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifi.php');
          elseif($arraydata["fontstyle"]=="BI")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifbi',$arraydata["fontstyle"],$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifbi.php');
          elseif($arraydata["fontstyle"]=="BIU")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifbi',"BIU",$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifbi.php');
          elseif($arraydata["fontstyle"]=="U")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserif',"U",$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserif.php');
          elseif($arraydata["fontstyle"]=="BU")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifb',"U",$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifb.php');
          elseif($arraydata["fontstyle"]=="IU")
          JasperPHP\Pdf::$pdfOutPut->SetFont('freeserifi',"IU",$arraydata["fontsize"],JasperPHP\Pdf::$fontdir.'/freeserifbi.php');


          } */
    }

    public function MultiCell($arraydata) {

        //if($fielddata==true) {
        $this->checkoverflow($arraydata, $arraydata["txt"], null);
        //}
    }

    public function SetXY($arraydata) {
        JasperPHP\Instructions::$objOutPut->SetXY($arraydata["x"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Instructions::$y_axis);
    }

    public function Cell($arraydata) {
        //                print_r($arraydata);
        //              echo "<br/>";
        //JasperPHP\Pdf::$pdfOutPut->Cell($arraydata["width"], $arraydata["height"], $this->jasperObj->updatePageNo($arraydata["txt"]), $arraydata["border"], $arraydata["ln"], $arraydata["align"], $arraydata["fill"], $arraydata["link"] . "", 0, true, "T", $arraydata["valign"]);
    }

    public function Rect($arraydata) {
        if ($arraydata['mode'] == 'Transparent')
            $style = '';
        else
            $style = 'FD';
        //      JasperPHP\Pdf::$pdfOutPut->SetLineStyle($arraydata['border']);
        JasperPHP\Instructions::$objOutPut->Rect($arraydata["x"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $style, $arraydata['border'], $arraydata['fillcolor']);
    }

    public function RoundedRect($arraydata) {
        if ($arraydata['mode'] == 'Transparent')
            $style = '';
        else
            $style = 'FD';
        //
        //        JasperPHP\Pdf::$pdfOutPut->SetLineStyle($arraydata['border']);
        JasperPHP\Instructions::$objOutPut->RoundedRect($arraydata["x"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $arraydata["radius"], '1111', $style, $arraydata['border'], $arraydata['fillcolor']);
    }

    public function Ellipse($arraydata) {
        //JasperPHP\Pdf::$pdfOutPut->SetLineStyle($arraydata['border']);
        JasperPHP\Instructions::$objOutPut->Ellipse($arraydata["x"] + $arraydata["width"] / 2 + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Instructions::$y_axis + $arraydata["height"] / 2, $arraydata["width"] / 2, $arraydata["height"] / 2, 0, 0, 360, 'FD', $arraydata['border'], $arraydata['fillcolor']);
    }

    public function Image($arraydata) {
        //echo $arraydata["path"];
        $path = $arraydata["path"];
        $imgtype = mb_substr($path, -3);
        $arraydata["link"] = $arraydata["link"] . "";
        if ($imgtype == 'jpg')
            $imgtype = "JPEG";
        elseif ($imgtype == 'png' || $imgtype == 'PNG')
            $imgtype = "PNG";
        // echo $path;
        if (file_exists($path) || mb_substr($path, 0, 4) == 'http') {
            //echo $path;
            JasperPHP\Instructions::$objOutPut->Image($path, $arraydata["x"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Instructions::$y_axis, $arraydata["width"], $arraydata["height"], $imgtype, $arraydata["link"]);
        } elseif (mb_substr($path, 0, 21) == "data:image/jpg;base64") {
            $imgtype = "JPEG";
            //echo $path;
            $img = str_replace('data:image/jpg;base64,', '', $path);
            $imgdata = base64_decode($img);
            JasperPHP\Instructions::$objOutPut->Image('@' . $imgdata, $arraydata["x"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Instructions::$y_axis, $arraydata["width"], $arraydata["height"], '', $arraydata["link"]);
        } elseif (mb_substr($path, 0, 22) == "data:image/png;base64,") {
            $imgtype = "PNG";
            // JasperPHP\Pdf::$pdfOutPut->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $img = str_replace('data:image/png;base64,', '', $path);
            $imgdata = base64_decode($img);


            JasperPHP\Instructions::$objOutPut->Image('@' . $imgdata, $arraydata["x"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Instructions::$y_axis, $arraydata["width"], $arraydata["height"], '', $arraydata["link"]);
        }
    }

    public function SetTextColor($arraydata) {

        //if($this->jasperObj->hideheader==true && $this->jasperObj->currentband=='pageHeader')
        //    JasperPHP\Pdf::$pdfOutPut->SetTextColor(100,33,30);
        //else
        JasperPHP\Instructions::$objOutPut->SetTextColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
    }

    public function SetDrawColor($arraydata) {
        JasperPHP\Instructions::$objOutPut->SetDrawColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
    }

    public function SetLineWidth($arraydata) {
        JasperPHP\Instructions::$objOutPut->SetLineWidth($arraydata["width"]);
    }

    public function breaker($arraydata) {
        $this->print_expression($arraydata);
        $pageFooter = $this->jasperObj->getChildByClassName('PageFooter');
        if ($this->print_expression_result == true) {
            if ($pageFooter)
                $pageFooter->generate($this->jasperObj);
            JasperPHP\Instructions::addInstruction(array("type" => "resetY_axis"));
            JasperPHP\Instructions::$currrentPage++;
            JasperPHP\Instructions::addInstruction(array("type" => "AddPage"));
            JasperPHP\Instructions::addInstruction(array("type" => "setPage", "value" => JasperPHP\Instructions::$currrentPage, 'resetMargins' => false));
            $pageHeader = $this->jasperObj->getChildByClassName('PageHeader');
            //if (JasperPHP\Pdf::$print_expression_result == true) {
            if ($pageHeader)
                $pageHeader->generate($this->jasperObj);
            //}
            JasperPHP\Instructions::runInstructions();
        }
    }

    public function Line($arraydata) {
        $this->print_expression($arraydata);
        if ($this->print_expression_result == true) {
            JasperPHP\Instructions::$objOutPut->Line($arraydata["x1"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y1"] + JasperPHP\Instructions::$y_axis, $arraydata["x2"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y2"] + JasperPHP\Instructions::$y_axis, $arraydata["style"]);
        }
    }

    public function SetFillColor($arraydata) {
        JasperPHP\Instructions::$objOutPut->SetFillColor($arraydata["r"], $arraydata["g"], $arraydata["b"]);
    }

    public function lineChart($arraydata) {

        // $this->generateLineChart($arraydata, JasperPHP\Pdf::$y_axis);
    }

    public function barChart($arraydata) {

        // $this->generateBarChart($arraydata, JasperPHP\Pdf::$y_axis, 'barChart');
    }

    public function pieChart($arraydata) {

        //$this->generatePieChart($arraydata, JasperPHP\Pdf::$y_axis);
    }

    public function stackedBarChart($arraydata) {

        //$this->generateBarChart($arraydata, JasperPHP\Pdf::$y_axis, 'stackedBarChart');
    }

    public function stackedAreaChart($arraydata) {

        //$this->generateAreaChart($arraydata, JasperPHP\Pdf::$y_axis, $arraydata["type"]);
    }

    public function Barcode($arraydata) {

        $this->showBarcode($arraydata, JasperPHP\Instructions::$y_axis);
    }

    public function CrossTab($arraydata) {

        //$this->generateCrossTab($arraydata, JasperPHP\Pdf::$y_axis);
    }

    public function showBarcode($data, $y) {

        $pdf = JasperPHP\Instructions::get();
        $type = strtoupper($data['barcodetype']);
        $height = $data['height'];
        $width = $data['width'];
        $x = $data['x'];
        $y = $data['y'] + $y;
        $textposition = $data['textposition'];
        $code = $data['code'];
        //$code=$this->analyse_expression($code);
        $modulewidth = $data['modulewidth'];
        if ($textposition == "" || $textposition == "none")
            $withtext = false;
        else
            $withtext = true;

        $style = array(
            'border' => false,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'text' => $withtext,
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );


        //[2D barcode section]
        //DATAMATRIX
        //QRCODE,H or Q or M or L (H=high level correction, L=low level correction)
        // -------------------------------------------------------------------
        // PDF417 (ISO/IEC 15438:2006)

        /*

          The $type parameter can be simple 'PDF417' or 'PDF417' followed by a
          number of comma-separated options:

          'PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6'

          Possible options are:

          a  = aspect ratio (width/height);
          e  = error correction level (0-8);

          Macro Control Block options:

          t  = total number of macro segments;
          s  = macro segment index (0-99998);
          f  = file ID;
          o0 = File Name (text);
          o1 = Segment Count (numeric);
          o2 = Time Stamp (numeric);
          o3 = Sender (text);
          o4 = Addressee (text);
          o5 = File Size (numeric);
          o6 = Checksum (numeric).

          Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional.
          To use a comma character ',' on text options, replace it with the character 255: "\xff".

         */
        switch ($type) {
            case "PDF417":
                $pdf->write2DBarcode($code, 'PDF417', $x, $y, $width, $height, $style, 'N');
                break;
            case "DATAMATRIX":

                //$this->pdf->Cell( $width,10,$code);
                //echo $this->left($code,3);
                if ($this->left($code, 3) == "QR:") {

                    $code = $this->right($code, strlen($code) - 3);

                    $pdf->write2DBarcode($code, 'QRCODE', $x, $y, $width, $height, $style, 'N');
                } else
                    $pdf->write2DBarcode($code, 'DATAMATRIX', $x, $y, $width, $height, $style, 'N');
                break;
            case "CODE128":

                $pdf->write1DBarcode($code, 'C128', $x, $y, $width, $height, $modulewidth, $style, 'N');

                // $this->pdf->write1DBarcode($code, 'C128', $x, $y, $width, $height,"", $style, 'N');
                break;
            case "EAN8":
                $pdf->write1DBarcode($code, 'EAN8', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "EAN13":
                $pdf->write1DBarcode($code, 'EAN13', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "CODE39":
                $pdf->write1DBarcode($code, 'C39', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "CODE93":
                $pdf->write1DBarcode($code, 'C93', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
            case "I25":
            case "INT2OF5":
                $pdf->write1DBarcode($code, 'I25', $x, $y, $width, $height, $modulewidth, $style, 'N');
                break;
        }
    }

    public function checkoverflow($obj) {
        $pdf = JasperPHP\Instructions::$objOutPut;
        $JasperObj = $this->jasperObj;
        // var_dump($obj->children);
        $txt = (string) $obj['txt'];
        //$newfont = $JasperObj->recommendFont($txt, null, null);
        //$pdf->SetFont($newfont,$pdf->getFontStyle(),$this->defaultFontSize);
        $this->print_expression($obj);
        $arraydata = $obj;

        $pdf->SetXY($arraydata["x"] + JasperPHP\Instructions::$arrayPageSetting["leftMargin"], $arraydata["y"] + JasperPHP\Instructions::$y_axis);
        if ($this->print_expression_result == true) {
            $angle = $this->rotate($arraydata);
            if ($angle != 0) {
                $pdf->StartTransform();
                $pdf->Rotate($angle);
            }
            // echo $arraydata["link"];
            if ($arraydata["link"]) {
                //print_r($arraydata);
                //$this->debughyperlink=true;
                //  echo $arraydata["link"].",print:".$this->print_expression_result;
                //$arraydata["link"] = $JasperObj->analyse_expression($arraydata["link"], "");
                //$this->debughyperlink=false;
            }
            //print_r($arraydata);


            /* @var \TCPDF $pdf */
            if ($arraydata["writeHTML"] == true) {
                //echo  ($txt);
                $pdf->writeHTML($txt, true, 0, true, true);
                $pdf->Ln();
                /* if($this->currentband=='detail'){
                  if($this->maxpagey['page_'.($pdf->getPage()-1)]=='')
                  $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                  else{
                  if($this->maxpagey['page_'.($pdf->getPage()-1)]<$pdf->GetY())
                  $this->maxpagey['page_'.($pdf->getPage()-1)]=$pdf->GetY();
                  }
                  } */
            } elseif ($arraydata["poverflow"] == "false" && $arraydata["soverflow"] == "false") {
                if ($arraydata["valign"] == "M")
                    $arraydata["valign"] = "C";
                if ($arraydata["valign"] == "")
                    $arraydata["valign"] = "T";

                // $text = $txt[0];
                while ($pdf->GetStringWidth($txt) > $arraydata["width"]) { // aka a gambiarra da gambiarra funcionan assim nao mude a naão ser que de problema seu bosta
                    if ($txt != $pdf->getAliasNbPages() && $txt != ' ' . $pdf->getAliasNbPages()) {
                        $txt = mb_substr($txt, 0, -1, 'UTF-8');
                    }
                }

                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pattern = (array_key_exists("pattern", $arraydata)) ? $arraydata["pattern"] : '';
                $text = $pattern != '' ? $JasperObj->formatText($txt, $pattern) : $txt;
                if ($arraydata['multiCell'] === true) {

                    $pdf->MultiCell(
                            $arraydata["width"], $arraydata["height"], $text, $arraydata["border"], $arraydata["align"], 0, 0, $x, $y, true, 0);
                } else {
                    $pdf->Cell($arraydata["width"], $arraydata["height"], $text, $arraydata["border"], "", $arraydata["align"], $arraydata["fill"], $arraydata["link"], 0, true, "T", $arraydata["valign"]);
                }
            } elseif ($arraydata["poverflow"] == "true") {
                if ($arraydata["valign"] == "C")
                    $arraydata["valign"] = "M";
                if ($arraydata["valign"] == "")
                    $arraydata["valign"] = "T";

                $x = $pdf->GetX();
                $yAfter = $pdf->GetY();
                $maxheight = array_key_exists('maxheight', $arraydata) ? $arraydata['maxheight'] : '';
                //if($arraydata["link"])   echo $arraydata["linktarget"].",".$arraydata["link"]."<br/><br/>";
                $pdf->MultiCell($arraydata["width"], $arraydata["height"], $JasperObj->formatText($txt, $arraydata["pattern"]), $arraydata["border"]
                        , $arraydata["align"], $arraydata["fill"], 1, '', '', true, 0, false, true, $maxheight); //,$arraydata["valign"]);
                if (($yAfter + $arraydata["height"]) <= JasperPHP\Instructions::$arrayPageSetting["pageHeight"]) {
                    JasperPHP\Instructions::$y_axis = $pdf->GetY() - 20;
                }
            } elseif ($arraydata["soverflow"] == "true") {

                if ($arraydata["valign"] == "M")
                    $arraydata["valign"] = "C";
                if ($arraydata["valign"] == "")
                    $arraydata["valign"] = "T";

                $pdf->Cell($arraydata["width"], $arraydata["height"], $JasperObj->formatText($txt, $arraydata["pattern"]), $arraydata["border"], "", $arraydata["align"], $arraydata["fill"], $arraydata["link"] . "", 0, true, "T", $arraydata["valign"]);
                $pdf->Ln();
            } else {
                $pdf->MultiCell($arraydata["width"], $arraydata["height"], $JasperObj->formatText($txt, $arraydata["pattern"]), $arraydata["border"], $arraydata["align"], $arraydata["fill"], 1, '', '', true, 0, true, true, $maxheight);
            }
        }
    }

    public function print_expression($data) {
        $expression = $data["printWhenExpression"];
        $this->print_expression_result = false;
        if ($expression != "") {
            //echo      'if('.$expression.'){$this->print_expression_result=true;}';
            //$expression=$this->analyse_expression($expression);
            error_reporting(0);
            eval('if(' . $expression . '){$this->print_expression_result=true;}');
            error_reporting(5);
        } else
            $this->print_expression_result = true;
    }

    public function rotate($arraydata) {
        $pdf = JasperPHP\Instructions::$objOutPut;
        if (array_key_exists("rotation", $arraydata)) {
            $type = (string) $arraydata["rotation"];
            $angle = null;
            if ($type == "")
                $angle = 0;
            elseif ($type == "Left")
                $angle = 90;
            elseif ($type == "Right")
                $angle = 270;
            elseif ($type == "UpsideDown")
                $angle = 180;

            return $angle;
        }
    }

}
