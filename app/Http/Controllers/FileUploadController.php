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

        // Load and parse the XML file
        $xml = simplexml_load_file("$uploads_dir/$fileName");
        if (!$xml) {
            return response()->json(['error' => 'Failed to parse XML file'], 400);
        }

        // Get header information
        $header = $xml->HEADER;
        $output = "ВЫПИСКА ПО СЧЕТУ\n";
        // Add account information
        $output .= "Информация о счете:\n";
        $output .= "Наименование клиента: " . (string)$header->ClientName . "\n";
        $output .= "Номер счета: " . (string)$header->AcctNo . "\n";
        $output .= "Период: " . (string)$header->Period . "\n";
        $output .= "Валюта: " . (string)$header->Ccy . "\n\n";

        // Add summary information
        $output .= "Сводная информация:\n";
        $output .= "Входящий остаток: " . (string)$header->InAmtBasePer . " " . (string)$header->Ccy . "\n";
        $output .= "Всего поступило: " . (string)$header->InAmtPer . " " . (string)$header->Ccy . "\n";
        $output .= "Всего списано: " . (string)$header->OutAmtPer . " " . (string)$header->Ccy . "\n";
        $output .= "Исходящий остаток: " . (string)$header->OutAmtBasePer . " " . (string)$header->Ccy . "\n\n";

        // Process transactions by date
        $output .= "Операции по счету:\n";

        foreach ($xml->DETAILS as $details) {
            $date = (string)$details['DATE'];
            $output .= "Дата: " . $date . "\n";
            foreach ($details->DETAIL as $detail) {
                $output .= "Детали операции:\n";
                $output .= "Номер документа: " . (string)$detail->DocNo . "\n";
                $output .= "Сумма: " . (string)$detail->DocDstAmt . " " . (string)$detail->DocDstCcy . "\n";
                $output .= "Назначение платежа: " . (string)$detail->DocNomination . "\n";

                if ((string)$detail->DocBenefAcctNo === (string)$header->AcctNo) {
                    $output .= "Тип операции: Поступление\n";
                    $output .= "От кого: " . (string)$detail->DocSenderName . "\n";
                    $output .= "Счет отправителя: " . (string)$detail->DocSenderAcctNo . "\n";
                    $output .= "Банк отправителя: " . (string)$detail->DocSenderBicName . "\n";
                } else {
                    $output .= "Тип операции: Списание\n";
                    $output .= "Кому: " . (string)$detail->DocBenefName . "\n";
                    $output .= "Счет получателя: " . (string)$detail->DocBenefAcctNo . "\n";
                    $output .= "Банк получателя: " . (string)$detail->DocBenefBicName . "\n";
                }

                $output .= "Остаток: " . (string)$detail->OutBalance . " " . (string)$detail->DocDstCcy . "\n";
            }

            // Add daily summary
            $detHead = $details->DET_HEAD;
            $output .= "Итого за день:\n";
            $output .= "Общая сумма: " . (string)$detHead->DayAmt . " " . (string)$header->Ccy . "\n";
            $output .= "Сумма поступлений: " . (string)$detHead->VsumCr . " " . (string)$header->Ccy . "\n";
            $output .= "Количество операций: " . (string)$detHead->DayEntryCount . "\n";
        }

        // Save the converted file
        $outputFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.txt';
        file_put_contents("$uploads_dir/$outputFileName", $output);

        // Provide the text file for download
        return response()->download("$uploads_dir/$outputFileName")->deleteFileAfterSend(true);
    }
}
