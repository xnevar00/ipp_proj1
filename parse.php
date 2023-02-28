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

function labelCheck($word)
{
  $ok = 1;
  foreach (str_split($word) as $char) {
    if (($char >= '0' && $char <= '9') || ($char >= 'A' && $char <= 'Z') || ($char >= 'a' && $char <= 'z') || $char == '_' || $char == '-' || $char == '$'|| $char == '&' || $char == '%' || $char == '*' || $char == '!' || $char == '?' )
    {
      if ($char == $word[0] && ($char >= '0' && $char <= '9'))
      {
        $ok = 0;
      }
    } else
    {
      $ok = 0;
    }
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
  {
    $ok = 0;
  }
  $word = substr($word, 1);
  if (strlen($word) == 0)
  {
    $ok = 0;
  }
  foreach (str_split($word) as $char) {
    if (($char >= '0' && $char <= '9') || ($char >= 'A' && $char <= 'Z') || ($char >= 'a' && $char <= 'z') || $char == '_' || $char == '-' || $char == '$'|| $char == '&' || $char == '%' || $char == '*' || $char == '!' || $char == '?' )
    {
      if ($char == $word[0] && ($char >= '0' && $char <= '9'))
      {
        $ok = 0;
      }
    } else
    {
      $ok = 0;
    }
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
      {
        printf("Regex error\n");
        exit(23);
      }
      break;
    case "string":
      $word = substr($word, strlen("string")+1); 
      if(!preg_match('/^(?:[^\s#\\\\]|\\\\(?:\d\d\d))*$/', $word))
      {
        printf("Regex error\n");
        exit(23);
      }
      break;
    case "bool":
      $word = substr($word, strlen("bool")+1); 
      if (strcmp($word, "true") != 0 && strcmp($word, "false") != 0)
      {
        $ok = 0;
      }
      break;
    case "nil":
      $word = substr($word, strlen("nil")+1); 
      if (strcmp($word, "nil") == 0)
        $ok = 1;
      else
        $ok = 0;
      break;
    default:
      exit(23);
  }

  return $ok;

}
function varsymbCheck($word) //(1 == var) (2 == symb) (0 == error)
{
  $wordvar = $word;
  $okvar = 1;
  $length = strlen($wordvar);
  $fr = substr($wordvar, 0, 2-$length);
  $framework = Framework::NULLF;
  switch ($fr) {
    case "GF":
        $framework = Framework::GF;
        break;
    case "TF":
        $framework = Framework::TF;
        break;
    case "LF":
        $framework = Framework::LF;
        break;
    default:
        $okvar = 0;
        break;
  }
  $wordvar = substr($wordvar, 2);   //removing processed characters from string
  if ($wordvar[0] != '@')
  {
    $okvar = 0;
  }
  $wordvar = substr($wordvar, 1);
  foreach (str_split($wordvar) as $char) {
    $av = ord($char);
    if (($av >= 48 && $av <= 57) || ($av >= 65 && $av <= 90) || ($av >= 97 && $av <= 122) || $av == '_' || $av == '-' || $av == '$'|| $av == '&' || $av == '%' || $av == '*' || $av == '!' || $av == '?' )
    {
      if ($char == $wordvar[0] && ($av >= 48 && $av <= 57))
      {
        $okvar = 0;
      }
    } else
    {
      $okvar = 0;
    }
  }

  if ($okvar != 0)
  {
    return $okvar;
  }
}

function parseDEFVAR($words, int $instruction_counter, $xml)
{
  $ok = varsymbCheck(($words[1]));
  if ($ok != 1)
  {
    echo "CHYBA 23";
    exit(23);
  } 
  $xml->startElement("arg1");
  $xml->startAttribute("type");
  $xml->text("var");
  $xml->endAttribute();
  $xml->text($words[1]);
  $xml->endElement();
}

function parseMOVE($words, $xml)
{
  $start_index = 0;
  $ok = varsymbCheck($words[1]);   //spatny zapis promenne
  if ($ok == 0 || $ok == 2)
  {
    echo "CHYBA 23";
    exit(23);
  } else if ($ok == 1)
  {
    $xml->startElement("arg1");
    $xml->startAttribute("type");
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[1]);
    $xml->endElement();
  }

  $ok = varsymbCheck($words[2]);
  $xml->startElement("arg2");
  $xml->startAttribute("type");
  if ($ok == 1)
  {
    $xml->text("var");
    $start_index = 0;
  } else if ($ok == 2)
  {
    $type = strtok($words[2], '@');
    $xml->text($type);
    $start_index = strlen($type)+1;
  } else
  {
    echo "CHYBA 23";
    exit(23);
  } 
  $xml->endAttribute();
  $value = substr($words[2], $start_index); 
  $xml->text($value);
  $xml->endElement();

}

