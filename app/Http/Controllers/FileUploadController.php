<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'file_name' => 'required|string',
            'INN' => 'required|file|mimes:xml', // Accept only XML files
        ]);

        // Define the uploads directory
        $uploads_dir = public_path('uploads');
        if (! is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true); // Create directory if it doesn't exist
        }

        // Clear previous uploads
        $files = glob($uploads_dir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file); // Delete each file
            }
        }

        // Process the uploaded XML file
        $xmlFile = $request->file('INN');
        $fileName = $xmlFile->getClientOriginalName();
        $xmlFile->move($uploads_dir, $fileName);

        // Rename the uploaded file
        rename("$uploads_dir/$fileName", "$uploads_dir/xml.txt");

        // Initialize the output string

        $vipiska = chr(239).chr(187).chr(191)."1CClientBankExchange\nВерсияФормата=1.03\nКодировка=DOS\n";

        $AccountNameEnglish = "";
        $FilterDateFrom     = "";
        $FilterDateTo       = "";
        $AccountNo          = "";
        $StartingBalance    = "";
        $ClosingBalance     = "";

        $TotalPaidOut       = "";
        $TotalPaidIn        = "";


        $f = fopen("uploads/xml.txt", "r");
        if(!$f){
            echo("Не удалось открыть файл"); exit();
        };


        while($line = fgets($f)){
            $utf = trim($line, " \n\r\t\v\x00");
            $eqv = mb_stripos($utf, '>') + 1;
            $len = mb_stripos($utf, '</') - $eqv;

            $s = "<gemini:AccountNameEnglish>"; if (empty($AccountNameEnglish) && mb_stripos($utf, $s) === 0){ $AccountNameEnglish   = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:FilterDateFrom>";     if (empty($FilterDateFrom)     && mb_stripos($utf, $s) === 0){ $FilterDateFrom       = str_replace('/', '.', mb_substr($utf, $eqv, $len));}
            $s = "<gemini:FilterDateTo>";       if (empty($FilterDateTo)       && mb_stripos($utf, $s) === 0){ $FilterDateTo         = str_replace('/', '.', mb_substr($utf, $eqv, $len));}
            $s = "<gemini:AccountNo>";          if (empty($AccountNo)          && mb_stripos($utf, $s) === 0){ $AccountNo            = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:StartingBalance>";    if (empty($StartingBalance)    && mb_stripos($utf, $s) === 0){ $StartingBalance      = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:ClosingBalance>";     if (empty($ClosingBalance)     && mb_stripos($utf, $s) === 0){ $ClosingBalance       = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:TotalPaidOut>";       if (empty($TotalPaidOut)       && mb_stripos($utf, $s) === 0){ $TotalPaidOut         = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:TotalPaidIn>";        if (empty($TotalPaidIn)        && mb_stripos($utf, $s) === 0){ $TotalPaidIn          = mb_substr($utf, $eqv, $len);}
        }


        $vipiska    .= "Отправитель=$AccountNameEnglish\n";
        $vipiska    .= "Получатель=\n";
        $vipiska    .= "ДатаСоздания=$FilterDateFrom\n";
        $vipiska    .= "ВремяСоздания=00:00:00\n";
        $vipiska    .= "ДатаНачала=$FilterDateFrom\n";
        $vipiska    .= "ДатаКонца=$FilterDateTo\n";
        $vipiska    .= "РасчСчет=$AccountNo\n";
        $vipiska    .= "\n";


        $vipiska    .= "СекцияРасчСчет\n";
        $vipiska    .= "ДатаНачала=$FilterDateFrom\n";
        $vipiska    .= "ДатаКонца=$FilterDateTo\n";
        $vipiska    .= "РасчСчет=$AccountNo\n";
        $vipiska    .= "НачальныйОстаток=$StartingBalance\n";
        $vipiska    .= "ВсегоПоступило=$TotalPaidIn\n";
        $vipiska    .= "ВсегоСписано=0.00\n";
        $vipiska    .= "КонечныйОстаток=$ClosingBalance\n";
        $vipiska    .= "КонецРасчСчет\n";

        $f = fopen("uploads/xml.txt", "r");
        if(!$f){
            echo("Не удалось открыть файл"); exit();
        }

        $DocumentNumber         = "";
        $Date                   = "";
        $PaidIn                 = "";
        $PaidOut                = "";
        $PartnerAccountNumber   = "";
        $PartnerName            = "";
        $PartnerBankName        = "";
        $TransactionType        = "";
        $Description            = "";
        $PartnerTaxCode         = "";

        while($line = fgets($f)){

            $utf = trim($line, " \n\r\t\v\x00");
            $eqv = mb_stripos($utf, '>') + 1;
            $len = mb_stripos($utf, '</') - $eqv;

            $s = "<gemini:DocumentNumber>";         if (empty($DocumentNumber)       && mb_stripos($utf, $s) === 0){ $DocumentNumber       = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:Date>";                   if (empty($Date)                 && mb_stripos($utf, $s) === 0){ $Date                 = str_replace('/', '.', mb_substr($utf, $eqv, $len));}
            $s = "<gemini:PaidIn>";                 if (empty($PaidIn)               && mb_stripos($utf, $s) === 0){ $PaidIn               = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:PaidOut>";                if (empty($PaidOut)              && mb_stripos($utf, $s) === 0){ $PaidOut              = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:PartnerAccountNumber>";   if (empty($PartnerAccountNumber) && mb_stripos($utf, $s) === 0){ $PartnerAccountNumber = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:PartnerName>";            if (empty($PartnerName)          && mb_stripos($utf, $s) === 0){ $PartnerName          = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:PartnerBankName>";        if (empty($PartnerBankName)      && mb_stripos($utf, $s) === 0){ $PartnerBankName      = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:TransactionType>";        if (empty($TransactionType)      && mb_stripos($utf, $s) === 0){ $TransactionType      = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:Description>";            if (empty($Description)          && mb_stripos($utf, $s) === 0){ $Description          = mb_substr($utf, $eqv, $len);}
            $s = "<gemini:PartnerTaxCode>";         if (empty($PartnerTaxCode)       && mb_stripos($utf, $s) === 0){ $PartnerTaxCode       = mb_substr($utf, $eqv, $len);}


            if ($DocumentNumber == ""
                || empty($Date)
                || (empty($PaidIn) && empty($PaidOut))
                || empty($PartnerAccountNumber)
                || empty($PartnerName)
                || empty($PartnerBankName)
                || empty($TransactionType)
                || empty($Description)
            ){ continue; }


            $vipiska    .= "\n";
            $vipiska    .= "СекцияДокумент=Платежное поручение\n";
            $vipiska    .= "Номер=$DocumentNumber\n";
            $vipiska    .= "Дата=$Date\n";

            if(empty($PaidIn)){
                $vipiska    .= "Сумма=$PaidOut\n";
                $vipiska    .= "ПлательщикСчет=\n";
                $vipiska    .= "ДатаСписано=$Date\n";


                $vipiska    .= "Плательщик=$AccountNameEnglish\n";
                $vipiska    .= "ПлательщикИНН=$request[INN]\n";
                $vipiska    .= "ПлательщикКПП=\n";
                $vipiska    .= "ПлательщикРасчСчет=$AccountNo\n";
                $vipiska    .= "ПлательщикБанк1=\n";
                $vipiska    .= "ПлательщикБИК=\n";
                $vipiska    .= "ПлательщикКорсчет=\n";
                $vipiska    .= "ПолучательСчет=$PartnerAccountNumber\n";


                $vipiska    .= "ДатаПоступило=\n";

                $vipiska    .= "Получатель=$PartnerName\n";
                $vipiska    .= "ПолучательИНН=" . str_replace("GE", "", $PartnerAccountNumber) . "\n"; //выпилить GE

                $vipiska    .= "ПолучательКПП=\n";
                $vipiska    .= "ПолучательРасчСчет=$PartnerAccountNumber\n";
                $vipiska    .= "ПолучательБанк1=$PartnerBankName\n";
                $vipiska    .= "ПолучательБИК=\n";
                $vipiska    .= "ПолучательКорсчет=\n";

                $vipiska    .= "ВидПлатежа=$TransactionType\n";
                $vipiska    .= "ВидОплаты=01\n";
                $vipiska    .= "СрокАкцепта=\n";
                $vipiska    .= "УсловиеОплаты1=\n";
                $vipiska    .= "СтатусСоставителя=\n";
                $vipiska    .= "ПоказательКБК=\n";
                $vipiska    .= "ОКАТО=0\n";
                $vipiska    .= "ПоказательОснования=0\n";
                $vipiska    .= "ПоказательПериода=0\n";
                $vipiska    .= "ПоказательНомера=0\n";
                $vipiska    .= "ПоказательДаты=0\n";
                $vipiska    .= "Очередность=5\n";
                $vipiska    .= "НазначениеПлатежа=$Description\n";
                $vipiska    .= "ВидАккредитива=\n";
                $vipiska    .= "СрокПлатежа=\n";
                $vipiska    .= "НомерСчетаПоставщика=\n";
                $vipiska    .= "ПлатежПоПредст=\n";
                $vipiska    .= "ДополнУсловия=\n";
                $vipiska    .= "ДатаОтсылкиДок=\n";
                $vipiska    .= "КонецДокумента\n";
            }else{
                $vipiska    .= "Сумма=$PaidIn\n";
                $vipiska    .= "ПлательщикСчет=$PartnerAccountNumber\n";
                $vipiska    .= "ДатаСписано=\n";
                $vipiska    .= "Плательщик=$PartnerName\n";
                $vipiska    .= "ПлательщикИНН=" . str_replace("GE", "", $PartnerAccountNumber) . "\n"; //выпилить GE


                $vipiska    .= "ПлательщикКПП=\n";
                $vipiska    .= "ПлательщикРасчСчет=$PartnerAccountNumber\n";
                $vipiska    .= "ПлательщикБанк1=$PartnerBankName\n";
                $vipiska    .= "ПлательщикБИК=\n";
                $vipiska    .= "ПлательщикКорсчет=\n";
                $vipiska    .= "ПолучательСчет=$AccountNo\n";



                $vipiska    .= "ДатаПоступило=$Date\n";


                $vipiska    .= "Получатель=$AccountNameEnglish\n";
                $vipiska    .= "ПолучательИНН=$request[INN]\n";
                $vipiska    .= "ПолучательКПП=\n";
                $vipiska    .= "ПолучательРасчСчет=$AccountNo\n";
                $vipiska    .= "ПолучательБанк1=\n";
                $vipiska    .= "ПолучательБИК=\n";
                $vipiska    .= "ПолучательКорсчет=\n";


                $vipiska    .= "ВидПлатежа=$TransactionType\n";
                $vipiska    .= "ВидОплаты=01\n";
                $vipiska    .= "СрокАкцепта=\n";
                $vipiska    .= "УсловиеОплаты1=\n";
                $vipiska    .= "СтатусСоставителя=\n";
                $vipiska    .= "ПоказательКБК=\n";
                $vipiska    .= "ОКАТО=0\n";
                $vipiska    .= "ПоказательОснования=0\n";
                $vipiska    .= "ПоказательПериода=0\n";
                $vipiska    .= "ПоказательНомера=0\n";
                $vipiska    .= "ПоказательДаты=0\n";
                $vipiska    .= "Очередность=5\n";
                $vipiska    .= "НазначениеПлатежа=$Description\n";
                $vipiska    .= "ВидАккредитива=\n";
                $vipiska    .= "СрокПлатежа=\n";
                $vipiska    .= "НомерСчетаПоставщика=\n";
                $vipiska    .= "ПлатежПоПредст=\n";
                $vipiska    .= "ДополнУсловия=\n";
                $vipiska    .= "ДатаОтсылкиДок=\n";
                $vipiska    .= "КонецДокумента\n";
            }

            $DocumentNumber         = "";
            $Date                   = "";
            $PaidIn                 = "";
            $PaidOut                = "";
            $PartnerAccountNumber   = "";
            $PartnerName            = "";
            $PartnerBankName        = "";
            $TransactionType        = "";
            $Description            = "";
            $PartnerTaxCode         = "";
        }
        $vipiska .= "\nКонецФайла";

        file_put_contents("$uploads_dir/output.txt", $vipiska);

    // Provide the text file for download
    return response()->download("$uploads_dir/output.txt")->deleteFileAfterSend(true);

    }

}
