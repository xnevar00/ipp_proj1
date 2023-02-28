<?php

function xmlStart(XMLWriter $xml)
{
  $xml->openUri('php://stdout');
  $xml->setIndent(2);
  $xml->startDocument("1.0", "UTF-8");
  $xml->startElement("program");
  $xml->startAttribute("language");
  $xml->text("IPPcode23");
  $xml->endAttribute();
}

function xmlEnd(XMLWriter $xml)
{
  $xml->endElement();
  $xml->endDocument();
}

function errorExit($message, $error_code)
{
  fwrite(STDERR, $message);
  exit($error_code);
}
function writeInstruction($instruction_counter, $xml, $word)
{ $xml->startElement("instruction");
  $xml->startAttribute("order");
  $xml->text($instruction_counter);
  $xml->endAttribute();
  $xml->startAttribute("opcode");
  $xml->text($word);
  $xml->endAttribute();
}

function endInstruction($xml)
{
  $xml->endElement();
}

function writeElement($arg, $type, $text, $xml)
{
    $xml->startElement($arg);
    $xml->startAttribute("type");
    $xml->text($type);
    $xml->endAttribute();
    $xml->text($text);
    $xml->endElement();
}

function argumentCountCheck($words, $expected_number)
{
  if (count($words) != $expected_number)
    errorExit("Spatny pocet operandu!\n", 23);
}

function labelCheck($word)
{
  $ok = 1;
  foreach (str_split($word) as $char) {
    if (($char >= '0' && $char <= '9') || ($char >= 'A' && $char <= 'Z') || ($char >= 'a' && $char <= 'z') || $char == '_' || $char == '-' || $char == '$'|| $char == '&' || $char == '%' || $char == '*' || $char == '!' || $char == '?' )
    {
      if ($char == $word[0] && ($char >= '0' && $char <= '9'))
        $ok = 0;
    } else
      $ok = 0;
  }
  return $ok;
}

function varCheck($word)
{
  $ok = 1;
  $length = strlen($word);
  $fr = substr($word, 0, 2-$length);
  switch ($fr) {
    case "GF":
    case "TF":
    case "LF":
        break;
    default:
        $ok = 0;
        break;
  }
  $word = substr($word, 2);   //removing processed characters from string
  if ($word[0] != '@')
    $ok = 0;
  $word = substr($word, 1);
  if (strlen($word) == 0)
    $ok = 0;
  foreach (str_split($word) as $char) {
    if (($char >= '0' && $char <= '9') || ($char >= 'A' && $char <= 'Z') || ($char >= 'a' && $char <= 'z') || $char == '_' || $char == '-' || $char == '$'|| $char == '&' || $char == '%' || $char == '*' || $char == '!' || $char == '?' )
    {
      if ($char == $word[0] && ($char >= '0' && $char <= '9'))
        $ok = 0;
    } else
      $ok = 0;
  }
  return $ok;
}

function constCheck($word)
{
  $ok = 1;
  $type = strtok($word, '@'); //getting the type of symbol
  switch($type){
    case "int":
      $word = substr($word, strlen("int")+1); 
      if(!preg_match('/^[+|-]?([1-9][0-9]*(_[0-9]+)*)|0|(0[xX][0-9a-fA-F]+(_[0-9a-fA-F]+)*)|(0[oO]?[0-7]+(_[0-7]+)*)$/', $word))
        errorExit("Regex error!\n", 23);
      break;
    case "string":
      $word = substr($word, strlen("string")+1); 
      if(!preg_match('/^(?:[^\s#\\\\]|\\\\(?:\d\d\d))*$/', $word))
        errorExit("Regex error!\n", 23);
      break;
    case "bool":
      $word = substr($word, strlen("bool")+1); 
      if (strcmp($word, "true") != 0 && strcmp($word, "false") != 0)
        $ok = 0;
      break;
    case "nil":
      $word = substr($word, strlen("nil")+1); 
      if (strcmp($word, "nil") == 0)
        $ok = 1;
      else
        $ok = 0;
      break;
    default:
      errorExit("Neznamy typ!\n", 23);
  }
  return $ok;
}

function parseNoArgs($words, $xml)
{
  argumentCountCheck($words, 1);
}

function parseVar($words, $xml)
{
  argumentCountCheck($words, 2);

  if (varCheck($words[1]) == 1)
    writeElement("arg1", "var", $words[1], $xml);
  else
    errorExit("Argument neni var!\n", 23);
}

function parseVarSymb($words, $xml)
{
  argumentCountCheck($words, 3);

  if (varCheck($words[1]) == 1)
    writeElement("arg1", "var", $words[1], $xml);
  else
    errorExit("Argument neni var!\n", 23);

  if (varCheck($words[2]) == 1)
    writeElement("arg2", "var", $words[2], $xml);
  else if (constCheck($words[2]) == 1)
    writeElement("arg2", strtok($words[2], '@'), substr($words[2], strpos($words[2], "@") + 1), $xml);
  else
    errorExit("Argument neni symb!\n", 23);
}