function parseCALL($words, $xml)
{
  $ok = labelCheck($words[1]);
  if ($ok == 0)
  {
    printf("Nespravny nazev navesti\n");
    exit(23);
  }

  $xml->startElement("arg1");
  $xml->startAttribute("type");
  $xml->text("label");
  $xml->endAttribute();
  $xml->text($words[1]);
  $xml->endElement();
}

function parsePUSHS($words, $xml)
{
  $ok = varsymbCheck($words[1]);
  $xml->startElement("arg1");
  $xml->startAttribute("type");
  if ($ok == 1)
  {
    $xml->text("var");
    $start_index = 0;
  } else if ($ok == 2)
  {
    $type = strtok($words[1], '@');
    $xml->text($type);
    $start_index = strlen($type)+1;
  } else
  {
    echo "CHYBA 23";
    exit(23);
  } 
  $xml->endAttribute();
  $value = substr($words[1], $start_index); 
  $xml->text($value);
  $xml->endElement();
}

function parsePOPS($words, $xml)
{
  if (varsymbCheck($words[1]) == 1)
  {
    $xml->startElement("arg1");
    $xml->startAttribute("type");
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[1]);
    $xml->endElement();
  } else
  {
    echo "CHYBA 23";
    exit(23);
  }
}

function parseADD($words, $xml)
{
  if(varsymbCheck($words[1]) != 1 || varsymbCheck($words[2]) != 2 || varsymbCheck($words[3]) != 2)
  {
    echo "CHYBA 23";
    exit(23);
  }
  $xml->startElement("arg1");
  $xml->startAttribute("type");
  $xml->text("var");
  $xml->endAttribute();
  $xml->text($words[1]);
  $xml->endElement();

  if(strcmp(strtok($words[2], '@'), "int") != 0 || strcmp(strtok($words[3], '@'), "int") != 0)
  {
      echo "CHYBA 23";
      exit(23);
  }
  $value = substr($words[2], 4);
  $xml->startElement("arg2");
  $xml->startAttribute("type");
  $xml->text("int");
  $xml->endAttribute();
  $xml->text($value);
  $xml->endElement();

  $value = substr($words[3], 4);
  $xml->startElement("arg3");
  $xml->startAttribute("type");
  $xml->text("int");
  $xml->endAttribute();
  $xml->text($value);
  $xml->endElement();


}

function parseNoArgs($words, $xml)
{
  if (count($words) != 1)
  {
    if ($words[1][0] != '#')
    {
      echo "Tato operace nema mit zadne operandy\n";
      exit(23);
    }
  }
}

function parseVar($words, $xml)
{
  if (count($words) != 2)
  {
    if( $words[2][0] != '#')
    {
      echo "Tato operace ma mit pouze 1 operand!\n";
      exit(23);
    }
  }
  if (varCheck($words[1]) == 1)
  {
    $xml->startElement("arg1");
    $xml->startAttribute("type");
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[1]);
    $xml->endElement();
  } else
  {
    echo "Argument neni var!\n";
    exit(23);
  }

}

function parseVarSymb($words, $xml)
{
  if (count($words) != 3)
  {
    if( $words[3][0] != '#')
    {
      echo "Tato operace ma mit pouze 2 operandy!\n";
      exit(23);
    }
  }
  if (varCheck($words[1]) == 1)
  {
    $xml->startElement("arg1");
    $xml->startAttribute("type");
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[1]);
    $xml->endElement();
  } else
  {
    echo "Argument neni var!\n";
    exit(23);
  }
  $xml->startElement("arg2");
  $xml->startAttribute("type");
  if (varCheck($words[2]) == 1)
  {
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[2]);
  } else if (constCheck($words[2]) == 1)
  {
    $xml->text(strtok($words[2], '@'));
    $xml->endAttribute();
    $xml->text(substr($words[2], strpos($words[2], "@") + 1));
  } else
  {
    echo "Argument neni symb!\n";
    exit(23);
  }
  $xml->endElement();
}

function parseVarSymbSymb($words, $xml)
{
  if (count($words) != 4)
  {
    if( $words[4][0] != '#')
    {
      echo "Tato operace ma mit pouze 3 operandy!\n";
      exit(23);
    }
  }
  if (varCheck($words[1]) == 1)
  {
    $xml->startElement("arg1");
    $xml->startAttribute("type");
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[1]);
    $xml->endElement();
  } else
  {
    echo "Argument neni var!\n";
    exit(23);
  }

  $xml->startElement("arg2");
  $xml->startAttribute("type");
  if (varCheck($words[2]) == 1)
  {
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[2]);
  } else if (constCheck($words[2]) == 1)
  {
    $xml->text(strtok($words[2], '@'));
    $xml->endAttribute();
    $xml->text(substr($words[2], strpos($words[2], "@") + 1));
  } else
  {
    echo "Argument neni symb!\n";
    exit(23);
  }
  $xml->endElement();

  $xml->startElement("arg3");
  $xml->startAttribute("type");
  if (varCheck($words[3]) == 1)
  {
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[3]);
  } else if (constCheck($words[3]) == 1)
  {
    $xml->text(strtok($words[3], '@'));
    $xml->endAttribute();
    $xml->text(substr($words[3], strpos($words[3], "@") + 1));
  } else
  {
    echo "Argument neni symb!\n";
    exit(23);
  }
  $xml->endElement();
}

