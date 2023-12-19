<?php

set_time_limit(100);
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("UTC");

define('FONTS_DIR','/usr/share/fonts/');
define('PICS_DIR','/var/www/html/blinkBot/pics/');

define('ID_SUFFIX','@example.com');
define('ID_SUFFIX2','@example2.com');

define('SEQ_FILE','/opt/scripts/idseq.txt');

require_once('/usr/share/php/tcpdf/tcpdf.php');
require_once('/opt/scripts/mrzgen.inc.php');

if(isset($argv[1]) && $argv[1]=="debug" || isset($_REQUEST['debug'])) {
        $info=[
                'id' => 'truc@example.com',
                'fct' => 'STAFF',
                'fname' => 'John',
                'lname' => 'SNITH',
                'rank' => 'CHIEF OFFICER',
                'dob' => '28/03/1972',
                'sex' => 'Male',
                'nationality' => 'FRA',
                'valid' => '02/02/2025',
                'passport' => '8121215',
                'ref' => 'H001271427',
                'tel' => '+33680245637'
        ];
        
}

function sendID($info, $id, $debug = false) {

        error_log(print_r($info,1));

        $info['fname']=strtoupper($info['fname']);
        $info['lname']=strtoupper($info['lname']);
        $info['rank']=strtoupper($info['rank']);
        $info['fct']=strtoupper($info['fct']);
        $info['ref']=strtoupper($info['ref']);

        $info['valid'] = date('d/m/Y', strtotime('+3 years'));
        $info['start'] = date('d/m/Y');

        $ym = date('ym');
        $seq = 1;
        if(file_exists(SEQ_FILE)) {
                $uidFile = file_get_contents(SEQ_FILE);
                preg_match("/\d{4}(\d{5}).*?\n$/",$uidFile,$match);

                if(isset($match[1]))
                        $seq = (int)$match[1]+1;
        }
        
        $uid = $ym.sprintf('%05d',$seq);
        
        if(!$debug)
                file_put_contents(SEQ_FILE, $uid.' '.date('Y-m-d H:i').' '.$info['id'].PHP_EOL, FILE_APPEND);
        
        $info['uid'] = $uid;

        // Geom-Graphic-W03-SemiBold.ttf
        $fontGeom = TCPDF_FONTS::addTTFfont(FONTS_DIR.'truetype/Geom-Graphic-W03-SemiBold.ttf', 'TrueTypeUnicode', '', 96);
        
        $fontOCRB = TCPDF_FONTS::addTTFfont(FONTS_DIR.'truetype/OCR-B-Regular.ttf', 'TrueTypeUnicode', '', 96);

        $pdf = new TCPDF('L', 'mm', array(86.35,54.75), PDF_PAGE_FORMAT, true, 'UTF-8', false); 

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('EXEMPLE AIRLINE');
        $pdf->SetTitle("CREW ID ".strtoupper($id));
        $pdf->SetSubject('Flight OPS');
        $pdf->SetKeywords("CREW, ID, AIRLINE, {$info['rank']}, {$info['fname']}, {$info['lname']}, {$id}");

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetAutoPageBreak(TRUE, 0);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
        }

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);        

        // add a page
        $pdf->AddPage();

        //SetMargins($left,$top,$right = -1,$keepmargins = false)
        $pdf->SetMargins(4, 1, 1, false); // set the margins

        $pdf->StartTransform();          
        $pdf->Rotate(90,61,-10);
        $pdf->SetTextColor(255, 0, 0);

        $pdf->SetFont($fontGeom, '', 30, '', false);


        if($info['fct']=='CREW') {
                if($info['rank']=='LOAD')
                        $info['rank']='LOADMASTER';
                elseif($info['rank']=='TECH')
                        $info['rank']='ENGINEER';

                $pdf->setFontSpacing(0.6);
                $aut="Le titulaire peut, à tout moment, rentrer en France, sur production du présent certificat, au cours de sa période de validité.";
        }
        else {
                $aut="<br><br>";
                $pdf->setFontSpacing(0.1);
        }

        $rank="<span class=\"rank\">{$info['rank']}</span><br>";

        if(isset($info['ref']) && $info['ref']!='' && $info['ref']!='' && $info['fct']=='CREW')
                $ref="<tr><td class=\"header\">Habilitation</td><td class=\"col\">{$info['ref']}</td></tr>";
        else
                $ref="";
        
        #Text(x, y, txt, fstroke = false, fclip = false, ffill = true, border = 0, ln = 0, align = '', fill = 0, link = '', stretch = 0, ignore_min_height = false, calign = 'T', valign = 'M', rtloff = false)
        $pdf->Text(0, 0, $info['fct']);
        $pdf->StopTransform();          
        $pdf->setFontSpacing(0);


        $pdf->setJPEGQuality(100);

        $pdf->Image($file=PICS_DIR.'logoLong2300x220.png', $x=3, $y=2, $w=80, $h=7.6521, 'PNG', '', $align='', $resize=true, $dpi=600, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);

        $pdf->setJPEGQuality(100);

        if(file_exists(PICS_DIR.$id.'.jpg'))
                $img=PICS_DIR.$id.'.jpg';
        else
                $img=PICS_DIR.'default.jpg';

        $pdf->Image($img, 4, 12, 24.8, 32, 'JPG', '', $align='', $resize=true, $dpi=600, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);

        $html=<<<EOF
