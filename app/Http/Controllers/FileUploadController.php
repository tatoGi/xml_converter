<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
       
        // Validate the incoming request
        $request->validate([
            'file_name' => 'required|file|mimes:xml',
            'INN' => 'required', // Accept only XML files
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
        $xmlFile = $request->file('file_name');
        $fileName = $xmlFile->getClientOriginalName();
        $xmlFile->move($uploads_dir, $fileName);

        // Load and parse the XML file
        $xml = simplexml_load_file("$uploads_dir/$fileName");
        if (!$xml) {
            return response()->json(['error' => 'Failed to parse XML file'], 400);
        }

        // Initialize the output string with 1C format header
        $vipiska = chr(239).chr(187).chr(191)."1CClientBankExchange\nВерсияФормата=1.03\nКодировка=DOS\n";

        // Get header information
        $header = $xml->HEADER;
        $AccountNameEnglish = (string)$header->ClientName;
        $FilterDateFrom = str_replace('/', '.', (string)$header->Period);
        $FilterDateTo = str_replace('/', '.', (string)$header->Period);
        $AccountNo = (string)$header->AcctNo;
        $StartingBalance = (string)$header->InAmtBasePer;
        $ClosingBalance = (string)$header->OutAmtBasePer;
        $TotalPaidIn = (string)$header->InAmtPer;
        $TotalPaidOut = (string)$header->OutAmtPer;

        // Write header section
        $vipiska .= "Отправитель=$AccountNameEnglish\n";
        $vipiska .= "Получатель=\n";
        $vipiska .= "ДатаСоздания=$FilterDateFrom\n";
        $vipiska .= "ВремяСоздания=00:00:00\n";
        $vipiska .= "ДатаНачала=$FilterDateFrom\n";
        $vipiska .= "ДатаКонца=$FilterDateTo\n";
        $vipiska .= "РасчСчет=$AccountNo\n\n";

        // Write account section
        $vipiska .= "СекцияРасчСчет\n";
        $vipiska .= "ДатаНачала=$FilterDateFrom\n";
        $vipiska .= "ДатаКонца=$FilterDateTo\n";
        $vipiska .= "РасчСчет=$AccountNo\n";
        $vipiska .= "НачальныйОстаток=$StartingBalance\n";
        $vipiska .= "ВсегоПоступило=$TotalPaidIn\n";
        $vipiska .= "ВсегоСписано=$TotalPaidOut\n";
        $vipiska .= "КонечныйОстаток=$ClosingBalance\n";
        $vipiska .= "КонецРасчСчет\n";

        // Process transactions
        foreach ($xml->DETAILS as $details) {
            foreach ($details->DETAIL as $detail) {
                $DocumentNumber = (string)$detail->DocNo;
                $Date = str_replace('/', '.', (string)$detail->DocValueDate);
                $PaidIn = (string)$detail->EntryCrAmt;
                $PaidOut = (string)$detail->EntryAmtBase;
                $PartnerAccountNumber = (string)$detail->DocBenefInn;
                $PartnerName = (string)$detail->DocBenefName;
                $PartnerBankName = (string)$detail->DocBenefBicName;
                $TransactionType = (string)$detail->DocNomination;
                $Description = (string)$detail->DocInformation;

                if (empty($DocumentNumber) || empty($Date) || (empty($PaidIn) && empty($PaidOut))) {
                    continue;
                }

                $vipiska .= "\nСекцияДокумент=Платежное поручение\n";
                $vipiska .= "Номер=$DocumentNumber\n";
                $vipiska .= "Дата=$Date\n";

                if (empty($PaidIn)) {
                    // Outgoing payment
                    $vipiska .= "Сумма=$PaidOut\n";
                    $vipiska .= "ПлательщикСчет=\n";
                    $vipiska .= "ДатаСписано=$Date\n";
                    $vipiska .= "Плательщик=$AccountNameEnglish\n";
                    $vipiska .= "ПлательщикИНН=$PartnerAccountNumber\n";
                    $vipiska .= "ПлательщикКПП=\n";
                    $vipiska .= "ПлательщикРасчСчет=$AccountNo\n";
                    $vipiska .= "ПлательщикБанк1=\n";
                    $vipiska .= "ПлательщикБИК=\n";
                    $vipiska .= "ПлательщикКорсчет=\n";
                    $vipiska .= "ПолучательСчет=$PartnerAccountNumber\n";
                    $vipiska .= "ДатаПоступило=\n";
                    $vipiska .= "Получатель=$PartnerName\n";
                    $vipiska .= "ПолучательИНН=" . $PartnerAccountNumber . "\n";
                    $vipiska .= "ПолучательКПП=\n";
                    $vipiska .= "ПолучательРасчСчет=$PartnerAccountNumber\n";
                    $vipiska .= "ПолучательБанк1=$PartnerBankName\n";
                    $vipiska .= "ПолучательБИК=\n";
                    $vipiska .= "ПолучательКорсчет=\n";
                } else {
                    // Incoming payment
                    $vipiska .= "Сумма=$PaidIn\n";
                    $vipiska .= "ПлательщикСчет=$PartnerAccountNumber\n";
                    $vipiska .= "ДатаСписано=\n";
                    $vipiska .= "Плательщик=$PartnerName\n";
                    $vipiska .= "ПлательщикИНН=" . $PartnerAccountNumber . "\n";
                    $vipiska .= "ПлательщикКПП=\n";
                    $vipiska .= "ПлательщикРасчСчет=$PartnerAccountNumber\n";
                    $vipiska .= "ПлательщикБанк1=$PartnerBankName\n";
                    $vipiska .= "ПлательщикБИК=\n";
                    $vipiska .= "ПлательщикКорсчет=\n";
                    $vipiska .= "ПолучательСчет=$AccountNo\n";
                    $vipiska .= "ДатаПоступило=$Date\n";
                    $vipiska .= "Получатель=$AccountNameEnglish\n";
                    $vipiska .= "ПолучательИНН=$request[INN]\n";
                    $vipiska .= "ПолучательКПП=\n";
                    $vipiska .= "ПолучательРасчСчет=$AccountNo\n";
                    $vipiska .= "ПолучательБанк1=\n";
                    $vipiska .= "ПолучательБИК=\n";
                    $vipiska .= "ПолучательКорсчет=\n";
                }

                // Common fields for both incoming and outgoing payments
                $vipiska .= "ВидПлатежа=$TransactionType\n";
                $vipiska .= "ВидОплаты=01\n";
                $vipiska .= "СрокАкцепта=\n";
                $vipiska .= "УсловиеОплаты1=\n";
                $vipiska .= "СтатусСоставителя=\n";
                $vipiska .= "ПоказательКБК=\n";
                $vipiska .= "ОКАТО=0\n";
                $vipiska .= "ПоказательОснования=0\n";
                $vipiska .= "ПоказательПериода=0\n";
                $vipiska .= "ПоказательНомера=0\n";
                $vipiska .= "ПоказательДаты=0\n";
                $vipiska .= "Очередность=5\n";
                $vipiska .= "НазначениеПлатежа=$Description\n";
                $vipiska .= "ВидАккредитива=\n";
                $vipiska .= "СрокПлатежа=\n";
                $vipiska .= "НомерСчетаПоставщика=\n";
                $vipiska .= "ПлатежПоПредст=\n";
                $vipiska .= "ДополнУсловия=\n";
                $vipiska .= "ДатаОтсылкиДок=\n";
                $vipiska .= "КонецДокумента\n";
            }
        }

        $vipiska .= "\nКонецФайла";

        // Save the output file
        file_put_contents("$uploads_dir/output.txt", $vipiska);

        // Provide the text file for download
        return response()->download("$uploads_dir/output.txt")->deleteFileAfterSend(true);

    }

}