function parseVarSymbSymb($words, $xml)
{
  argumentCountCheck($words, 4);

  if (varCheck($words[1]) == 1)
    writeElement("arg1", "var", $words[1], $xml);
  else
    errorExit("Argument neni var!\n", 23);

  if (varCheck($words[2]) == 1)
    writeElement("arg2", "var", $words[2], $xml);
  else if (constCheck($words[2]) == 1)
    writeElement("arg2", strtok($words[2], '@'), substr($words[2], strpos($words[2], "@") + 1), $xml);
  else
    errorExit("Argument neni symb!\n", 23);

  if (varCheck($words[3]) == 1)
    writeElement("arg3", "var", $words[3], $xml);
  else if (constCheck($words[3]) == 1)
    writeElement("arg3", strtok($words[3], '@'), substr($words[3], strpos($words[3], "@") + 1), $xml);
  else
    errorExit("Argument neni symb!\n", 23);
}

function parseLabel($words, $xml)
{
  argumentCountCheck($words, 2);
  
  if(labelCheck($words[1]) == 1)
    writeElement("arg1", "label", $words[1], $xml);
  else
    errorExit("Argument neni typu label!\n", 23);
}

function parseSymb($words, $xml)
{
  argumentCountCheck($words, 2);

  if (varCheck($words[1]) == 1)
    writeElement("arg1", "var", $words[1], $xml);
  else if (constCheck($words[1]) == 1)
    writeElement("arg1", strtok($words[1], '@'), substr($words[1], strpos($words[1], "@") + 1), $xml);
  else
    errorExit("Argument neni symb!\n", 23);
}

function parseVarType($words, $xml)
{
  argumentCountCheck($words, 3);

  if (varCheck($words[1]) == 1)
    writeElement("arg1", "var", $words[1], $xml);
  else
    errorExit("Argument neni symb!\n", 23);

  if (strcmp($words[2], "int") == 0 || strcmp($words[2], "string") == 0 || strcmp($words[2], "bool") == 0)
    writeElement("arg2", "type", $words[2], $xml);
  else
    errorExit("Argument neni type!\n", 23);
}

function parseLabelSymbSymb($words, $xml)
{
  argumentCountCheck($words, 4);
  
  if(labelCheck($words[1]) == 1)
    writeElement("arg1", "label", $words[1], $xml);
  else
    errorExit("Argument neni label!\n", 23);

  if (varCheck($words[2]) == 1)
    writeElement("arg2", "var", $words[2], $xml);
  else if (constCheck($words[2]) == 1)
    writeElement("arg2", strtok($words[2], '@'), substr($words[2], strpos($words[2], "@") + 1), $xml);
  else
    errorExit("Argument neni symb!\n", 23);

  if (varCheck($words[3]) == 1)
    writeElement("arg3", "var", $words[3], $xml);
  else if (constCheck($words[3]) == 1)
    writeElement("arg3", strtok($words[3], '@'), substr($words[3], strpos($words[3], "@") + 1), $xml);
  else
    errorExit("Argument neni symb!\n", 23);
}

$header = 0;
$instruction_counter = 1;
$xml = new XMLWriter();
xmlStart($xml);

while(!feof(STDIN))
{
  $line = fgets(STDIN);
  $line = explode('#', $line)[0];
  $line = trim($line);
  $words = preg_split('/\s+/', $line);

  $i = 0;
  $word = $words[0];
  $word = strtoupper($word);
  if (strlen($line) == 0)
    continue;

  if ($header == 0)
  {
    if (strcmp($words[0], ".IPPcode23") != 0 || (count($words) != 1 && $words[1][0] != '#'))
      errorExit("Spatna hlavicka!\n", 21);
    $header = 1;
    continue;
  }

  writeInstruction($instruction_counter, $xml, $word);
  switch ($word) {
    case "MOVE":
    case "INT2CHAR":
    case "STRLEN":
    case "TYPE":
    case "NOT":
      parseVarSymb($words, $xml);
      break;
    case "CREATEFRAME":
    case "PUSHFRAME":
    case "POPFRAME":
    case "RETURN":
    case "BREAK":
      parseNoArgs($words, $xml);
      break;
    case "DEFVAR":
    case "POPS":
      parseVar($words, $xml);
      break;
    case "ADD":
    case "SUB":
    case "MUL":
    case "IDIV":
    case "LT":
    case "GT":
    case "EQ":
    case "AND":
    case "OR":
    case "STRI2INT":
    case "CONCAT":
    case "GETCHAR":
    case "SETCHAR":
      parseVarSymbSymb($words, $xml);
      break;
    case "LABEL";
    case "JUMP":
    case "CALL":
      parseLabel($words, $xml);
      break;
    case "PUSHS":
    case "WRITE":
    case "EXIT":
    case "DPRINT":
      parseSymb($words, $xml);
      break;
    case "READ":
      parseVarType($words, $xml);
      break;
    case "JUMPIFEQ":
    case "JUMPIFNEQ":
      parseLabelSymbSymb($words, $xml);
      break;
    default:
      errorExit("Neznama operace\n", 22);
  }
  endInstruction($xml);
  $instruction_counter++;
}
xmlEnd($xml);
?>