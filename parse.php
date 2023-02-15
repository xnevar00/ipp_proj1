<?php

$xml = new XMLWriter();
$xml->openUri('php://stdout');
$xml->startDocument("1.0", "UTF-8");
$xml->startElement("program");
$xml->startAttribute("language");
$xml->text("IPPcode23");
$xml->endAttribute();



while(!feof(STDIN))
{
    $line = fgets(STDIN);
    $line = rtrim($line, "\n");
    $words = explode(' ', $line);
    $i = 0;
    foreach ($words as $word) {
        //echo $word; 
    }
  }
  $xml->endElement();
  $xml->endDocument();
?>