<style>
        span {
                letter-spacing: -1px;
                color: #051137;
        }
        .fname {
                font-size: 14px;
                font-weight: regular;
        }
        .lname {
                font-size: 16px;
                font-weight: bold;
        }
        .rank {
                //color: #FB0D1B;
                color: #051137;
                font-size: 16px;
                //line-height: 0px;
        }
        .header {
                width: 60px;
                color: #444;
        }
        .col {
                color: #000;
                //color: #051137;
        }
        .sc {
                font-size: 18px;
        }
</style>
<span class="fname">{$info['fname']}</span><br>
<span class="lname">{$info['lname']}</span><br>
{$rank}
<table>
        <tr>
                <td class="header">Sex</td>
                <td class="col">{$info['sex']}</td>
        </tr>
        <tr>
                <td class="header">Date of Birth</td>
                <td class="col">{$info['dob']}</td>
        </tr>
        <tr>
                <td class="header">Nationality</td>
                <td class="col">{$info['nationality']}</td>
        </tr>
        <tr>
                <td class="header">Validity</td>
                <td class="col">{$info['valid']}</td>
        </tr>
        {$ref}
</table>
        EOF;

        $pdf->SetMargins(34, 1, 13, false); // set the margins

        $pdf->SetFont('helvetica', '', 8, '', false);
        $pdf->SetY(11);
        
        $pdf->writeHTML($html=$html, $ln=false, $fill=0, $reseth=false, $cell=false, $align='L');

        $html=<<<EOF
<style>
        span {
                //letter-spacing: -1px;
                color: #051137;
        }
        .fname {
                font-size: 14px;
                font-weight: regular;
        }
        .lname {
                font-size: 16px;
                font-weight: bold;
        }
        .rank {
                //color: #FB0D1B;
                color: #051137;
                font-size: 16px;
                line-height: 0px;
        }
        .cmc {
                width: 24px;
                color: #444;
        }
        .issued {
                width: 48px;
                color: #444;
        }
</style>
        EOF;

        if($info['fct']=='CREW')
                $html.="<span class=\"issued\"><b>Crew Member Certificate #{$info['uid']}</b></span><br><span class=\"cmc\">Certificat de membre d’équipage, émis en France</span>";
        else
                $html.="<span class=\"cmc\"><b>Card #{$info['uid']}</b>";


        $pdf->SetMargins(4, 1, 1, false); // set the margins

        $pdf->SetFont('helvetica', '', 8, '', false);
        $pdf->SetY(45);
        
        $pdf->writeHTML($html=$html, $ln=false, $fill=0, $reseth=false, $cell=false, $align='L');

        $pdf->SetFont($fontGeom, '', 54, '', false);
        $pdf->SetX(0);
        $pdf->SetY(68);

        $pdf->AddPage();

        $pdf->SetRightMargin(0);
        $pdf->SetRightMargin(0);

        $pdf->SetTextColor(80, 80, 80);


        $pdf->Image(PICS_DIR.'rf.png', 72, 18, 8, 5, 'PNG', '', $align='', $resize=true, $dpi=600, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);

        $pdf->Image(PICS_DIR.'eu.jpg', 72, 26, 8, 5, 'JPG', '', $align='', $resize=true, $dpi=600, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);

        $style = array(
                'border' => false,
                'padding' => 0,
                'fgcolor' => array(0, 0, 0),
                'bgcolor' => false
        );
        $pdf->write2DBarcode(createQRCode($info), 'QRCODE,H', 4, 4, 30, 30, $style, 'N');

        $html=<<<EOF
