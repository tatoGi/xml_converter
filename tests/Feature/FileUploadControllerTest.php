<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileUploadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_matching_uses_tax_id_from_xml()
    {
        // Create sample XML content with Tax IDs
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
<STATEMENT>
    <HEADER>
        <ClientName>Test Company</ClientName>
        <Period>01.01.2025</Period>
        <AcctNo>GE30BG4501981900680000</AcctNo>
        <InAmtBasePer>1000.00</InAmtBasePer>
        <OutAmtBasePer>500.00</OutAmtBasePer>
        <InAmtPer>1000.00</InAmtPer>
        <OutAmtPer>500.00</OutAmtPer>
    </HEADER>
    <DETAILS>
        <DETAIL>
            <DocNo>123456</DocNo>
            <DocValueDate>01.01.2025</DocValueDate>
            <EntryCrAmt>100.00</EntryCrAmt>
            <EntryAmtBase></EntryAmtBase>
            <DocBenefAcctNo>GE30BG0000000664216500GEL</DocBenefAcctNo>
            <DocBenefName>Test Partner</DocBenefName>
            <DocBenefInn>405167947</DocBenefInn>
            <DocBenefBicName>Test Bank</DocBenefBicName>
            <DocPayerInn>204378869</DocPayerInn>
            <DocNomination>Payment</DocNomination>
            <DocInformation>Test payment</DocInformation>
        </DETAIL>
    </DETAILS>
</STATEMENT>';

        // Create a temporary XML file
        $xmlFile = UploadedFile::fake()->createWithContent('test.xml', $xmlContent);

        // Make the request
        $response = $this->post('/upload', [
            'file_name' => 'test.xml',
            'INN' => $xmlFile
        ]);

        // Assert the response is successful
        $response->assertStatus(200);

        // Check if the output file was created
        $this->assertFileExists(public_path('uploads/output.txt'));

        // Read the output file and verify Tax ID is used
        $output = file_get_contents(public_path('uploads/output.txt'));

        // Verify that the Tax ID (405167947) is used instead of the account number
        $this->assertStringContainsString('ПолучательИНН=405167947', $output);

        // Verify that the account number logic is NOT used (no GE prefix removal)
        $this->assertStringNotContainsString('ПолучательИНН=30BG0000000664216500GEL', $output);
    }

    public function test_contractor_matching_falls_back_to_account_number_when_tax_id_missing()
    {
        // Create sample XML content without Tax IDs
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
<STATEMENT>
    <HEADER>
        <ClientName>Test Company</ClientName>
        <Period>01.01.2025</Period>
        <AcctNo>GE30BG4501981900680000</AcctNo>
        <InAmtBasePer>1000.00</InAmtBasePer>
        <OutAmtBasePer>500.00</OutAmtBasePer>
        <InAmtPer>1000.00</InAmtPer>
        <OutAmtPer>500.00</OutAmtPer>
    </HEADER>
    <DETAILS>
        <DETAIL>
            <DocNo>123456</DocNo>
            <DocValueDate>01.01.2025</DocValueDate>
            <EntryCrAmt>100.00</EntryCrAmt>
            <EntryAmtBase></EntryAmtBase>
            <DocBenefAcctNo>GE30BG0000000664216500GEL</DocBenefAcctNo>
            <DocBenefName>Test Partner</DocBenefName>
            <DocBenefInn></DocBenefInn>
            <DocBenefBicName>Test Bank</DocBenefBicName>
            <DocPayerInn></DocPayerInn>
            <DocNomination>Payment</DocNomination>
            <DocInformation>Test payment</DocInformation>
        </DETAIL>
    </DETAILS>
</STATEMENT>';

        // Create a temporary XML file
        $xmlFile = UploadedFile::fake()->createWithContent('test.xml', $xmlContent);

        // Make the request
        $response = $this->post('/upload', [
            'file_name' => 'test.xml',
            'INN' => $xmlFile
        ]);

        // Assert the response is successful
        $response->assertStatus(200);

        // Read the output file and verify fallback logic is used
        $output = file_get_contents(public_path('uploads/output.txt'));

        // Verify that the account number logic is used (GE prefix removed)
        $this->assertStringContainsString('ПолучательИНН=30BG0000000664216500GEL', $output);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (file_exists(public_path('uploads/output.txt'))) {
            unlink(public_path('uploads/output.txt'));
        }

        parent::tearDown();
    }
}
