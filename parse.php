<?php

class Parser {
  public $instruction_counter;

  function errorExit($message, $error_code)
{
  fwrite(STDERR, $message);
  exit($error_code);
}

function argumentCountCheck($words, $expected_number)
{
  if (count($words) != $expected_number)
    $this->errorExit("Spatny pocet operandu!\n", 23);
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
        $this->errorExit("Regex error!\n", 23);
      break;
    case "string":
      $word = substr($word, strlen("string")+1); 
      if(!preg_match('/^(?:[^\s#\\\\]|\\\\(?:\d\d\d))*$/', $word))
        $this->errorExit("Regex error!\n", 23);
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
    $this->errorExit("Neznamy typ!\n", 23);
  }
  return $ok;
}

function parseNoArgs($words, $xml)
{
  $this->argumentCountCheck($words, 1);
}

function parseVar($words, $xml)
{
  $this->argumentCountCheck($words, 2);

  if ($this->varCheck($words[1]) == 1)
    $xml->writeElement("arg1", "var", $words[1]);
  else
  $this->errorExit("Argument neni var!\n", 23);
}

function parseVarSymb($words, $xml)
{
  $this->argumentCountCheck($words, 3);

  if ($this->varCheck($words[1]) == 1)
    $xml->writeElement("arg1", "var", $words[1]);
  else
  $this->errorExit("Argument neni var!\n", 23);

  if ($this->varCheck($words[2]) == 1)
    $xml->writeElement("arg2", "var", $words[2]);
  else if ($this->constCheck($words[2]) == 1)
    $xml->writeElement("arg2", strtok($words[2], '@'), substr($words[2], strpos($words[2], "@") + 1));
  else
  $this->errorExit("Argument neni symb!\n", 23);
}

function parseVarSymbSymb($words, $xml)
{
  $this->argumentCountCheck($words, 4);

  if ($this->varCheck($words[1]) == 1)
    $xml->writeElement("arg1", "var", $words[1]);
  else
  $this->errorExit("Argument neni var!\n", 23);

  if ($this->varCheck($words[2]) == 1)
    $xml->writeElement("arg2", "var", $words[2]);
  else if ($this->constCheck($words[2]) == 1)
    $xml->writeElement("arg2", strtok($words[2], '@'), substr($words[2], strpos($words[2], "@") + 1));
  else
  $this->errorExit("Argument neni symb!\n", 23);

  if ($this->varCheck($words[3]) == 1)
    $xml->writeElement("arg3", "var", $words[3]);
  else if ($this->constCheck($words[3]) == 1)
    $xml->writeElement("arg3", strtok($words[3], '@'), substr($words[3], strpos($words[3], "@") + 1));
  else
  $this->errorExit("Argument neni symb!\n", 23);
}

function parseLabel($words, $xml)
{
  $this->argumentCountCheck($words, 2);
  
  if($this->labelCheck($words[1]) == 1)
    $xml->writeElement("arg1", "label", $words[1]);
  else
  $this->errorExit("Argument neni typu label!\n", 23);
}

function parseSymb($words, $xml)
{
  $this->argumentCountCheck($words, 2);

  if ($this->varCheck($words[1]) == 1)
    $xml->writeElement("arg1", "var", $words[1]);
  else if ($this->constCheck($words[1]) == 1)
    $xml->writeElement("arg1", strtok($words[1], '@'), substr($words[1], strpos($words[1], "@") + 1));
  else
  $this->errorExit("Argument neni symb!\n", 23);
}

function parseVarType($words, $xml)
{
  $this->argumentCountCheck($words, 3);

  if ($this->varCheck($words[1]) == 1)
    $xml->writeElement("arg1", "var", $words[1]);
  else
  $this->errorExit("Argument neni symb!\n", 23);

  if (strcmp($words[2], "int") == 0 || strcmp($words[2], "string") == 0 || strcmp($words[2], "bool") == 0)
    $xml->writeElement("arg2", "type", $words[2]);
  else
  $this->errorExit("Argument neni type!\n", 23);
}