function parseLabel($words, $xml)
{
  if (count($words) != 2)
  {
    if( $words[2][0] != '#')
    {
      echo "Tato operace ma mit pouze 1 operand!\n";
      exit(23);
    }
  }
  if(labelCheck($words[1]) == 1)
  {
    $xml->startElement("arg1");
    $xml->startAttribute("type");
    $xml->text("label");
    $xml->endAttribute();
    $xml->text($words[1]);
    $xml->endElement();
  } else
  {
    echo "Argument neni typu label!\n";
    exit(23);
  }

}

function parseSymb($words, $xml)
{
  if (count($words) != 2)
  {
    if( $words[2][0] != '#')
    {
      echo "Tato operace ma mit pouze 1 operand!\n";
      exit(23);
    }
  }
  $xml->startElement("arg1");
  $xml->startAttribute("type");
  if (varCheck($words[1]) == 1)
  {
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[1]);
  } else if (constCheck($words[1]) == 1)
  {
    $xml->text(strtok($words[1], '@'));
    $xml->endAttribute();
    $xml->text(substr($words[1], strpos($words[1], "@") + 1));
  } else
  {
    echo "Argument neni symb!\n";
    exit(23);
  }
  $xml->endElement();
}

function parseVarType($words, $xml)
{
  if (count($words) != 3)
  {
    if( $words[3][0] != '#')
    {
      echo "Tato operace ma mit pouze 2 operandy!\n";
      exit(23);
    }
  }

  if (varCheck($words[1]) == 1)
  {
    $xml->startElement("arg1");
    $xml->startAttribute("type");
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[1]);
    $xml->endElement();
  } else
  {
    echo "Argument neni var!\n";
    exit(23);
  }

  if (strcmp($words[2], "int") == 0 || strcmp($words[2], "string") == 0 || strcmp($words[2], "bool") == 0)
  {
    $xml->startElement("arg2");
    $xml->startAttribute("type");
    $xml->text("type");
    $xml->endAttribute();
    $xml->text($words[2]);
    $xml->endElement();
  } else
  {
    echo "Argument neni type!\n";
    exit(23);
  }

}

function parseLabelSymbSymb($words, $xml)
{
  if (count($words) != 4)
  {
      echo "Tato operace ma mit 3 operandy!\n";
      exit(23);
  }
  if(labelCheck($words[1]) == 1)
  {
    $xml->startElement("arg1");
    $xml->startAttribute("type");
    $xml->text("label");
    $xml->endAttribute();
    $xml->text($words[1]);
    $xml->endElement();
  } else
  {
    echo "Argument neni typu label!\n";
    exit(23);
  }

  $xml->startElement("arg2");
  $xml->startAttribute("type");
  if (varCheck($words[2]) == 1)
  {
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[2]);
  } else if (constCheck($words[2]) == 1)
  {
    $xml->text(strtok($words[2], '@'));
    $xml->endAttribute();
    $xml->text(substr($words[2], strpos($words[2], "@") + 1));
  } else
  {
    echo "Argument neni symb!\n";
    exit(23);
  }
  $xml->endElement();

  $xml->startElement("arg3");
  $xml->startAttribute("type");
  if (varCheck($words[3]) == 1)
  {
    $xml->text("var");
    $xml->endAttribute();
    $xml->text($words[3]);
  } else if (constCheck($words[3]) == 1)
  {
    $xml->text(strtok($words[3], '@'));
    $xml->endAttribute();
    $xml->text(substr($words[3], strpos($words[3], "@") + 1));
  } else
  {
    echo "Argument neni symb!\n";
    exit(23);
  }
  $xml->endElement();
}

$instruction_counter = 1;
$xml = new XMLWriter();
xmlStart($xml);

$header = 0;
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
    {
      continue;
    }

    if ($header == 0)
    {
      if (strcmp($words[0], ".IPPcode23") != 0 || (count($words) != 1 && $words[1][0] != '#'))
      {
        printf("Spatna hlavicka\n");
        exit(21);
      }
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
        printf("Neznama operace! \n");
        exit(22);
    }
    endInstruction($xml);
    $instruction_counter++;
}

xmlEnd($xml);
?>