<?php

$docxFile = __DIR__ . '/../SKRIPSI.docx';

if (!file_exists($docxFile)) {
    echo "File not found.\n";
    exit;
}

$zip = new ZipArchive;
if ($zip->open($docxFile) === TRUE) {
    if (($index = $zip->locateName('word/document.xml')) !== false) {
        $data = $zip->getFromIndex($index);
        $zip->close();

        $dom = new DOMDocument();
        $dom->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        $text = $dom->saveXML();
        $text = strip_tags($text);
        
        // Output parts of text containing KNN or Rute
        $lines = explode("\n", wordwrap($text, 100));
        foreach ($lines as $line) {
            if (stripos($line, 'knn') !== false || stripos($line, 'rute') !== false) {
                echo trim($line) . "\n";
            }
        }
    } else {
        echo "document.xml not found in docx.\n";
    }
} else {
    echo "Failed to open zip.\n";
}