<style>
        .verso {
                font-size: 8px;
                font-weight: regular;
                text-align: justify;
        }
</style>
<span class="verso">{$aut}</span><br>
<span class="verso">Issued: {$info['start']} -  AIRLINE</span><br>
        EOF;

        $pdf->SetMargins(37, 1, 1, false); // set the margins
        $pdf->SetFont('helvetica', '', 8, '', false);
        $pdf->SetY(4);
        $pdf->writeHTML($html=$html, $ln=false, $fill=0, $reseth=false, $cell=false, $align='L');

        $html=<<<EOF
<style>
        p {
                color: #000000;
                font-size: 8px;
                //margin: 20px;
        }
</style>
<p><br>Bd Charles Gaulle,<br>4 Quai d'Arenc
<br>9125 Roissy
<br>France
<br>+33 1 24 12 55 01
<br>contact@example.com</p>
EOF;        
        $pdf->SetMargins(37, 1, 1, false); // set the margins
        $pdf->SetFont('helvetica', '', 8, '', false);
        $pdf->SetY(14);
        $pdf->writeHTML($html=$html, $ln=false, $fill=0, $reseth=false, $cell=false, $align='L');

        $pdf->SetMargins(3, 1, 0, false); // set the margins

        $mrz=str_replace('<','&lt;',mrzGenerate($info));

        $html="<style>
                .mrz {
                        //letter-spacing: -2px;
                        color: #000000;
                        //font-size: 32px;
                        //line-height: 26px;
                }
        </style>
        <span class=\"mrz\">{$mrz}</span>";

        $pdf->SetFont($fontOCRB, '', 12.6, '', false);
        $pdf->SetY(35);
        $pdf->writeHTML($html=$html, $ln=false, $fill=0, $reseth=false, $cell=false, $align='L');

        if($debug)
                die($pdf->Output("crewid-{$id}.pdf", 'I'));
        else
                $pdfstring = $pdf->Output("crewid-{$id}.pdf", 'S');

        $body="Dear ".ucfirst(strtolower($info['fname']))." {$info['lname']},<br><br>Find attached your <b>CREW AIRLINE ID</b> card.<br><br>Best regards<br><br>";

        $cc=[   'truc@example.com',
                'machin@example.com'];

        $att=['string'=>$pdfstring,'name'=>'cmaid-'.$id.'.pdf','type'=>'application/pdf'];

        sendEmail($id,"📇 CREW AIRLINE ID",$body,$cc,null,$att);

        return $uid;   
}

function createQRCode($info) {
        $org='AIRLINE';
        $url='https://example.com';
        $site = "item1.URL;type=pref:https://maps.apple.com/?ll=43.315038,5.365880&lsp=9902&q=AIRLINE%20Tower\n";
        $site.= "item1.X-ABLabel:CMA-CGM Tower";
        $add="ADR;WORK;CHARSET=UTF-8:8 rue John Smith;Roissy;;91225;France";

        $tel=$info['tel'];

        $fname=ucwords(strtolower($info['fname']));
        $lname=strtoupper($info['lname']);

        $rank=ucwords(strtolower($info['rank']));

        $vcard=("BEGIN:VCARD
VERSION:4.0
N:{$lname};{$fname};;;
ORG:{$org}
URL:{$url}
TITLE:{$rank}
{$site}
EMAIL:{$info['id']}
TEL;WORK:+33167821245
TEL;CELL;PREF:{$tel}
{$add}
END:VCARD");

        return $vcard;
}

function smallCaps($in) {
        return preg_replace('/((^| ))([A-Z])/','\\2<span class="sc">\\3</span>',strtoupper($in));        
}