function parseLabelSymbSymb($words, $xml)
{
  $this->argumentCountCheck($words, 4);
  
  if($this->labelCheck($words[1]) == 1)
    $xml->writeElement("arg1", "label", $words[1]);
  else
  $this->errorExit("Argument neni label!\n", 23);

  if ($this->varCheck($words[2]) == 1)
    $xml->writeElement("arg2", "var", $words[2]);
  else if ($this->constCheck($words[2]) == 1)
    $xml->writeElement("arg2", strtok($words[2], '@'), substr($words[2], strpos($words[2], "@") + 1));
  else
  $this->errorExit("Argument neni symb!\n", 23);

  if ($this->varCheck($words[3]) == 1)
    $xml->writeElement("arg3", "var", $words[3]);
  else if ($this->constCheck($words[3]) == 1)
    $xml->writeElement("arg3", strtok($words[3], '@'), substr($words[3], strpos($words[3], "@") + 1));
  else
    $this->errorExit("Argument neni symb!\n", 23);
}
}

class Xml {
  public $xml;

function xmlStart()
{
  $this->xml = new XMLWriter();
  $this->xml->openUri('php://stdout');
  $this->xml->setIndent(2);
  $this->xml->startDocument("1.0", "UTF-8");
  $this->xml->startElement("program");
  $this->xml->startAttribute("language");
  $this->xml->text("IPPcode23");
  $this->xml->endAttribute();
}

function xmlEnd()
{
  $this->xml->endElement();
  $this->xml->endDocument();
}

function writeInstruction($instruction_counter, $word)
{ $this->xml->startElement("instruction");
  $this->xml->startAttribute("order");
  $this->xml->text($instruction_counter);
  $this->xml->endAttribute();
  $this->xml->startAttribute("opcode");
  $this->xml->text($word);
  $this->xml->endAttribute();
}
function endInstruction()
{
  $this->xml->endElement();
}

function writeElement($arg, $type, $text)
{
    $this->xml->startElement($arg);
    $this->xml->startAttribute("type");
    $this->xml->text($type);
    $this->xml->endAttribute();
    $this->xml->text($text);
    $this->xml->endElement();
}
}

$header = 0;
$parser = new Parser();
$parser->instruction_counter = 1;
$xml = new Xml();

if ($argc > 2)
{
  $parser->errorExit("Prilis mnoho arumentu!\n", 10);
}
if ($argc == 2)
{
  if (strcmp($argv[1], "--help") != 0)
  {
    $parser->errorExit("Neplatny argument!\n", 10);
  }
  echo "Skript nacte ze standardniho vstupu zdrojovy kod v IPPcode23, zkontroluje lexikalni a syntaktickou spravnost kodu a vypise na standardni
vystup XML reprezentaci programu.\n
--help    Vypise na standardni vystup napovedu skriptu\n
Chybove navratove kody:
21 - chybna nebo chybejici hlavicka ve zdrojovem kodu zapsanem v IPPcode23;
22 - neznamy nebo chybny operacni kod ve zdrojovem kodu zapsanem v IPPcode23;
23 - jina lexikalni nebo syntakticka chyba zdrojoveho kódu zapsaneho v IPPcode23.\n";
  exit(0);
}

$header = 0;
$parser = new Parser();
$parser->instruction_counter = 1;
$xml = new Xml();
$xml->xmlStart();

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
    $parser->errorExit("Spatna hlavicka!\n", 21);
    $header = 1;
    continue;
  }

  $xml->writeInstruction($parser->instruction_counter, $word);
  switch ($word) {
    case "MOVE":
    case "INT2CHAR":
    case "STRLEN":
    case "TYPE":
    case "NOT":
      $parser->parseVarSymb($words, $xml);
      break;
    case "CREATEFRAME":
    case "PUSHFRAME":
    case "POPFRAME":
    case "RETURN":
    case "BREAK":
      $parser->parseNoArgs($words, $xml);
      break;
    case "DEFVAR":
    case "POPS":
      $parser->parseVar($words, $xml);
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
      $parser->parseVarSymbSymb($words, $xml);
      break;
    case "LABEL";
    case "JUMP":
    case "CALL":
      $parser->parseLabel($words, $xml);
      break;
    case "PUSHS":
    case "WRITE":
    case "EXIT":
    case "DPRINT":
      $parser->parseSymb($words, $xml);
      break;
    case "READ":
      $parser->parseVarType($words, $xml);
      break;
    case "JUMPIFEQ":
    case "JUMPIFNEQ":
      $parser->parseLabelSymbSymb($words, $xml);
      break;
    default:
    $parser->errorExit("Neznama operace\n", 22);
  }
  $xml->endInstruction();
  $parser->instruction_counter++;
}
$xml->xmlEnd();
exit(0);
?>